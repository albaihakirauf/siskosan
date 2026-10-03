<?php

require_once __DIR__ . '/Model.php';

class Room extends Model {
    protected $table = 'rooms';
    protected $fillable = [
        'property_id', 'room_number', 'type', 'price', 'capacity',
        'facilities', 'status', 'description', 'photo', 'floor', 'size_sqm'
    ];
    protected $casts = [
        'price' => 'float',
        'capacity' => 'integer',
        'facilities' => 'json',
        'floor' => 'integer',
        'size_sqm' => 'float',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function property() {
        return Property::find($this->property_id);
    }

    public function tenant() {
        return Tenant::where('room_id', $this->id)->where('status', 'active')->first();
    }

    public function getPhotoUrl() {
        if ($this->photo) {
            return asset('uploads/rooms/' . $this->photo);
        }
        return asset('assets/images/room-placeholder.svg');
    }

    public function getTypeLabel() {
        $labels = [
            'single' => 'Single',
            'double' => 'Double',
            'suite' => 'Suite',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatusLabel() {
        $labels = [
            'empty' => 'Kosong',
            'occupied' => 'Terisi',
            'maintenance' => 'Maintenance',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass() {
        $classes = [
            'empty' => 'bg-green-100 text-green-800',
            'occupied' => 'bg-blue-100 text-blue-800',
            'maintenance' => 'bg-yellow-100 text-yellow-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFacilitiesArray() {
        return $this->facilities ?? [];
    }

    public function getCurrentInvoice($month = null, $year = null) {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $tenant = $this->tenant();
        if (!$tenant) return null;
        
        return Invoice::where('tenant_id', $tenant->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();
    }

    public function isOccupied() {
        return $this->status === 'occupied';
    }

    public function isAvailable() {
        return $this->status === 'empty';
    }
}