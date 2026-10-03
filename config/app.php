<?php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        $lower = strtolower($value);
        if ($lower === 'true' || $lower === '1' || $lower === 'on' || $lower === 'yes') {
            return true;
        }
        if ($lower === 'false' || $lower === '0' || $lower === 'off' || $lower === 'no') {
            return false;
        }
        if ($value !== '' && is_numeric($value)) {
            return $value + 0;
        }
        return $value;
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '') {
        return __DIR__ . '/../public' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

$configData = [
    'app' => [
        'name' => env('APP_NAME', 'KosManager'),
        'env' => env('APP_ENV', 'development'),
        'debug' => env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://localhost/siskosan'),
        'timezone' => 'Asia/Jakarta',
        'locale' => 'id',
        'currency' => [
            'code' => env('APP_CURRENCY', 'IDR'),
            'symbol' => env('CURRENCY_SYMBOL', 'Rp'),
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimals' => 0,
        ],
        'pagination' => [
            'per_page' => 15,
        ],
        'upload' => [
            'path' => public_path('uploads'),
            'max_size' => 5120,
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
        ],
        'notification' => [
            'reminder_days' => [3, 1],
            'email_enabled' => env('NOTIFICATION_EMAIL_ENABLED', false),
            'whatsapp_enabled' => env('NOTIFICATION_WHATSAPP_ENABLED', false),
            'whatsapp_api_url' => env('WHATSAPP_API_URL', ''),
            'whatsapp_api_token' => env('WHATSAPP_API_TOKEN', ''),
        ],
        'billing' => [
            'due_day' => (int) env('INVOICE_DUE_DAY', 5),
            'late_fee_percentage' => (float) env('LATE_FEE_PERCENTAGE', 2),
            'auto_generate_invoices' => true,
        ],
        'session' => [
            'lifetime' => 120,
            'remember_me' => 2628000,
        ],
    ],
];

$GLOBALS['app_config'] = $configData;

if (!function_exists('config')) {
    function config($key, $default = null) {
        $config = $GLOBALS['app_config'] ?? [];
        $keys = explode('.', $key);
        $value = $config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }
}

return $configData;