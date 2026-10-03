<?php

// Load .env file FIRST (before config)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key && $value !== '') {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Controllers/Controller.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Property.php';
require_once __DIR__ . '/../app/Models/Room.php';
require_once __DIR__ . '/../app/Models/Tenant.php';
require_once __DIR__ . '/../app/Models/Invoice.php';
require_once __DIR__ . '/../app/Models/Payment.php';
require_once __DIR__ . '/../app/Models/Complaint.php';
require_once __DIR__ . '/../app/Models/Notification.php';
require_once __DIR__ . '/../app/Models/Setting.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Controllers/PropertyController.php';
require_once __DIR__ . '/../app/Controllers/RoomController.php';
require_once __DIR__ . '/../app/Controllers/TenantController.php';
require_once __DIR__ . '/../app/Controllers/InvoiceController.php';
require_once __DIR__ . '/../app/Controllers/PaymentController.php';
require_once __DIR__ . '/../app/Controllers/MaintenanceController.php';
require_once __DIR__ . '/../app/Controllers/ReportController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';
require_once __DIR__ . '/../app/Controllers/NotificationController.php';
require_once __DIR__ . '/../app/Controllers/SettingController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => config('app.session.lifetime') * 60,
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// CORS headers for API
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize URI: remove /siskosan prefix and any public/index.php artifacts
$uri = str_replace('/siskosan', '', $uri);
$uri = str_replace('public/index.php', '', $uri);
$uri = trim($uri, '/');
$uri = $uri ?: 'home';

$method = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash
$uri = rtrim($uri, '/');

// Parse route parameters
$routeParams = [];

// Public routes
$publicRoutes = [
    'home' => ['controller' => null, 'method' => null, 'middleware' => 'any'],
    'login' => ['controller' => 'AuthController', 'method' => 'showLogin', 'middleware' => 'guest'],
    'login.post' => ['controller' => 'AuthController', 'method' => 'login', 'middleware' => 'guest'],
    'register' => ['controller' => 'AuthController', 'method' => 'showRegister', 'middleware' => 'guest'],
    'register.post' => ['controller' => 'AuthController', 'method' => 'register', 'middleware' => 'guest'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout', 'middleware' => 'auth'],
    'forgot-password' => ['controller' => 'AuthController', 'method' => 'showForgotPassword', 'middleware' => 'guest'],
    'forgot-password.post' => ['controller' => 'AuthController', 'method' => 'forgotPassword', 'middleware' => 'guest'],
    'reset-password/{token}' => ['controller' => 'AuthController', 'method' => 'showResetPassword', 'middleware' => 'guest'],
    'reset-password.post' => ['controller' => 'AuthController', 'method' => 'resetPassword', 'middleware' => 'guest'],
];

// Protected routes
$protectedRoutes = [
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index', 'middleware' => 'auth'],
    
    // Properties
    'properties' => ['controller' => 'PropertyController', 'method' => 'index', 'middleware' => 'auth'],
    'properties/create' => ['controller' => 'PropertyController', 'method' => 'create', 'middleware' => 'auth'],
    'properties.store' => ['controller' => 'PropertyController', 'method' => 'store', 'middleware' => 'auth'],
    'properties/{id}' => ['controller' => 'PropertyController', 'method' => 'show', 'middleware' => 'auth'],
    'properties/{id}/edit' => ['controller' => 'PropertyController', 'method' => 'edit', 'middleware' => 'auth'],
    'properties/{id}/update' => ['controller' => 'PropertyController', 'method' => 'update', 'middleware' => 'auth'],
    'properties/{id}/delete' => ['controller' => 'PropertyController', 'method' => 'destroy', 'middleware' => 'auth'],
    
    // Rooms
    'rooms' => ['controller' => 'RoomController', 'method' => 'index', 'middleware' => 'auth'],
    'rooms/create' => ['controller' => 'RoomController', 'method' => 'create', 'middleware' => 'auth'],
    'rooms.store' => ['controller' => 'RoomController', 'method' => 'store', 'middleware' => 'auth'],
    'rooms/{id}' => ['controller' => 'RoomController', 'method' => 'show', 'middleware' => 'auth'],
    'rooms/{id}/edit' => ['controller' => 'RoomController', 'method' => 'edit', 'middleware' => 'auth'],
    'rooms/{id}/update' => ['controller' => 'RoomController', 'method' => 'update', 'middleware' => 'auth'],
    'rooms/{id}/delete' => ['controller' => 'RoomController', 'method' => 'destroy', 'middleware' => 'auth'],
    'rooms/{id}/status' => ['controller' => 'RoomController', 'method' => 'updateStatus', 'middleware' => 'auth'],
    
    // Tenants
    'tenants' => ['controller' => 'TenantController', 'method' => 'index', 'middleware' => 'auth'],
    'tenants/create' => ['controller' => 'TenantController', 'method' => 'create', 'middleware' => 'auth'],
    'tenants.store' => ['controller' => 'TenantController', 'method' => 'store', 'middleware' => 'auth'],
    'tenants/{id}' => ['controller' => 'TenantController', 'method' => 'show', 'middleware' => 'auth'],
    'tenants/{id}/edit' => ['controller' => 'TenantController', 'method' => 'edit', 'middleware' => 'auth'],
    'tenants/{id}/update' => ['controller' => 'TenantController', 'method' => 'update', 'middleware' => 'auth'],
    'tenants/{id}/checkout' => ['controller' => 'TenantController', 'method' => 'checkout', 'middleware' => 'auth'],
    
    // Invoices
    'invoices' => ['controller' => 'InvoiceController', 'method' => 'index', 'middleware' => 'auth'],
    'invoices/create' => ['controller' => 'InvoiceController', 'method' => 'create', 'middleware' => 'auth'],
    'invoices.store' => ['controller' => 'InvoiceController', 'method' => 'store', 'middleware' => 'auth'],
    'invoices/{id}' => ['controller' => 'InvoiceController', 'method' => 'show', 'middleware' => 'auth'],
    'invoices/{id}/edit' => ['controller' => 'InvoiceController', 'method' => 'edit', 'middleware' => 'auth'],
    'invoices/{id}/update' => ['controller' => 'InvoiceController', 'method' => 'update', 'middleware' => 'auth'],
    'invoices/generate' => ['controller' => 'InvoiceController', 'method' => 'generateMonthly', 'middleware' => 'auth'],
    'invoices/bulk-status' => ['controller' => 'InvoiceController', 'method' => 'bulkUpdateStatus', 'middleware' => 'auth'],
    
    // Payments
    'payments' => ['controller' => 'PaymentController', 'method' => 'index', 'middleware' => 'auth'],
    'payments/create' => ['controller' => 'PaymentController', 'method' => 'create', 'middleware' => 'auth'],
    'payments.store' => ['controller' => 'PaymentController', 'method' => 'store', 'middleware' => 'auth'],
    'payments/verify-all' => ['controller' => 'PaymentController', 'method' => 'verifyAll', 'middleware' => 'auth'],
    'payments/{id}' => ['controller' => 'PaymentController', 'method' => 'show', 'middleware' => 'auth'],
    'payments/{id}/verify' => ['controller' => 'PaymentController', 'method' => 'verify', 'middleware' => 'auth'],
    'payments/{id}/reject' => ['controller' => 'PaymentController', 'method' => 'reject', 'middleware' => 'auth'],
    
    // Maintenance
    'maintenance' => ['controller' => 'MaintenanceController', 'method' => 'index', 'middleware' => 'auth'],
    'maintenance/create' => ['controller' => 'MaintenanceController', 'method' => 'create', 'middleware' => 'auth'],
    'maintenance.store' => ['controller' => 'MaintenanceController', 'method' => 'store', 'middleware' => 'auth'],
    'maintenance/{id}' => ['controller' => 'MaintenanceController', 'method' => 'show', 'middleware' => 'auth'],
    'maintenance/{id}/edit' => ['controller' => 'MaintenanceController', 'method' => 'edit', 'middleware' => 'auth'],
    'maintenance/{id}/update' => ['controller' => 'MaintenanceController', 'method' => 'update', 'middleware' => 'auth'],
    'maintenance/{id}/assign' => ['controller' => 'MaintenanceController', 'method' => 'assign', 'middleware' => 'auth'],
    'maintenance/{id}/resolve' => ['controller' => 'MaintenanceController', 'method' => 'resolve', 'middleware' => 'auth'],
    
    // Reports
    'reports' => ['controller' => 'ReportController', 'method' => 'index', 'middleware' => 'auth'],
    'reports/financial' => ['controller' => 'ReportController', 'method' => 'financial', 'middleware' => 'auth'],
    'reports/occupancy' => ['controller' => 'ReportController', 'method' => 'occupancy', 'middleware' => 'auth'],
    'reports/arrears' => ['controller' => 'ReportController', 'method' => 'arrears', 'middleware' => 'auth'],
    'reports/export/{type}' => ['controller' => 'ReportController', 'method' => 'export', 'middleware' => 'auth'],
    
    // Profile
    'profile' => ['controller' => 'ProfileController', 'method' => 'index', 'middleware' => 'auth'],
    'profile/edit' => ['controller' => 'ProfileController', 'method' => 'edit', 'middleware' => 'auth'],
    'profile/update' => ['controller' => 'ProfileController', 'method' => 'update', 'middleware' => 'auth'],
    
    // Notifications
    'notifications' => ['controller' => 'NotificationController', 'method' => 'index', 'middleware' => 'auth'],
    'notifications/{id}/read' => ['controller' => 'NotificationController', 'method' => 'markRead', 'middleware' => 'auth'],
    'notifications/read-all' => ['controller' => 'NotificationController', 'method' => 'markAllRead', 'middleware' => 'auth'],
    'notifications/unread-count' => ['controller' => 'NotificationController', 'method' => 'getUnreadCount', 'middleware' => 'auth'],
    'notifications/recent' => ['controller' => 'NotificationController', 'method' => 'getRecent', 'middleware' => 'auth'],
    
    // Settings
    'settings' => ['controller' => 'SettingController', 'method' => 'index', 'middleware' => 'auth'],
    'settings/update' => ['controller' => 'SettingController', 'method' => 'update', 'middleware' => 'auth'],
];

// Match route
$matchedRoute = null;
$allRoutes = array_merge($publicRoutes, $protectedRoutes);

// Build method-specific route maps
$methodUpper = $method; // GET, POST, etc.
$postRoutes = [];
foreach ($allRoutes as $pattern => $route) {
    if (substr($pattern, -5) === '.post') {
        $basePattern = substr($pattern, 0, -5);
        $postRoutes[$basePattern] = $route;
    }
}

foreach ($allRoutes as $pattern => $route) {
    // Skip .post routes from normal matching
    if (substr($pattern, -5) === '.post') continue;
    
    // Convert pattern to regex
    $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
    $regex = '^' . $regex . '$';
    
    if (preg_match('#' . $regex . '#', $uri, $matches)) {
        // If POST request and a .post route exists for this pattern, use it
        if ($methodUpper === 'POST' && isset($postRoutes[$pattern])) {
            $matchedRoute = $postRoutes[$pattern];
        } else {
            $matchedRoute = $route;
        }
        array_shift($matches); // Remove full match
        $paramNames = [];
        preg_match_all('/\{(\w+)\}/', $pattern, $paramNames);
        foreach ($paramNames[1] as $i => $name) {
            $routeParams[$name] = $matches[$i] ?? null;
        }
        break;
    }
}

if (!$matchedRoute) {
    // Try to match with method suffix for POST
    $postUri = $uri;
    if ($methodUpper === 'POST') {
        // Check for .post suffix routes
        foreach ($allRoutes as $pattern => $route) {
            if (strpos($pattern, '.post') !== false) {
                $basePattern = str_replace('.post', '', $pattern);
                $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $basePattern);
                $regex = '^' . $regex . '$';
                
                if (preg_match('#' . $regex . '#', $uri, $matches)) {
                    $matchedRoute = $route;
                    array_shift($matches);
                    $paramNames = [];
                    preg_match_all('/\{(\w+)\}/', $basePattern, $paramNames);
                    foreach ($paramNames[1] as $i => $name) {
                        $routeParams[$name] = $matches[$i] ?? null;
                    }
                    break;
                }
            }
        }
    }
}

if (!$matchedRoute) {
    // Try home redirect
    if ($uri === '' || $uri === 'home') {
        if (auth_check()) {
            redirect(url('dashboard'));
        } else {
            redirect(url('login'));
        }
    }
    
    // 404
    http_response_code(404);
    $layoutFile = view_path('layouts/app.php');
    if (file_exists($layoutFile)) {
        $auth = auth();
        $app_name = config('app.name');
        $unreadCount = 0;
        $notifications = [];
        $title = '404';
        $content = '<div class="min-h-screen flex items-center justify-center bg-gray-50"><div class="text-center"><h1 class="text-4xl font-bold text-gray-900">404</h1><p class="text-gray-600 mt-2">Halaman tidak ditemukan</p><a href="' . url($auth ? 'dashboard' : 'login') . '" class="text-blue-600 hover:underline mt-4 inline-block">Kembali</a></div></div>';
        include $layoutFile;
    } else {
        echo '<h1>404 - Not Found</h1>';
    }
    exit;
}

// Home: redirect based on auth
if ($matchedRoute && ($matchedRoute['controller'] ?? null) === null) {
    if (auth_check()) {
        redirect(url('dashboard'));
    } else {
        redirect(url('login'));
    }
}

// Check middleware
$middleware = $matchedRoute['middleware'] ?? 'auth';

if ($middleware === 'auth' && !auth_check()) {
    redirect(url('login'));
}

if ($middleware === 'guest' && auth_check()) {
    redirect(url('dashboard'));
}

// Check CSRF for POST requests (all routes)
if ($methodUpper === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Token CSRF tidak valid, silakan coba lagi');
        back();
    }
}

// Store route params in $_GET for controllers
$_GET = array_merge($_GET, $routeParams);

// Dispatch controller
$controllerClass = $matchedRoute['controller'];
$methodName = $matchedRoute['method'];

try {
    $controller = new $controllerClass();
    $controller->$methodName(...array_values($routeParams));
} catch (\Throwable $e) {
    if (config('app.debug')) {
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:5px;font-family:monospace;">';
        echo 'Error: ' . $e->getMessage() . "\n";
        echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo 'Stack Trace:' . "\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 - Internal Server Error</h1><p>Terjadi kesalahan pada server. Silakan coba lagi nanti.</p>';
    }
}