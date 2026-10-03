<?php

require_once __DIR__ . '/Model.php';

class Property extends Model {
    protected $table = 'properties';
    protected $fillable = [
        'owner_id', 'name', 'address', 'city', 'province', 'postal_code',
        'description', 'photo', 'facilities', 'latitude', 'longitude', 'is_active'
    ];
    protected $casts = [
        'facilities' => 'json',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function owner() {
        return User::find($this->owner_id);
    }

    public function rooms() {
        return Room::where('property_id', $this->id);
    }

    public function getPhotoUrl() {
        if ($this->photo) {
            return asset('uploads/properties/' . $this->photo);
        }
        return asset('assets/images/property-placeholder.svg');
    }

    public function getFacilitiesArray() {
        return $this->facilities ?? [];
    }

    public function getOccupancyRate() {
        $total = $this->rooms()->count();
        if ($total === 0) return 0;
        $occupied = $this->rooms()->where('status', 'occupied')->count();
        return round(($occupied / $total) * 100, 1);
    }

    public function getTotalRevenue($month = null, $year = null) {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $db = db();
        return $db->table('invoices i')
            ->join('tenants t', 'i.tenant_id', '=', 't.id')
            ->join('rooms r', 't.room_id', '=', 'r.id')
            ->where('r.property_id', $this->id)
            ->where('i.period_month', $month)
            ->where('i.period_year', $year)
            ->where('i.status', 'paid')
            ->sum('i.total_amount');
    }

    public static function getUserProperties($userId) {
        $auth = auth();
        if ($auth && (int)$auth->id === (int)$userId && $auth->role !== 'owner') {
            return self::where('is_active', 1)->orderBy('name')->get();
        }
        return self::where('owner_id', $userId)->where('is_active', 1)->orderBy('name')->get();
    }
}