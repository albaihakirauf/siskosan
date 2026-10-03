<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Install KosManager</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
    <style>* { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class='min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4'>
    <div class='max-w-2xl w-full space-y-8'>
        <div class='text-center'>
            <div class='mx-auto h-16 w-16 bg-primary-600 rounded-2xl flex items-center justify-center'>
                <svg class='h-10 w-10 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/>
                </svg>
            </div>
            <h1 class='mt-6 text-3xl font-bold text-gray-900'>KosManager</h1>
            <p class='mt-2 text-gray-600'>Instalasi Aplikasi Manajemen Kos-Kosan</p>
        </div>

        <div class='bg-white rounded-xl border border-gray-200 p-8'>
            <h2 class='text-xl font-semibold text-gray-900 mb-6'>Konfigurasi Database</h2>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? 'localhost';
    $port = $_POST['db_port'] ?? 3306;
    $database = $_POST['db_name'] ?? 'kosmanager';
    $username = $_POST['db_user'] ?? 'root';
    $password = $_POST['db_pass'] ?? '';
    $appUrl = $_POST['app_url'] ?? 'http://localhost/siskosan';

    try {
        // Test connection without database
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$database}`");
        
        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $stmt) {
            if ($stmt) {
                $pdo->exec($stmt . ';');
            }
        }
        
        // Update .env file
        $envContent = "APP_NAME=KosManager
APP_ENV=production
APP_DEBUG=false
APP_URL={$appUrl}

DB_CONNECTION=mysql
DB_HOST={$host}
DB_PORT={$port}
DB_DATABASE={$database}
DB_USERNAME={$username}
DB_PASSWORD={$password}

APP_CURRENCY=IDR
CURRENCY_SYMBOL=Rp

INVOICE_DUE_DAY=5
LATE_FEE_PERCENTAGE=2

NOTIFICATION_EMAIL_ENABLED=false
NOTIFICATION_WHATSAPP_ENABLED=false
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_TOKEN=
";
        file_put_contents(__DIR__ . '/.env', $envContent);
        
        echo "<div class='mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800'>
                <div class='flex items-center gap-3'>
                    <svg class='h-6 w-6 flex-shrink-0' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/></svg>
                    <div>
                        <h3 class='font-semibold'>Instalasi Berhasil!</h3>
                        <p class='text-sm'>Database telah dibuat dan tabel-tabel telah diisi dengan data default.</p>
                    </div>
                </div>
              </div>";
        
        echo "<div class='text-center'>
                <a href='{$appUrl}/login' class='inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold text-lg rounded-lg shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200' style='background-color: #2563eb; color: white; border: none; text-decoration: none; display: inline-flex;'>
                    <svg class='h-5 w-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'/></svg>
                    Login ke KosManager
                </a>
              </div>";
        
        echo "</div></div></body></html>";
        exit;
    } catch (PDOException $e) {
        $error = "Koneksi database gagal: " . $e->getMessage();
    }
}

if (isset($error)) {
    echo "<div class='mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800'>
            <div class='flex items-center gap-3'>
                <svg class='h-6 w-6 flex-shrink-0' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/></svg>
                <span>{$error}</span>
            </div>
          </div>";
}

echo "
            <form method='POST' class='space-y-4'>
                <div>
                    <label class='block text-sm font-medium text-gray-700'>Host Database</label>
                    <input type='text' name='db_host' required value='localhost'
                           class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                </div>
                <div class='grid grid-cols-2 gap-4'>
                    <div>
                        <label class='block text-sm font-medium text-gray-700'>Port</label>
                        <input type='number' name='db_port' required value='3306'
                               class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                    </div>
                    <div>
                        <label class='block text-sm font-medium text-gray-700'>Nama Database</label>
                        <input type='text' name='db_name' required value='kosmanager'
                               class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                    </div>
                </div>
                <div class='grid grid-cols-2 gap-4'>
                    <div>
                        <label class='block text-sm font-medium text-gray-700'>Username</label>
                        <input type='text' name='db_user' required value='root'
                               class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                    </div>
                    <div>
                        <label class='block text-sm font-medium text-gray-700'>Password</label>
                        <input type='password' name='db_pass' value=''
                               class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                    </div>
                </div>
                <div>
                    <label class='block text-sm font-medium text-gray-700'>URL Aplikasi</label>
                    <input type='url' name='app_url' required value='http://localhost/siskosan'
                           class='mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'>
                </div>
                <button type='submit' class='w-full py-4 bg-blue-600 text-white font-semibold text-lg rounded-lg shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200' style='background-color: #2563eb; color: white; border: none;'>
                    Install Sekarang
                </button>
            </form>
        </div>
    </div>
</body>
</html>";