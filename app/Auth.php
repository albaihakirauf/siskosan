<?php

class Auth {
    private static $user = null;
    private static $checked = false;

    public static function user() {
        if (self::$checked) {
            return self::$user;
        }
        
        self::$checked = true;
        
        if (isset($_SESSION['user_id'])) {
            $db = db();
            $user = $db->table('users')->where('id', $_SESSION['user_id'])->where('is_active', 1)->first();
            if ($user) {
                self::$user = new User((array) $user);
                return self::$user;
            }
        }
        
        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            $db = db();
            $user = $db->table('users')->where('remember_token', $_COOKIE['remember_token'])->where('is_active', 1)->first();
            if ($user) {
                self::login($user);
                return self::$user;
            }
        }
        
        return null;
    }

    public static function id() {
        return self::user()->id ?? null;
    }

    public static function check() {
        return self::user() !== null;
    }

    public static function guest() {
        return !self::check();
    }

    public static function login($user, $remember = false) {
        if (is_array($user)) {
            $user = new User($user);
        } elseif (is_object($user) && !($user instanceof User)) {
            $user = new User((array) $user);
        }
        
        $_SESSION['user_id'] = $user->id;
        self::$user = $user;
        self::$checked = true;
        
        if ($remember) {
            $token = bin2hex(random_bytes(64));
            $db = db();
            $db->table('users')->where('id', $user->id)->update(['remember_token' => $token]);
            setcookie('remember_token', $token, time() + config('app.session.remember_me'), '/', '', false, true);
        }
        
        // Regenerate session ID for security
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
        return true;
    }

    public static function logout() {
        $user = self::user();
        if ($user) {
            $db = db();
            $db->table('users')->where('id', $user->id)->update(['remember_token' => null]);
        }
        
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        session_destroy();
        self::$user = null;
        self::$checked = false;
    }

    public static function attempt($email, $password, $remember = false) {
        $db = db();
        $user = $db->table('users')->where('email', $email)->where('is_active', 1)->first();
        
        if ($user && password_verify($password, $user['password'])) {
            self::login($user, $remember);
            return true;
        }
        
        return false;
    }

    public static function register($data) {
        $db = db();
        
        // Check if email exists
        if ($db->table('users')->where('email', $data['email'])->exists()) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }
        
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['role'] = $data['role'] ?? 'tenant';
        
        $id = $db->table('users')->insert($data);
        
        return ['success' => true, 'user_id' => $id];
    }

    public static function hasRole($role) {
        $user = self::user();
        if (!$user) return false;
        
        if (is_array($role)) {
            return in_array($user->role, $role);
        }
        
        return $user->role === $role;
    }

    public static function isOwner() {
        return self::hasRole('owner');
    }

    public static function isAdmin() {
        return self::hasRole('admin');
    }

    public static function isTenant() {
        return self::hasRole('tenant');
    }

    public static function can($permission) {
        $user = self::user();
        if (!$user) return false;
        
        // Owner can do everything
        if ($user->role === 'owner') return true;
        
        $permissions = [
            'admin' => [
                'view_dashboard',
                'manage_properties',
                'manage_rooms',
                'manage_tenants',
                'manage_payments',
                'manage_invoices',
                'manage_complaints',
                'view_reports',
                'manage_users',
            ],
            'tenant' => [
                'view_dashboard',
                'view_own_invoices',
                'view_own_payments',
                'create_complaint',
                'view_own_complaints',
                'update_profile',
            ],
        ];
        
        $userPermissions = $permissions[$user->role] ?? [];
        return in_array($permission, $userPermissions);
    }

    public static function authorize($permission) {
        if (!self::can($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan aksi ini');
        }
    }
}

function auth() {
    return Auth::user();
}

function auth_id() {
    return Auth::id();
}

function auth_check() {
    return Auth::check();
}

function auth_guest() {
    return Auth::guest();
}