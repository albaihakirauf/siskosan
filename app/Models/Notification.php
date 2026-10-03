<?php

require_once __DIR__ . '/Model.php';

class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'reference_type', 'reference_id', 'is_read', 'read_at'
    ];
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
    protected $dates = ['created_at', 'read_at'];

    public function user() {
        return User::find($this->user_id);
    }

    public function getTypeLabel() {
        $labels = [
            'invoice_due' => 'Tagihan Jatuh Tempo',
            'invoice_overdue' => 'Tagihan Terlambat',
            'payment_received' => 'Pembayaran Diterima',
            'payment_verified' => 'Pembayaran Diverifikasi',
            'complaint_created' => 'Komplain Baru',
            'complaint_updated' => 'Komplain Diperbarui',
            'announcement' => 'Pengumuman',
            'system' => 'Sistem',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeIcon() {
        $icons = [
            'invoice_due' => 'credit-card',
            'invoice_overdue' => 'alert-triangle',
            'payment_received' => 'check-circle',
            'payment_verified' => 'badge-check',
            'complaint_created' => 'alert-circle',
            'complaint_updated' => 'refresh-cw',
            'announcement' => 'megaphone',
            'system' => 'settings',
        ];
        return $icons[$this->type] ?? 'bell';
    }

    public function markAsRead() {
        if (!$this->is_read) {
            $db = db();
            $db->table('notifications')->where('id', $this->id)->update([
                'is_read' => true,
                'read_at' => date('Y-m-d H:i:s'),
            ]);
            $this->is_read = true;
            $this->read_at = new DateTime();
        }
        return true;
    }

    public static function markAllAsRead($userId) {
        $db = db();
        return $db->table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public static function getUnreadCount($userId) {
        $db = db();
        return $db->table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

    public function getReferenceUrl() {
        switch ($this->reference_type) {
            case 'invoice':
                return url('invoices/' . $this->reference_id);
            case 'payment':
                return url('payments/' . $this->reference_id);
            case 'complaint':
                return url('maintenance/' . $this->reference_id);
            default:
                return '#';
        }
    }
}