<?php

require_once __DIR__ . '/Model.php';

class Invoice extends Model {
    protected $table = 'invoices';
    protected $fillable = [
        'tenant_id', 'room_id', 'invoice_number', 'period_month', 'period_year',
        'rent_amount', 'electricity_amount', 'water_amount', 'wifi_amount',
        'other_amount', 'other_description', 'total_amount', 'due_date', 'status', 'notes'
    ];
    protected $casts = [
        'rent_amount' => 'float',
        'electricity_amount' => 'float',
        'water_amount' => 'float',
        'wifi_amount' => 'float',
        'other_amount' => 'float',
        'total_amount' => 'float',
        'period_month' => 'integer',
        'period_year' => 'integer',
        'due_date' => 'date',
    ];
    protected $dates = ['created_at', 'updated_at', 'due_date'];

    public function tenant() {
        return Tenant::find($this->tenant_id);
    }

    public function room() {
        return Room::find($this->room_id);
    }

    public function payments() {
        return Payment::where('invoice_id', $this->id)->orderBy('payment_date', 'DESC');
    }

    public function getStatusLabel() {
        $labels = [
            'unpaid' => 'Belum Bayar',
            'partial' => 'Cicilan',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass() {
        $classes = [
            'unpaid' => 'bg-red-100 text-red-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-200 text-red-900',
            'cancelled' => 'bg-gray-100 text-gray-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaidAmount() {
        return $this->payments()->where('status', 'verified')->sum('amount');
    }

    public function getRemainingAmount() {
        return $this->total_amount - $this->getPaidAmount();
    }

    public function isOverdue() {
        if ($this->status === 'paid') return false;
        $due = $this->due_date instanceof DateTime ? $this->due_date : new DateTime($this->due_date);
        $now = new DateTime();
        return $now > $due;
    }

    public function getDaysOverdue() {
        if (!$this->isOverdue()) return 0;
        $due = $this->due_date instanceof DateTime ? $this->due_date : new DateTime($this->due_date);
        $now = new DateTime();
        return $now->diff($due)->days;
    }

    public function getPeriodLabel() {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }

    public static function generateMonthlyInvoices() {
        $db = db();
        $month = (int) date('m');
        $year = (int) date('Y');
        $dueDay = config('app.billing.due_day');
        $dueDate = sprintf('%04d-%02d-%02d', $year, $month, $dueDay);
        
        // Get all active tenants
        $tenants = $db->table('tenants t')
            ->join('rooms r', 't.room_id', '=', 'r.id')
            ->where('t.status', 'active')
            ->where('r.status', 'occupied')
            ->select('t.*', 'r.price as room_price')
            ->get();

        $created = 0;
        foreach ($tenants as $tenant) {
            // Check if invoice already exists
            $exists = $db->table('invoices')
                ->where('tenant_id', $tenant['id'])
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->exists();

            if (!$exists) {
                $invoiceNumber = generate_invoice_number($tenant['id']);
                $rentAmount = $tenant['monthly_rent'] ?? $tenant['room_price'];
                
                $db->table('invoices')->insert([
                    'tenant_id' => $tenant['id'],
                    'room_id' => $tenant['room_id'],
                    'invoice_number' => $invoiceNumber,
                    'period_month' => $month,
                    'period_year' => $year,
                    'rent_amount' => $rentAmount,
                    'total_amount' => $rentAmount, // Additional charges can be added later
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                ]);
                
                // Create notification
                send_notification($tenant['user_id'], 'invoice_due', 
                    'Tagihan Baru', 
                    "Tagihan sewa bulan " . date('F Y', strtotime("$year-$month-01")) . " telah diterbitkan. Jatuh tempo: " . format_date($dueDate),
                    'invoice', $db->getPdo()->lastInsertId()
                );
                
                $created++;
            }
        }

        return $created;
    }

    public static function updateOverdueStatus() {
        $db = db();
        $today = date('Y-m-d');
        
        $db->query("
            UPDATE invoices 
            SET status = 'overdue' 
            WHERE status IN ('unpaid', 'partial') 
            AND due_date < ?
        ", [$today]);
    }
}