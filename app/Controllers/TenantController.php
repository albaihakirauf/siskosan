<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Invoice.php';

class TenantController extends Controller {
    public function index() {
        $this->authorize('manage_tenants');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        $roomId = $_GET['room_id'] ?? null;
        
        $query = Tenant::query();
        
        // Filter by property/room
        if ($roomId) {
            $room = Room::find($roomId);
            $roomProperty = $room ? $room->property() : null;
            if (!$room || !$roomProperty || ($user->role === 'owner' && $roomProperty->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $query->where('room_id', $roomId);
        } elseif ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $roomIds = Room::where('property_id', $propertyId)->get();
            $ids = array_column($roomIds, 'id');
            if ($ids) {
                $query->whereIn('room_id', $ids);
            } else {
                $query->where('room_id', -1);
            }
        } elseif ($user->role === 'owner') {
            $propertyIds = Property::where('owner_id', $user->id)->get();
            $propIds = array_column($propertyIds, 'id');
            $roomIds = Room::whereIn('property_id', $propIds)->get();
            $ids = array_column($roomIds, 'id');
            if ($ids) {
                $query->whereIn('room_id', $ids);
            } else {
                $query->where('room_id', -1);
            }
        }
        
        $search = $_GET['search'] ?? '';
        if ($search) {
            $like = "%{$search}%";
            $query->join('users u', 'tenants.user_id', '=', 'u.id')
                  ->select('tenants.*')
                  ->whereRaw("(u.name LIKE :s1 OR u.email LIKE :s2 OR tenants.ktp_number LIKE :s3)", [
                      's1' => $like,
                      's2' => $like,
                      's3' => $like,
                  ]);
        }
        
        $status = $_GET['status'] ?? '';
        if ($status) {
            $query->where('tenants.status', $status);
        }
        
        $tenants = $query->orderBy('tenants.created_at', 'DESC')->paginate(20);
        
        // Add room and property info
        $db = db();
        $currentUser = auth();
        foreach ($tenants['data'] as &$tenant) {
            $room = $db->table('rooms')->find($tenant['room_id']);
            $tenant['room_number'] = $room['room_number'] ?? '-';
            $tenant['property_name'] = '';
            if ($room) {
                $prop = $db->table('properties')->find($room['property_id']);
                $tenant['property_name'] = $prop['name'] ?? '-';
            }
            
            $tenantUser = $db->table('users')->find($tenant['user_id']);
            $tenant['tenant_name'] = $tenantUser['name'] ?? '-';
            $tenant['tenant_phone'] = $tenant['phone'] ?? ($tenantUser['phone'] ?? '-');
            
            // Get current invoice status
            $currentInvoice = $db->table('invoices')
                ->where('tenant_id', $tenant['id'])
                ->where('period_month', date('m'))
                ->where('period_year', date('Y'))
                ->first();
            $tenant['current_invoice_status'] = $currentInvoice['status'] ?? 'no_invoice';
            $tenant['current_invoice_amount'] = $currentInvoice['total_amount'] ?? 0;
        }
        
        // Get properties for filter
        $properties = Property::getUserProperties($currentUser->id);
        
        $this->view('tenants.index', [
            'tenants' => $tenants,
            'properties' => $properties,
            'propertyId' => $propertyId,
            'roomId' => $roomId,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create() {
        $this->authorize('manage_tenants');
        $user = auth();
        $roomId = $_GET['room_id'] ?? null;
        
        $properties = Property::getUserProperties($user->id);
        $rooms = [];
        
        if ($roomId) {
            $room = Room::find($roomId);
            $roomProperty = $room ? $room->property() : null;
            if (!$room || !$roomProperty || ($user->role === 'owner' && $roomProperty->owner_id !== $user->id)) {
                $this->abort(403);
            }
            if ($room->status !== 'empty') {
                $this->flash('error', 'Kamar tidak tersedia');
                return $this->redirect(url('tenants/create'));
            }
            $rooms = [$room];
        } else {
            // Get all empty rooms
            $propIds = array_column($properties, 'id');
            if ($propIds) {
                $rooms = Room::whereIn('property_id', $propIds)
                    ->where('status', 'empty')
                    ->get();
            }
        }
        
        $this->view('tenants.create', [
            'properties' => $properties,
            'rooms' => $rooms,
            'selectedRoom' => $roomId,
        ]);
    }

    public function store() {
        $this->authorize('manage_tenants');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|max:20',
            'ktp_number' => 'required|max:20',
            'emergency_contact_name' => 'required|max:100',
            'emergency_contact_phone' => 'required|max:20',
            'emergency_contact_relation' => 'max:50',
            'check_in_date' => 'required|date',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'date',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'numeric|min:0',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $user = auth();
        $room = Room::find($_POST['room_id']);
        $roomProperty = $room ? $room->property() : null;
        
        if (!$room || !$roomProperty || ($user->role === 'owner' && $roomProperty->owner_id !== $user->id)) {
            $this->abort(403);
        }

        if ($room->status !== 'empty') {
            $this->flash('error', 'Kamar tidak tersedia');
            return $this->back();
        }

        $db = db();
        
        // Create user account for tenant
        $userId = $db->table('users')->insert([
            'name' => trim($_POST['name']),
            'email' => strtolower(trim($_POST['email'])),
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role' => 'tenant',
            'phone' => $_POST['phone'],
            'is_active' => true,
        ]);

        // Handle KTP photo
        $ktpPhoto = null;
        if (isset($_FILES['ktp_photo']) && $_FILES['ktp_photo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['ktp_photo'], 'uploads/tenants');
            if ($result['success']) {
                $ktpPhoto = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        // Handle KK photo
        $kkPhoto = null;
        if (isset($_FILES['kk_photo']) && $_FILES['kk_photo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['kk_photo'], 'uploads/tenants');
            if ($result['success']) {
                $kkPhoto = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        // Create tenant
        $tenantId = $db->table('tenants')->insert([
            'user_id' => $userId,
            'room_id' => $_POST['room_id'],
            'ktp_number' => trim($_POST['ktp_number']),
            'ktp_photo' => $ktpPhoto,
            'kk_photo' => $kkPhoto,
            'phone' => $_POST['phone'],
            'emergency_contact_name' => $_POST['emergency_contact_name'],
            'emergency_contact_phone' => $_POST['emergency_contact_phone'],
            'emergency_contact_relation' => $_POST['emergency_contact_relation'] ?? null,
            'check_in_date' => $_POST['check_in_date'],
            'check_out_date' => $_POST['check_out_date'] ?? null,
            'contract_start_date' => $_POST['contract_start_date'],
            'contract_end_date' => $_POST['contract_end_date'] ?? null,
            'monthly_rent' => $_POST['monthly_rent'],
            'deposit_amount' => $_POST['deposit_amount'] ?? 0,
            'deposit_paid' => isset($_POST['deposit_paid']),
            'status' => 'active',
            'notes' => $_POST['notes'] ?? null,
        ]);

        // Update room status
        $db->table('rooms')->where('id', $_POST['room_id'])->update(['status' => 'occupied']);

        // Generate first invoice if check-in is current month
        $checkIn = new DateTime($_POST['check_in_date']);
        if ($checkIn->format('m') == date('m') && $checkIn->format('Y') == date('Y')) {
            $dueDay = config('app.billing.due_day');
            $dueDate = sprintf('%04d-%02d-%02d', date('Y'), date('m'), $dueDay);
            $invoiceNumber = generate_invoice_number($tenantId);
            
            $db->table('invoices')->insert([
                'tenant_id' => $tenantId,
                'room_id' => $_POST['room_id'],
                'invoice_number' => $invoiceNumber,
                'period_month' => (int) date('m'),
                'period_year' => (int) date('Y'),
                'rent_amount' => $_POST['monthly_rent'],
                'total_amount' => $_POST['monthly_rent'],
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);
        }

        log_activity('tenant_create', "Tenant checked in: {$_POST['name']}", 'tenant', $tenantId);
        $this->flash('success', 'Penyewa berhasil ditambahkan');
        return $this->redirect(url('tenants'));
    }

    public function show($id) {
        $this->authorize('manage_tenants');
        $tenant = Tenant::find($id);
        
        if (!$tenant) {
            $this->abort(404, 'Penyewa tidak ditemukan');
        }
        
        $user = auth();
        $room = $tenant->room();
        $property = $tenant->property();
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $invoices = $tenant->invoices()->limit(12)->get();
        $payments = $tenant->payments()->where('status', 'verified')->limit(10)->get();
        $complaints = $tenant->complaints()->limit(10)->get();
        $unpaidInvoices = $tenant->getUnpaidInvoices();
        
        $this->view('tenants.show', [
            'tenant' => $tenant,
            'room' => $room,
            'property' => $property,
            'invoices' => $invoices,
            'payments' => $payments,
            'complaints' => $complaints,
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }

    public function edit($id) {
        $this->authorize('manage_tenants');
        $tenant = Tenant::find($id);
        
        if (!$tenant) {
            $this->abort(404, 'Penyewa tidak ditemukan');
        }
        
        $user = auth();
        $room = $tenant->room();
        $property = $tenant->property();
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $this->view('tenants.edit', ['tenant' => $tenant]);
    }

    public function update($id) {
        $this->authorize('manage_tenants');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $this->abort(404, 'Penyewa tidak ditemukan');
        }
        
        $user = auth();
        $room = $tenant->room();
        $property = $tenant->property();
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $rules = [
            'phone' => 'max:20',
            'emergency_contact_name' => 'max:100',
            'emergency_contact_phone' => 'max:20',
            'emergency_contact_relation' => 'max:50',
            'contract_end_date' => 'date',
            'monthly_rent' => 'numeric|min:0',
            'deposit_amount' => 'numeric|min:0',
            'notes' => 'max:1000',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $data = [
            'phone' => $_POST['phone'] ?? null,
            'emergency_contact_name' => $_POST['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? null,
            'emergency_contact_relation' => $_POST['emergency_contact_relation'] ?? null,
            'contract_end_date' => $_POST['contract_end_date'] ?? null,
            'monthly_rent' => $_POST['monthly_rent'] ?? $tenant->monthly_rent,
            'deposit_amount' => $_POST['deposit_amount'] ?? $tenant->deposit_amount,
            'deposit_paid' => isset($_POST['deposit_paid']),
            'notes' => $_POST['notes'] ?? null,
        ];

        // Handle KTP photo
        if (isset($_FILES['ktp_photo']) && $_FILES['ktp_photo']['error'] === UPLOAD_ERR_OK) {
            if ($tenant->ktp_photo) {
                delete_file('uploads/tenants/' . $tenant->ktp_photo);
            }
            $result = upload_file($_FILES['ktp_photo'], 'uploads/tenants');
            if ($result['success']) {
                $data['ktp_photo'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        // Handle KK photo
        if (isset($_FILES['kk_photo']) && $_FILES['kk_photo']['error'] === UPLOAD_ERR_OK) {
            if ($tenant->kk_photo) {
                delete_file('uploads/tenants/' . $tenant->kk_photo);
            }
            $result = upload_file($_FILES['kk_photo'], 'uploads/tenants');
            if ($result['success']) {
                $data['kk_photo'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $db = db();
        $db->table('tenants')->where('id', $id)->update($data);
        
        // Update user phone if changed
        if (isset($data['phone'])) {
            $db->table('users')->where('id', $tenant->user_id)->update(['phone' => $data['phone']]);
        }
        
        log_activity('tenant_update', "Tenant updated", 'tenant', $id);
        $this->flash('success', 'Data penyewa berhasil diperbarui');
        return $this->redirect(url('tenants/' . $id));
    }

    public function checkout($id) {
        $this->authorize('manage_tenants');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $this->flash('error', 'Penyewa tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $room = $tenant->room();
        $property = $tenant->property();
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        $checkOutDate = $_POST['check_out_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';

        $db = db();
        $db->table('tenants')->where('id', $id)->update([
            'status' => 'checkout',
            'check_out_date' => $checkOutDate,
            'notes' => $notes . ($tenant->notes ? "\n\nCheckout: " . $notes : ''),
        ]);

        // Update room status
        $db->table('rooms')->where('id', $tenant->room_id)->update(['status' => 'empty']);

        // Deactivate user account
        $db->table('users')->where('id', $tenant->user_id)->update(['is_active' => false]);

        log_activity('tenant_checkout', "Tenant checked out", 'tenant', $id);
        $this->flash('success', 'Check-out berhasil');
        return $this->redirect(url('tenants/' . $id));
    }
}