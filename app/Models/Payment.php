<?php

require_once __DIR__ . '/Model.php';

class Payment extends Model {
    protected $table = 'payments';
    protected $fillable = [
        'invoice_id', 'tenant_id', 'amount', 'payment_method',
        'payment_date', 'proof_photo', 'reference_number', 'notes',
        'verified_by', 'verified_at', 'status'
    ];
    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];
    protected $dates = ['created_at', 'updated_at', 'payment_date', 'verified_at'];

    public function invoice() {
        return Invoice::find($this->invoice_id);
    }

    public function tenant() {
        return Tenant::find($this->tenant_id);
    }

    public function verifier() {
        return $this->verified_by ? User::find($this->verified_by) : null;
    }

    public function getMethodLabel() {
        $labels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'ewallet' => 'E-Wallet',
            'other' => 'Lainnya',
        ];
        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusLabel() {
        $labels = [
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass() {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'verified' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getProofUrl() {
        if ($this->proof_photo) {
            return asset('uploads/payments/' . $this->proof_photo);
        }
        return null;
    }

    public function verify($userId) {
        $db = db();
        $db->table('payments')->where('id', $this->id)->update([
            'status' => 'verified',
            'verified_by' => $userId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        // Update invoice status
        $invoice = $this->invoice();
        if ($invoice) {
            $paidAmount = $invoice->getPaidAmount();
            $newStatus = $paidAmount >= $invoice->total_amount ? 'paid' : 'partial';
            
            $db->table('invoices')->where('id', $invoice->id)->update([
                'status' => $newStatus,
            ]);

            // Notify tenant
            $tenant = $this->tenant();
            $tenantUserId = $tenant ? $tenant->user_id : $this->tenant_id;
            send_notification($tenantUserId, 'payment_verified',
                'Pembayaran Diverifikasi',
                "Pembayaran Anda sebesar " . format_currency($this->amount) . " untuk tagihan " . $invoice->getPeriodLabel() . " telah diverifikasi.",
                'payment', $this->id
            );
        }

        log_activity('payment_verify', 'Payment verified', 'payment', $this->id);
        return true;
    }

    public function reject($userId, $reason = '') {
        $db = db();
        $db->table('payments')->where('id', $this->id)->update([
            'status' => 'rejected',
            'verified_by' => $userId,
            'verified_at' => date('Y-m-d H:i:s'),
            'notes' => $reason . ($this->notes ? "\n\n" . $this->notes : ''),
        ]);

        // Notify tenant
        $tenant = $this->tenant();
        $tenantUserId = $tenant ? $tenant->user_id : $this->tenant_id;
        send_notification($tenantUserId, 'payment_received',
            'Pembayaran Ditolak',
            "Pembayaran Anda sebesar " . format_currency($this->amount) . " ditolak. Alasan: " . $reason,
            'payment', $this->id
        );

        log_activity('payment_reject', 'Payment rejected', 'payment', $this->id);
        return true;
    }
}