<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Complaint.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/User.php';

class MaintenanceController extends Controller {
    public function index() {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_complaints');
        } else {
            $this->authorize('manage_complaints');
        }
        $propertyId = $_GET['property_id'] ?? null;
        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $query = Complaint::query();
        
        // Filter by property
        if ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $roomIds = Room::where('property_id', $propertyId)->get();
            $ids = array_column($roomIds, 'id');
            $query->whereIn('room_id', $ids);
        } elseif ($user->role === 'owner') {
            $propertyIds = Property::where('owner_id', $user->id)->get();
            $propIds = array_column($propertyIds, 'id');
            $roomIds = Room::whereIn('property_id', $propIds)->get();
            $rIds = array_column($roomIds, 'id');
            if ($rIds) {
                $query->whereIn('room_id', $rIds);
            } else {
                $query->where('room_id', -1);
            }
        } elseif ($user->role === 'tenant') {
            $tenant = Tenant::where('user_id', $user->id)->where('status', 'active')->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                $query->where('tenant_id', -1);
            }
        } elseif ($user->role === 'admin') {
            // Admin sees all, but can filter by assigned_to
            if (isset($_GET['my_tasks'])) {
                $query->where('assigned_to', $user->id);
            }
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $complaints = $query->orderBy('created_at', 'DESC')->paginate(20);
        
        // Add tenant, room info
        $db = db();
        foreach ($complaints['data'] as &$complaint) {
            $tenant = $db->table('tenants t')
                ->join('users u', 't.user_id', '=', 'u.id')
                ->join('rooms r', 't.room_id', '=', 'r.id')
                ->where('t.id', $complaint['tenant_id'])
                ->select('u.name as tenant_name', 'u.phone as tenant_phone', 'r.room_number', 'r.property_id')
                ->first();
            
            $complaint['tenant_name'] = $tenant['tenant_name'] ?? '-';
            $complaint['tenant_phone'] = $tenant['tenant_phone'] ?? '-';
            $complaint['room_number'] = $tenant['room_number'] ?? '-';
            
            if ($tenant && $tenant['property_id']) {
                $prop = $db->table('properties')->find($tenant['property_id']);
                $complaint['property_name'] = $prop['name'] ?? '-';
            } else {
                $complaint['property_name'] = '-';
            }
            
            if ($complaint['assigned_to']) {
                $assignee = $db->table('users')->find($complaint['assigned_to']);
                $complaint['assignee_name'] = $assignee['name'] ?? '-';
            }
        }
        
        // Get properties for filter
        $properties = Property::getUserProperties($user->id);
        
        // Stats
        $stats = [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];
        
        $allComplaints = $db->table('complaints c')
            ->join('tenants t', 'c.tenant_id', '=', 't.id')
            ->join('rooms r', 'c.room_id', '=', 'r.id')
            ->when($user->role === 'owner', function($q) use ($user) {
                return $q->whereIn('r.property_id', array_column(Property::where('owner_id', $user->id)->get(), 'id'));
            })
            ->select('c.status')
            ->get();
        
        foreach ($allComplaints as $c) {
            if (isset($stats[$c['status']])) {
                $stats[$c['status']]++;
            }
        }
        
        $categories = [
            'electricity' => 'Listrik',
            'water' => 'Air',
            'internet' => 'Internet',
            'furniture' => 'Furniture',
            'cleanliness' => 'Kebersihan',
            'security' => 'Keamanan',
            'other' => 'Lainnya',
        ];
        
        $this->view('maintenance.index', [
            'complaints' => $complaints,
            'properties' => $properties,
            'propertyId' => $propertyId,
            'status' => $status,
            'category' => $category,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    public function create() {
        $this->authorize('create_complaint');
        $user = auth();
        $roomId = $_GET['room_id'] ?? null;
        
        if ($user->role === 'tenant') {
            $tenant = Tenant::where('user_id', $user->id)->where('status', 'active')->first();
            if (!$tenant) {
                $this->flash('error', 'Anda tidak memiliki kamar aktif');
                return $this->redirect(url('dashboard'));
            }
            $roomId = $tenant->room_id;
        }
        
        $room = null;
        if ($roomId) {
            $room = Room::find($roomId);
            $roomProperty = $room ? $room->property() : null;
            if (!$room || !$roomProperty || ($user->role === 'owner' && $roomProperty->owner_id !== $user->id)) {
                $this->abort(403);
            }
        }
        
        $categories = [
            'electricity' => 'Listrik',
            'water' => 'Air',
            'internet' => 'Internet',
            'furniture' => 'Furniture',
            'cleanliness' => 'Kebersihan',
            'security' => 'Keamanan',
            'other' => 'Lainnya',
        ];
        
        $this->view('maintenance.create', [
            'room' => $room,
            'categories' => $categories,
        ]);
    }

    public function store() {
        $this->authorize('create_complaint');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|min:5|max:200',
            'description' => 'required|min:10',
            'category' => 'required|in:electricity,water,internet,furniture,cleanliness,security,other',
            'priority' => 'in:low,medium,high,urgent',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $user = auth();
        $room = Room::find($_POST['room_id']);
        
        if (!$room) {
            $this->flash('error', 'Kamar tidak ditemukan');
            return $this->back();
        }
        
        $tenant = null;
        if ($user->role === 'tenant') {
            $tenant = Tenant::where('user_id', $user->id)->where('room_id', $room->id)->where('status', 'active')->first();
            if (!$tenant) {
                $this->flash('error', 'Anda tidak berhak mengajukan komplain untuk kamar ini');
                return $this->back();
            }
        } else {
            $tenant = Tenant::where('room_id', $room->id)->where('status', 'active')->first();
            if (!$tenant) {
                $this->flash('error', 'Kamar tidak memiliki penyewa aktif');
                return $this->back();
            }
        }

        // Handle photo
        $photoBefore = null;
        if (isset($_FILES['photo_before']) && $_FILES['photo_before']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['photo_before'], 'uploads/complaints');
            if ($result['success']) {
                $photoBefore = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $data = [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'category' => $_POST['category'],
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => 'open',
            'photo_before' => $photoBefore,
        ];

        $db = db();
        $id = $db->table('complaints')->insert($data);
        
        // Notify admin/owner
        $property = $room->property();
        $admins = $db->table('users')
            ->whereIn('role', ['owner', 'admin'])
            ->where('is_active', 1);
        
        if ($property) {
            $admins->where('id', $property->owner_id);
        }
        $admins = $admins->get();
        
        foreach ($admins as $admin) {
            $tenantUser = $tenant->user();
            send_notification($admin['id'], 'complaint_created',
                'Komplain Baru',
                "Komplain baru dari {$tenantUser->name} (Kamar {$room->room_number}): {$_POST['title']}",
                'complaint', $id
            );
        }
        
        log_activity('complaint_create', "Complaint created: {$_POST['title']}", 'complaint', $id);
        $this->flash('success', 'Komplain berhasil dikirim');
        
        if ($user->role === 'tenant') {
            return $this->redirect(url('maintenance'));
        }
        return $this->redirect(url('maintenance/' . $id));
    }

    public function show($id) {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_complaints');
        } else {
            $this->authorize('manage_complaints');
        }
        $complaint = Complaint::find($id);
        
        if (!$complaint) {
            $this->abort(404, 'Komplain tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $complaint->tenant();
        $room = $complaint->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }
        if ($user->role === 'tenant' && $tenant->user_id !== $user->id) {
            $this->abort(403);
        }

        $assignee = $complaint->assignee();
        
        // Get available staff for assignment
        $staff = [];
        if ($user->role === 'owner' || $user->role === 'admin') {
            $db = db();
            $staff = $db->table('users')
                ->whereIn('role', ['owner', 'admin'])
                ->where('is_active', 1)
                ->get();
        }
        
        $this->view('maintenance.show', [
            'complaint' => $complaint,
            'tenant' => $tenant,
            'room' => $room,
            'property' => $property,
            'assignee' => $assignee,
            'staff' => $staff,
        ]);
    }

    public function edit($id) {
        $this->authorize('manage_complaints');
        $complaint = Complaint::find($id);
        
        if (!$complaint) {
            $this->abort(404, 'Komplain tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $complaint->tenant();
        $room = $complaint->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $categories = [
            'electricity' => 'Listrik',
            'water' => 'Air',
            'internet' => 'Internet',
            'furniture' => 'Furniture',
            'cleanliness' => 'Kebersihan',
            'security' => 'Keamanan',
            'other' => 'Lainnya',
        ];
        
        $this->view('maintenance.edit', [
            'complaint' => $complaint,
            'categories' => $categories,
        ]);
    }

    public function update($id) {
        $this->authorize('manage_complaints');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $complaint = Complaint::find($id);
        if (!$complaint) {
            $this->abort(404, 'Komplain tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $complaint->tenant();
        $room = $complaint->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $rules = [
            'title' => 'required|min:5|max:200',
            'description' => 'required|min:10',
            'category' => 'required|in:electricity,water,internet,furniture,cleanliness,security,other',
            'priority' => 'in:low,medium,high,urgent',
            'status' => 'in:open,in_progress,resolved,closed',
            'estimated_cost' => 'numeric|min:0',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'category' => $_POST['category'],
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? $complaint->status,
            'estimated_cost' => $_POST['estimated_cost'] ?? 0,
        ];

        // Handle photo
        if (isset($_FILES['photo_before']) && $_FILES['photo_before']['error'] === UPLOAD_ERR_OK) {
            if ($complaint->photo_before) {
                delete_file('uploads/complaints/' . $complaint->photo_before);
            }
            $result = upload_file($_FILES['photo_before'], 'uploads/complaints');
            if ($result['success']) {
                $data['photo_before'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $db = db();
        $db->table('complaints')->where('id', $id)->update($data);
        
        log_activity('complaint_update', "Complaint updated", 'complaint', $id);
        $this->flash('success', 'Komplain berhasil diperbarui');
        return $this->redirect(url('maintenance/' . $id));
    }

    public function assign($id) {
        $this->authorize('manage_complaints');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $complaint = Complaint::find($id);
        if (!$complaint) {
            $this->flash('error', 'Komplain tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $tenant = $complaint->tenant();
        $room = $complaint->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        $assignedTo = $_POST['assigned_to'] ?? null;
        
        if ($assignedTo) {
            $assignee = User::find($assignedTo);
            if (!$assignee || !in_array($assignee->role, ['owner', 'admin'])) {
                $this->flash('error', 'Petugas tidak valid');
                return $this->back();
            }
        }

        $complaint->assignTo($assignedTo);
        
        $this->flash('success', 'Komplain berhasil ditugaskan');
        return $this->back();
    }

    public function resolve($id) {
        $this->authorize('manage_complaints');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $complaint = Complaint::find($id);
        if (!$complaint) {
            $this->flash('error', 'Komplain tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $tenant = $complaint->tenant();
        $room = $complaint->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        $notes = $_POST['resolved_notes'] ?? '';
        $actualCost = $_POST['actual_cost'] ?? 0;
        $photoAfter = null;
        
        if (isset($_FILES['photo_after']) && $_FILES['photo_after']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['photo_after'], 'uploads/complaints');
            if ($result['success']) {
                $photoAfter = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $complaint->resolve($user->id, $notes, $actualCost, $photoAfter);
        
        $this->flash('success', 'Komplain diselesaikan');
        return $this->back();
    }
}