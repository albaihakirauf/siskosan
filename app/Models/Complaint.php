<?php

require_once __DIR__ . '/Model.php';

class Complaint extends Model {
    protected $table = 'complaints';
    protected $fillable = [
        'tenant_id', 'room_id', 'title', 'description', 'category',
        'priority', 'status', 'photo_before', 'photo_after',
        'assigned_to', 'estimated_cost', 'actual_cost',
        'resolved_at', 'resolved_notes'
    ];
    protected $casts = [
        'estimated_cost' => 'float',
        'actual_cost' => 'float',
        'resolved_at' => 'datetime',
    ];
    protected $dates = ['created_at', 'updated_at', 'resolved_at'];

    public function tenant() {
        return Tenant::find($this->tenant_id);
    }

    public function room() {
        return Room::find($this->room_id);
    }

    public function assignee() {
        return $this->assigned_to ? User::find($this->assigned_to) : null;
    }

    public function getCategoryLabel() {
        $labels = [
            'electricity' => 'Listrik',
            'water' => 'Air',
            'internet' => 'Internet',
            'furniture' => 'Furniture',
            'cleanliness' => 'Kebersihan',
            'security' => 'Keamanan',
            'other' => 'Lainnya',
        ];
        return $labels[$this->category] ?? $this->category;
    }

    public function getPriorityLabel() {
        $labels = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
        ];
        return $labels[$this->priority] ?? $this->priority;
    }

    public function getPriorityBadgeClass() {
        $classes = [
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
        ];
        return $classes[$this->priority] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabel() {
        $labels = [
            'open' => 'Dibuka',
            'in_progress' => 'Diproses',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass() {
        $classes = [
            'open' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'resolved' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPhotoBeforeUrl() {
        if ($this->photo_before) {
            return asset('uploads/complaints/' . $this->photo_before);
        }
        return null;
    }

    public function getPhotoAfterUrl() {
        if ($this->photo_after) {
            return asset('uploads/complaints/' . $this->photo_after);
        }
        return null;
    }

    public function assignTo($userId) {
        $db = db();
        $db->table('complaints')->where('id', $this->id)->update([
            'assigned_to' => $userId,
            'status' => 'in_progress',
        ]);
        
        // Notify assignee
        send_notification($userId, 'complaint_created',
            'Komplain Baru Ditugaskan',
            "Anda ditugaskan untuk menangani komplain: {$this->title}",
            'complaint', $this->id
        );
        
        log_activity('complaint_assign', 'Complaint assigned', 'complaint', $this->id);
        return true;
    }

    public function resolve($userId, $notes = '', $actualCost = 0, $photoAfter = null) {
        $db = db();
        $updates = [
            'status' => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolved_notes' => $notes,
            'actual_cost' => $actualCost,
        ];
        
        if ($photoAfter) {
            $updates['photo_after'] = $photoAfter;
        }
        
        $db->table('complaints')->where('id', $this->id)->update($updates);
        
        // Notify tenant
        $tenant = $this->tenant();
        $tenantUserId = $tenant ? $tenant->user_id : null;
        if ($tenantUserId) {
            send_notification($tenantUserId, 'complaint_updated',
                'Komplain Diselesaikan',
                "Komplain \"{$this->title}\" telah diselesaikan. " . ($notes ? "Catatan: {$notes}" : ''),
                'complaint', $this->id
            );
        }
        
        log_activity('complaint_resolve', 'Complaint resolved', 'complaint', $this->id);
        return true;
    }
}