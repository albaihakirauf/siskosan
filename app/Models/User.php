<?php

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 
        'avatar', 'is_active', 'email_verified_at', 'remember_token'
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];
    protected $dates = ['created_at', 'updated_at', 'email_verified_at'];

    public function getAvatarUrl() {
        if ($this->avatar) {
            return asset('uploads/avatars/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563EB&color=fff&size=200';
    }

    public function properties() {
        return Property::where('owner_id', $this->id);
    }

    public function tenant() {
        return Tenant::where('user_id', $this->id)->first();
    }

    public function notifications() {
        return Notification::where('user_id', $this->id)->orderBy('created_at', 'DESC');
    }

    public function unreadNotificationsCount() {
        return Notification::where('user_id', $this->id)->where('is_read', 0)->count();
    }

    public function isOwner() {
        return $this->role === 'owner';
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isTenant() {
        return $this->role === 'tenant';
    }
}