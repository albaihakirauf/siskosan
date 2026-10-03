<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Property.php';

class InvoiceController extends Controller {
    public function index() {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_invoices');
        } else {
            $this->authorize('manage_invoices');
        }
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        $tenantId = $_GET['tenant_id'] ?? null;
        $status = $_GET['status'] ?? '';
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $query = Invoice::query();
        
        // Filter by property
        if ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $roomIds = Room::where('property_id', $propertyId)->get();
            $ids = array_column($roomIds, 'id');
            $tenantIds = Tenant::whereIn('room_id', $ids)->get();
            $tids = array_column($tenantIds, 'id');
            if ($tids) {
                $query->whereIn('tenant_id', $tids);
            } else {
                $query->where('tenant_id', -1);
            }
        } elseif ($user->role === 'owner') {
            $propertyIds = Property::where('owner_id', $user->id)->get();
            $propIds = array_column($propertyIds, 'id');
            $roomIds = Room::whereIn('property_id', $propIds)->get();
            $rIds = array_column($roomIds, 'id');
            $tenantIds = Tenant::whereIn('room_id', $rIds)->get();
            $tids = array_column($tenantIds, 'id');
            if ($tids) {
                $query->whereIn('tenant_id', $tids);
            } else {
                $query->where('tenant_id', -1);
            }
        } elseif ($user->role === 'tenant') {
            $tenant = Tenant::where('user_id', $user->id)->where('status', 'active')->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                $query->where('tenant_id', -1);
            }
        }
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $query->where('period_month', $month)
              ->where('period_year', $year)
              ->orderBy('created_at', 'DESC');
        
        $invoices = $query->paginate(20);
        
        // Add tenant and room info
        $db = db();
        foreach ($invoices['data'] as &$invoice) {
            $tenant = $db->table('tenants t')
                ->join('users u', 't.user_id', '=', 'u.id')
                ->join('rooms r', 't.room_id', '=', 'r.id')
                ->where('t.id', $invoice['tenant_id'])
                ->select('u.name as tenant_name', 'u.phone as tenant_phone', 'r.room_number', 'r.property_id')
                ->first();
            
            $invoice['tenant_name'] = $tenant['tenant_name'] ?? '-';
            $invoice['tenant_phone'] = $tenant['tenant_phone'] ?? '-';
            $invoice['room_number'] = $tenant['room_number'] ?? '-';
            
            if ($tenant && $tenant['property_id']) {
                $prop = $db->table('properties')->find($tenant['property_id']);
                $invoice['property_name'] = $prop['name'] ?? '-';
            } else {
                $invoice['property_name'] = '-';
            }
            
            $invoice['paid_amount'] = $db->table('payments')
                ->where('invoice_id', $invoice['id'])
                ->where('status', 'verified')
                ->sum('amount');
            $invoice['remaining_amount'] = $invoice['total_amount'] - $invoice['paid_amount'];
        }
        
        // Get properties for filter
        $properties = Property::getUserProperties($user->id);
        
        // Summary stats
        $summary = [
            'total' => $db->table('invoices i')
                ->join('tenants t', 'i.tenant_id', '=', 't.id')
                ->join('rooms r', 't.room_id', '=', 'r.id')
                ->where('i.period_month', $month)
                ->where('i.period_year', $year)
                ->when($user->role === 'owner', function($q) use ($user) {
                    return $q->whereIn('r.property_id', array_column(Property::where('owner_id', $user->id)->get(), 'id'));
                })
                ->count(),
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'partial' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
        ];
        
        $allInvoices = $db->table('invoices i')
            ->join('tenants t', 'i.tenant_id', '=', 't.id')
            ->join('rooms r', 't.room_id', '=', 'r.id')
            ->where('i.period_month', $month)
            ->where('i.period_year', $year)
            ->when($user->role === 'owner', function($q) use ($user) {
                return $q->whereIn('r.property_id', array_column(Property::where('owner_id', $user->id)->get(), 'id'));
            })
            ->select('i.*')
            ->get();
        
        foreach ($allInvoices as $inv) {
            $summary['total_amount'] += $inv['total_amount'];
            $paid = $db->table('payments')
                ->where('invoice_id', $inv['id'])
                ->where('status', 'verified')
                ->sum('amount');
            $summary['paid_amount'] += $paid;
            
            switch ($inv['status']) {
                case 'paid': $summary['paid']++; break;
                case 'unpaid': $summary['unpaid']++; break;
                case 'overdue': $summary['overdue']++; break;
                case 'partial': $summary['partial']++; break;
            }
        }
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $this->view('invoices.index', [
            'invoices' => $invoices,
            'properties' => $properties,
            'propertyId' => $propertyId,
            'tenantId' => $tenantId,
            'status' => $status,
            'month' => $month,
            'year' => $year,
            'months' => $months,
            'summary' => $summary,
        ]);
    }

    public function create() {
        $this->authorize('manage_invoices');
        $user = auth();
        $properties = Property::getUserProperties($user->id);

        $db = db();
        $tenants = $db->table('tenants t')
            ->join('users u', 't.user_id', '=', 'u.id')
            ->join('rooms r', 't.room_id', '=', 'r.id')
            ->select('t.id', 'u.name as tenant_name', 'r.room_number')
            ->where('t.status', 'active')
            ->orderBy('u.name')
            ->get();

        $this->view('invoices.create', [
            'properties' => $properties,
            'tenants' => $tenants,
        ]);
    }

    public function store() {
        $this->authorize('manage_invoices');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'tenant_id' => 'required|exists:tenants,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030',
            'rent_amount' => 'required|numeric|min:0',
            'electricity_amount' => 'numeric|min:0',
            'water_amount' => 'numeric|min:0',
            'wifi_amount' => 'numeric|min:0',
            'other_amount' => 'numeric|min:0',
            'other_description' => 'max:255',
            'due_date' => 'required|date',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $tenant = Tenant::find($_POST['tenant_id']);
        if (!$tenant) {
            $this->flash('error', 'Penyewa tidak ditemukan');
            return $this->back();
        }

        // Check if invoice already exists
        $db = db();
        $exists = $db->table('invoices')
            ->where('tenant_id', $_POST['tenant_id'])
            ->where('period_month', $_POST['period_month'])
            ->where('period_year', $_POST['period_year'])
            ->exists();
        
        if ($exists) {
            $this->flash('error', 'Tagihan untuk periode ini sudah ada');
            return $this->back();
        }

        $rentAmount = $_POST['rent_amount'];
        $electricityAmount = $_POST['electricity_amount'] ?? 0;
        $waterAmount = $_POST['water_amount'] ?? 0;
        $wifiAmount = $_POST['wifi_amount'] ?? 0;
        $otherAmount = $_POST['other_amount'] ?? 0;
        $totalAmount = $rentAmount + $electricityAmount + $waterAmount + $wifiAmount + $otherAmount;

        $invoiceNumber = generate_invoice_number($_POST['tenant_id']);

        $data = [
            'tenant_id' => $_POST['tenant_id'],
            'room_id' => $tenant->room_id,
            'invoice_number' => $invoiceNumber,
            'period_month' => $_POST['period_month'],
            'period_year' => $_POST['period_year'],
            'rent_amount' => $rentAmount,
            'electricity_amount' => $electricityAmount,
            'water_amount' => $waterAmount,
            'wifi_amount' => $wifiAmount,
            'other_amount' => $otherAmount,
            'other_description' => $_POST['other_description'] ?? null,
            'total_amount' => $totalAmount,
            'due_date' => $_POST['due_date'],
            'status' => 'unpaid',
            'notes' => $_POST['notes'] ?? null,
        ];

        $id = $db->table('invoices')->insert($data);
        
        // Notify tenant
        send_notification($tenant->user_id, 'invoice_due',
            'Tagihan Baru',
            "Tagihan sewa bulan " . date('F Y', strtotime("{$_POST['period_year']}-{$_POST['period_month']}-01")) . " telah diterbitkan. Jatuh tempo: " . format_date($_POST['due_date']),
            'invoice', $id
        );
        
        log_activity('invoice_create', "Invoice created: {$invoiceNumber}", 'invoice', $id);
        $this->flash('success', 'Tagihan berhasil dibuat');
        return $this->redirect(url('invoices'));
    }

    public function show($id) {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_invoices');
        } else {
            $this->authorize('manage_invoices');
        }
        $invoice = Invoice::find($id);
        
        if (!$invoice) {
            $this->abort(404, 'Tagihan tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $invoice->tenant();
        $room = $invoice->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }
        if ($user->role === 'tenant' && $tenant->user_id !== $user->id) {
            $this->abort(403);
        }

        $payments = $invoice->payments()->get();
        
        $this->view('invoices.show', [
            'invoice' => $invoice,
            'tenant' => $tenant,
            'room' => $room,
            'property' => $property,
            'payments' => $payments,
        ]);
    }

    public function edit($id) {
        $this->authorize('manage_invoices');
        $invoice = Invoice::find($id);
        
        if (!$invoice) {
            $this->abort(404, 'Tagihan tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $invoice->tenant();
        $room = $invoice->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $this->view('invoices.edit', ['invoice' => $invoice]);
    }

    public function update($id) {
        $this->authorize('manage_invoices');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $invoice = Invoice::find($id);
        if (!$invoice) {
            $this->abort(404, 'Tagihan tidak ditemukan');
        }
        
        $user = auth();
        $tenant = $invoice->tenant();
        $room = $invoice->room();
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        if ($invoice->status === 'paid') {
            $this->flash('error', 'Tagihan yang sudah lunas tidak bisa diedit');
            return $this->back();
        }

        $rules = [
            'rent_amount' => 'required|numeric|min:0',
            'electricity_amount' => 'numeric|min:0',
            'water_amount' => 'numeric|min:0',
            'wifi_amount' => 'numeric|min:0',
            'other_amount' => 'numeric|min:0',
            'other_description' => 'max:255',
            'due_date' => 'required|date',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $rentAmount = $_POST['rent_amount'];
        $electricityAmount = $_POST['electricity_amount'] ?? 0;
        $waterAmount = $_POST['water_amount'] ?? 0;
        $wifiAmount = $_POST['wifi_amount'] ?? 0;
        $otherAmount = $_POST['other_amount'] ?? 0;
        $totalAmount = $rentAmount + $electricityAmount + $waterAmount + $wifiAmount + $otherAmount;

        $data = [
            'rent_amount' => $rentAmount,
            'electricity_amount' => $electricityAmount,
            'water_amount' => $waterAmount,
            'wifi_amount' => $wifiAmount,
            'other_amount' => $otherAmount,
            'other_description' => $_POST['other_description'] ?? null,
            'total_amount' => $totalAmount,
            'due_date' => $_POST['due_date'],
            'notes' => $_POST['notes'] ?? null,
        ];

        $db = db();
        $db->table('invoices')->where('id', $id)->update($data);
        
        log_activity('invoice_update', "Invoice updated", 'invoice', $id);
        $this->flash('success', 'Tagihan berhasil diperbarui');
        return $this->redirect(url('invoices/' . $id));
    }

    public function generateMonthly() {
        $this->authorize('manage_invoices');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $created = Invoice::generateMonthlyInvoices();
        
        $this->flash('success', "Berhasil membuat {$created} tagihan baru");
        return $this->redirect(url('invoices'));
    }

    public function bulkUpdateStatus() {
        $this->authorize('manage_invoices');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $ids = $_POST['ids'] ?? [];
        $status = $_POST['status'] ?? '';
        
        if (empty($ids) || !in_array($status, ['unpaid', 'paid', 'cancelled'])) {
            $this->flash('error', 'Data tidak valid');
            return $this->back();
        }

        $db = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->query("UPDATE invoices SET status = ? WHERE id IN ({$placeholders})", array_merge([$status], $ids));
        
        log_activity('invoice_bulk_update', "Bulk updated {$stmt->rowCount()} invoices to {$status}");
        $this->flash('success', 'Status tagihan diperbarui');
        return $this->back();
    }
}