<?php

require_once __DIR__ . '/Model.php';

class Tenant extends Model {
    protected $table = 'tenants';
    protected $fillable = [
        'user_id', 'room_id', 'ktp_number', 'ktp_photo', 'kk_photo',
        'phone', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'check_in_date', 'check_out_date', 'contract_start_date', 'contract_end_date',
        'monthly_rent', 'deposit_amount', 'deposit_paid', 'status', 'notes'
    ];
    protected $casts = [
        'monthly_rent' => 'float',
        'deposit_amount' => 'float',
        'deposit_paid' => 'boolean',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];
    protected $dates = ['created_at', 'updated_at', 'check_in_date', 'check_out_date', 'contract_start_date', 'contract_end_date'];

    public function user() {
        return User::find($this->user_id);
    }

    public function room() {
        return Room::find($this->room_id);
    }

    public function property() {
        $room = $this->room();
        return $room ? $room->property() : null;
    }

    public function invoices() {
        return Invoice::where('tenant_id', $this->id)->orderBy('period_year', 'DESC')->orderBy('period_month', 'DESC');
    }

    public function payments() {
        return Payment::where('tenant_id', $this->id)->orderBy('payment_date', 'DESC');
    }

    public function complaints() {
        return Complaint::where('tenant_id', $this->id)->orderBy('created_at', 'DESC');
    }

    public function getKtpPhotoUrl() {
        if ($this->ktp_photo) {
            return asset('uploads/tenants/' . $this->ktp_photo);
        }
        return null;
    }

    public function getKkPhotoUrl() {
        if ($this->kk_photo) {
            return asset('uploads/tenants/' . $this->kk_photo);
        }
        return null;
    }

    public function getStatusLabel() {
        $labels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'checkout' => 'Check-out',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass() {
        $classes = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'checkout' => 'bg-red-100 text-red-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getTotalArrears() {
        $db = db();
        return $db->table('invoices')
            ->where('tenant_id', $this->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('total_amount');
    }

    public function getUnpaidInvoices() {
        return $this->invoices()->whereIn('status', ['unpaid', 'partial', 'overdue'])->get();
    }

    public function getCurrentInvoice() {
        return $this->invoices()->where('period_month', date('m'))->where('period_year', date('Y'))->first();
    }
}