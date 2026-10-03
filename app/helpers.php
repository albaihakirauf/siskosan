<?php

require_once __DIR__ . '/Database.php';

function app_path($path = '') {
    return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
}

function view_path($path = '') {
    return __DIR__ . '/../views' . ($path ? '/' . ltrim($path, '/') : '');
}

function config_path($path = '') {
    return __DIR__ . '/../config' . ($path ? '/' . ltrim($path, '/') : '');
}

function database_path($path = '') {
    return __DIR__ . '/../database' . ($path ? '/' . ltrim($path, '/') : '');
}

function asset($path = '') {
    $baseUrl = config('app.url');
    return $baseUrl . '/public' . ($path ? '/' . ltrim($path, '/') : '');
}

function url($path = '') {
    $baseUrl = config('app.url');
    return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
}

function route($name, $params = []) {
    $routes = [
        'home' => '',
        'login' => 'login',
        'register' => 'register',
        'logout' => 'logout',
        'dashboard' => 'dashboard',
        'profile' => 'profile',
        'profile.edit' => 'profile/edit',
        'properties.index' => 'properties',
        'properties.create' => 'properties/create',
        'properties.edit' => 'properties/{id}/edit',
        'properties.show' => 'properties/{id}',
        'rooms.index' => 'rooms',
        'rooms.create' => 'rooms/create',
        'rooms.edit' => 'rooms/{id}/edit',
        'rooms.show' => 'rooms/{id}',
        'tenants.index' => 'tenants',
        'tenants.create' => 'tenants/create',
        'tenants.edit' => 'tenants/{id}/edit',
        'tenants.show' => 'tenants/{id}',
        'payments.index' => 'payments',
        'payments.create' => 'payments/create',
        'payments.edit' => 'payments/{id}/edit',
        'payments.show' => 'payments/{id}',
        'invoices.index' => 'invoices',
        'invoices.create' => 'invoices/create',
        'invoices.edit' => 'invoices/{id}/edit',
        'invoices.show' => 'invoices/{id}',
        'reports.index' => 'reports',
        'reports.financial' => 'reports/financial',
        'reports.occupancy' => 'reports/occupancy',
        'reports.arrears' => 'reports/arrears',
        'maintenance.index' => 'maintenance',
        'maintenance.create' => 'maintenance/create',
        'maintenance.edit' => 'maintenance/{id}/edit',
        'maintenance.show' => 'maintenance/{id}',
        'notifications.index' => 'notifications',
    ];

    $route = $routes[$name] ?? $name;
    
    foreach ($params as $key => $value) {
        $route = str_replace("{{$key}}", $value, $route);
        $route = str_replace("{$key}", $value, $route);
    }

    return url($route);
}

function redirect($url, $statusCode = 302) {
    header("Location: {$url}", true, $statusCode);
    exit;
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    redirect($referer);
}

function abort($code, $message = '') {
    http_response_code($code);
    $messages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        419 => 'Page Expired',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
    ];
    $msg = $message ?: ($messages[$code] ?? 'Error');
    die("<h1>{$code} - {$msg}</h1>");
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf($token = null) {
    $token = $token ?? ($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function flash($key, $message = null) {
    if ($message === null) {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    $_SESSION['flash'][$key] = $message;
}

function has_flash($key) {
    return isset($_SESSION['flash'][$key]);
}

function format_currency($amount, $symbol = null, $decimals = null) {
    $symbol = $symbol ?? config('app.currency.symbol');
    $decimals = $decimals ?? config('app.currency.decimals');
    $decimalSep = config('app.currency.decimal_separator');
    $thousandSep = config('app.currency.thousand_separator');
    
    return $symbol . ' ' . number_format($amount, $decimals, $decimalSep, $thousandSep);
}

function format_date($date, $format = 'd M Y') {
    if (!$date) return '-';
    if (!is_string($date) && !$date instanceof DateTime) return '-';
    if (is_string($date) && !strtotime($date)) return $date;
    $dt = is_string($date) ? new DateTime($date) : $date;
    return $dt->format($format);
}

function format_datetime($date, $format = 'd M Y H:i') {
    if (!$date) return '-';
    if (!is_string($date) && !$date instanceof DateTime) return '-';
    if (is_string($date) && !strtotime($date)) return $date;
    $dt = is_string($date) ? new DateTime($date) : $date;
    return $dt->format($format);
}

function time_ago($date) {
    if (!$date) return '-';
    $dt = is_string($date) ? new DateTime($date) : $date;
    $now = new DateTime();
    $diff = $now->diff($dt);
    
    if ($diff->y > 0) return $diff->y . ' tahun yang lalu';
    if ($diff->m > 0) return $diff->m . ' bulan yang lalu';
    if ($diff->d > 0) return $diff->d . ' hari yang lalu';
    if ($diff->h > 0) return $diff->h . ' jam yang lalu';
    if ($diff->i > 0) return $diff->i . ' menit yang lalu';
    return 'Baru saja';
}

function str_slug($string, $separator = '-') {
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $string);
    $string = trim($string, $separator);
    return $string;
}

function str_limit($string, $limit = 100, $end = '...') {
    if (mb_strlen($string) <= $limit) {
        return $string;
    }
    return mb_substr($string, 0, $limit) . $end;
}

function uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0x0fff, 0x3fff) | 0x4000,
        mt_rand(0x8000, 0xbfff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generate_invoice_number($tenantId) {
    $date = new DateTime();
    $prefix = 'INV';
    $dateStr = $date->format('ym');
    $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return "{$prefix}-{$dateStr}-{$tenantId}-{$random}";
}

function upload_file($file, $directory = 'uploads', $allowedTypes = null, $maxSize = null) {
    $allowedTypes = $allowedTypes ?? config('app.upload.allowed_types');
    $maxSize = $maxSize ?? config('app.upload.max_size') * 1024; // Convert to bytes
    
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload gagal: ' . $file['error']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (maks ' . config('app.upload.max_size') . ' KB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    $uploadDir = public_path($directory);
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $directory . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal memindahkan file'];
}

function delete_file($path) {
    $fullPath = public_path($path);
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validate($data, $rules, $messages = []) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        $ruleParts = explode('|', $rule);
        
        foreach ($ruleParts as $r) {
            $params = [];
            if (preg_match('/(\w+)\((.*)\)/', $r, $matches)) {
                $r = $matches[1];
                $params = explode(',', $matches[2]);
            }
            
            switch ($r) {
                case 'required':
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $errors[$field][] = $messages["{$field}.required"] ?? "{$field} wajib diisi";
                    }
                    break;
                case 'email':
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = $messages["{$field}.email"] ?? "{$field} harus berupa email yang valid";
                    }
                    break;
                case 'numeric':
                    if ($value && !is_numeric($value)) {
                        $errors[$field][] = $messages["{$field}.numeric"] ?? "{$field} harus berupa angka";
                    }
                    break;
                case 'integer':
                    if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field][] = $messages["{$field}.integer"] ?? "{$field} harus berupa bilangan bulat";
                    }
                    break;
                case 'min':
                    if ($value !== null) {
                        $min = $params[0] ?? 0;
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $errors[$field][] = $messages["{$field}.min"] ?? "{$field} minimal {$min} karakter";
                        } elseif (is_numeric($value) && $value < $min) {
                            $errors[$field][] = $messages["{$field}.min"] ?? "{$field} minimal {$min}";
                        } elseif (is_array($value) && count($value) < $min) {
                            $errors[$field][] = $messages["{$field}.min"] ?? "{$field} minimal {$min} item";
                        }
                    }
                    break;
                case 'max':
                    if ($value !== null) {
                        $max = $params[0] ?? 0;
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $errors[$field][] = $messages["{$field}.max"] ?? "{$field} maksimal {$max} karakter";
                        } elseif (is_numeric($value) && $value > $max) {
                            $errors[$field][] = $messages["{$field}.max"] ?? "{$field} maksimal {$max}";
                        } elseif (is_array($value) && count($value) > $max) {
                            $errors[$field][] = $messages["{$field}.max"] ?? "{$field} maksimal {$max} item";
                        }
                    }
                    break;
                case 'in':
                    if ($value && !in_array($value, $params)) {
                        $errors[$field][] = $messages["{$field}.in"] ?? "{$field} tidak valid";
                    }
                    break;
                case 'unique':
                    if ($value) {
                        [$table, $column] = $params + [null, $field];
                        $db = db();
                        $exists = $db->table($table)->where($column, $value)->exists();
                        if ($exists) {
                            $errors[$field][] = $messages["{$field}.unique"] ?? "{$field} sudah digunakan";
                        }
                    }
                    break;
                case 'exists':
                    if ($value) {
                        [$table, $column] = $params + [null, 'id'];
                        $db = db();
                        $exists = $db->table($table)->where($column, $value)->exists();
                        if (!$exists) {
                            $errors[$field][] = $messages["{$field}.exists"] ?? "{$field} tidak ditemukan";
                        }
                    }
                    break;
                case 'confirmed':
                    if ($value !== ($data[$field . '_confirmation'] ?? null)) {
                        $errors[$field][] = $messages["{$field}.confirmed"] ?? "{$field} konfirmasi tidak cocok";
                    }
                    break;
                case 'date':
                    if ($value && !$value instanceof DateTime) {
                        $dt = DateTime::createFromFormat('Y-m-d', $value);
                        if (!$dt || $dt->format('Y-m-d') !== $value) {
                            $errors[$field][] = $messages["{$field}.date"] ?? "{$field} harus berupa tanggal yang valid (YYYY-MM-DD)";
                        }
                    }
                    break;
                case 'file':
                    if (!isset($data[$field]) || $data[$field]['error'] !== UPLOAD_ERR_OK) {
                        $errors[$field][] = $messages["{$field}.file"] ?? "{$field} wajib diupload";
                    }
                    break;
            }
        }
    }
    
    return $errors;
}

function dd(...$vars) {
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:5px;font-family:monospace;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

function log_activity($action, $description = null, $subjectType = null, $subjectId = null) {
    $userId = auth()->id ?? null;
    $db = db();
    $db->table('activity_logs')->insert([
        'user_id' => $userId,
        'action' => $action,
        'description' => $description,
        'subject_type' => $subjectType,
        'subject_id' => $subjectId,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}

function send_notification($userId, $type, $title, $message, $referenceType = null, $referenceId = null) {
    $db = db();
    $db->table('notifications')->insert([
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
    ]);
}

function send_whatsapp($phone, $message) {
    if (!config('app.notification.whatsapp_enabled')) {
        return false;
    }
    
    $token = config('app.notification.whatsapp_api_token');
    $url = config('app.notification.whatsapp_api_url');
    
    if (!$token || !$url) {
        return false;
    }
    
    $data = [
        'target' => $phone,
        'message' => $message,
        'countryCode' => '62',
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}