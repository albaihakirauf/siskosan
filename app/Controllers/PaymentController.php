<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Property.php';

class PaymentController extends Controller {
    public function index() {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_payments');
        } else {
            $this->authorize('manage_payments');
        }
        $propertyId = $_GET['property_id'] ?? null;
        $tenantId = $_GET['tenant_id'] ?? null;
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $query = Payment::query();
        
        // Filter by property
        if ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $roomIds = Room::where('property_id', $propertyId)->get();
            $rIds = array_column($roomIds, 'id');
            $tenantIds = Tenant::whereIn('room_id', $rIds)->get();
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
        
        $query->where('payment_date', '>=', $startDate)
              ->where('payment_date', '<=', $endDate)
              ->orderBy('payment_date', 'DESC');
        
        $payments = $query->paginate(20);
        
        // Add tenant, invoice info
        $db = db();
        foreach ($payments['data'] as &$payment) {
            $tenant = $db->table('tenants t')
                ->join('users u', 't.user_id', '=', 'u.id')
                ->join('rooms r', 't.room_id', '=', 'r.id')
                ->where('t.id', $payment['tenant_id'])
                ->select('u.name as tenant_name', 'u.phone as tenant_phone', 'r.room_number', 'r.property_id')
                ->first();
            
            $payment['tenant_name'] = $tenant['tenant_name'] ?? '-';
            $payment['tenant_phone'] = $tenant['tenant_phone'] ?? '-';
            $payment['room_number'] = $tenant['room_number'] ?? '-';
            
            if ($tenant && $tenant['property_id']) {
                $prop = $db->table('properties')->find($tenant['property_id']);
                $payment['property_name'] = $prop['name'] ?? '-';
            } else {
                $payment['property_name'] = '-';
            }
            
            $invoice = $db->table('invoices')->find($payment['invoice_id']);
            $payment['invoice_period'] = $invoice ? ($invoice['period_month'] . '/' . $invoice['period_year']) : '-';
            $payment['invoice_number'] = $invoice['invoice_number'] ?? '-';
            
            if ($payment['verified_by']) {
                $verifier = $db->table('users')->find($payment['verified_by']);
                $payment['verifier_name'] = $verifier['name'] ?? '-';
            }
        }
        
        // Get properties for filter
        $properties = Property::getUserProperties($user->id);
        
        // Summary
        $summary = [
            'total' => 0,
            'verified' => 0,
            'pending' => 0,
            'rejected' => 0,
            'total_amount' => 0,
            'verified_amount' => 0,
        ];
        
        $allPayments = $db->table('payments p')
            ->join('invoices i', 'p.invoice_id', '=', 'i.id')
            ->join('tenants t', 'p.tenant_id', '=', 't.id')
            ->join('rooms r', 't.room_id', '=', 'r.id')
            ->where('p.payment_date', '>=', $startDate)
            ->where('p.payment_date', '<=', $endDate)
            ->when($user->role === 'owner', function($q) use ($user) {
                return $q->whereIn('r.property_id', array_column(Property::where('owner_id', $user->id)->get(), 'id'));
            })
            ->select('p.*')
            ->get();
        
        foreach ($allPayments as $pay) {
            $summary['total']++;
            $summary['total_amount'] += $pay['amount'];
            
            switch ($pay['status']) {
                case 'verified': 
                    $summary['verified']++; 
                    $summary['verified_amount'] += $pay['amount'];
                    break;
                case 'pending': $summary['pending']++; break;
                case 'rejected': $summary['rejected']++; break;
            }
        }
        
        $this->view('payments.index', [
            'payments' => $payments,
            'properties' => $properties,
            'propertyId' => $propertyId,
            'tenantId' => $tenantId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
        ]);
    }

    public function create() {
        $this->authorize('manage_payments');
        $user = auth();
        $invoiceId = $_GET['invoice_id'] ?? null;
        
        $invoice = null;
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                $this->flash('error', 'Tagihan tidak ditemukan');
                return $this->redirect(url('payments/create'));
            }
            
            $tenant = $invoice->tenant();
            $room = $invoice->room();
            $property = $room ? $room->property() : null;
            
            if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
                $this->abort(403);
            }
        }
        
        $this->view('payments.create', ['invoice' => $invoice]);
    }

    public function store() {
        $this->authorize('manage_payments');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,ewallet,other',
            'payment_date' => 'required|date',
            'reference_number' => 'max:100',
            'notes' => 'max:500',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $invoice = Invoice::find($_POST['invoice_id']);
        if (!$invoice) {
            $this->flash('error', 'Tagihan tidak ditemukan');
            return $this->back();
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

        // Check if amount exceeds remaining
        $remaining = $invoice->getRemainingAmount();
        if ($_POST['amount'] > $remaining) {
            $this->flash('error', 'Jumlah pembayaran melebihi sisa tagihan (' . format_currency($remaining) . ')');
            return $this->back();
        }

        $data = [
            'invoice_id' => $_POST['invoice_id'],
            'tenant_id' => $invoice->tenant_id,
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'],
            'payment_date' => $_POST['payment_date'],
            'reference_number' => $_POST['reference_number'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'status' => 'pending',
        ];

        // Handle proof photo
        if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['proof_photo'], 'uploads/payments');
            if ($result['success']) {
                $data['proof_photo'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $db = db();
        $id = $db->table('payments')->insert($data);
        
        // Notify admin/owner
        if ($user->role !== 'owner' && $user->role !== 'admin') {
            $admins = $db->table('users')->whereIn('role', ['owner', 'admin'])->where('is_active', 1)->get();
            $tenantUser = $tenant->user();
            foreach ($admins as $admin) {
                send_notification($admin['id'], 'payment_received',
                    'Pembayaran Baru',
                    "Pembayaran dari {$tenantUser->name} sebesar " . format_currency($_POST['amount']) . " untuk tagihan {$invoice->getPeriodLabel()} menunggu verifikasi.",
                    'payment', $id
                );
            }
        }
        
        log_activity('payment_create', "Payment recorded: " . format_currency($_POST['amount']), 'payment', $id);
        $this->flash('success', 'Pembayaran berhasil dicatat, menunggu verifikasi');
        return $this->redirect(url('payments'));
    }

    public function show($id) {
        $user = auth();
        if ($user->role === 'tenant') {
            $this->authorize('view_own_payments');
        } else {
            $this->authorize('manage_payments');
        }
        $payment = Payment::find($id);
        
        if (!$payment) {
            $this->abort(404, 'Pembayaran tidak ditemukan');
        }
        
        $user = auth();
        $invoice = $payment->invoice();
        $tenant = $payment->tenant();
        $room = $invoice ? $invoice->room() : null;
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }
        if ($user->role === 'tenant' && $tenant->user_id !== $user->id) {
            $this->abort(403);
        }

        $verifier = $payment->verifier();
        
        $this->view('payments.show', [
            'payment' => $payment,
            'invoice' => $invoice,
            'tenant' => $tenant,
            'room' => $room,
            'property' => $property,
            'verifier' => $verifier,
        ]);
    }

    public function verify($id) {
        $this->authorize('manage_payments');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $payment = Payment::find($id);
        if (!$payment) {
            $this->flash('error', 'Pembayaran tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $invoice = $payment->invoice();
        $tenant = $payment->tenant();
        $room = $invoice ? $invoice->room() : null;
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        if ($payment->status !== 'pending') {
            $this->flash('error', 'Pembayaran sudah diproses');
            return $this->back();
        }

        $payment->verify($user->id);
        
        $this->flash('success', 'Pembayaran berhasil diverifikasi');
        return $this->back();
    }

    public function reject($id) {
        $this->authorize('manage_payments');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $payment = Payment::find($id);
        if (!$payment) {
            $this->flash('error', 'Pembayaran tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $invoice = $payment->invoice();
        $tenant = $payment->tenant();
        $room = $invoice ? $invoice->room() : null;
        $property = $room ? $room->property() : null;
        
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        if ($payment->status !== 'pending') {
            $this->flash('error', 'Pembayaran sudah diproses');
            return $this->back();
        }

        $reason = $_POST['reason'] ?? 'Tidak sesuai';
        $payment->reject($user->id, $reason);
        
        $this->flash('success', 'Pembayaran ditolak');
        return $this->back();
    }

    public function verifyAll() {
        $this->authorize('manage_payments');

        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $user = auth();
        $db = db();
        $pending = $db->table('payments')->where('status', 'pending')->get();
        $count = 0;

        foreach ($pending as $p) {
            $payment = Payment::find($p['id']);
            if ($payment) {
                $invoice = $payment->invoice();
                $tenant = $payment->tenant();
                $room = $invoice ? $invoice->room() : null;
                $property = $room ? $room->property() : null;

                if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
                    continue;
                }

                $payment->verify($user->id);
                $count++;
            }
        }

        $this->flash('success', "{$count} pembayaran berhasil diverifikasi (Demo ACC)");
        return $this->redirect(url('payments'));
    }
}