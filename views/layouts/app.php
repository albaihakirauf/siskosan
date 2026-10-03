<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $app_name ?? 'KosManager' ?> - <?= isset($title) ? $title . ' | ' : '' ?>Manajemen Kos-Kosan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) {
            .sidebar-open { transform: translateX(0) !important; }
            .sidebar-closed { transform: translateX(-100%) !important; }
        }
        .sidebar { transform: translateX(0); }
        @media (max-width: 1023px) { .sidebar { transform: translateX(-100%); } }
    </style>
</head>
<body class="h-full bg-gray-50 flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 sidebar-transition lg:static lg:translate-x-0" aria-label="Sidebar">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <a href="<?= url('dashboard') ?>" class="flex items-center gap-2">
                    <div class="h-10 w-10 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900"><?= $app_name ?? 'KosManager' ?></span>
                </a>
                <button id="sidebar-close" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Tutup sidebar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto" aria-label="Navigasi utama">
                <?php 
                $currentUri = $_SERVER['REQUEST_URI'];
                $currentUri = str_replace('/siskosan', '', $currentUri);
                $currentUri = trim($currentUri, '/');
                $currentUri = $currentUri ?: 'dashboard';
                
                $navItems = [
                    ['icon' => 'home', 'label' => 'Dashboard', 'url' => 'dashboard', 'roles' => ['owner', 'admin', 'tenant']],
                    ['icon' => 'building', 'label' => 'Properti', 'url' => 'properties', 'roles' => ['owner', 'admin']],
                    ['icon' => 'door', 'label' => 'Kamar', 'url' => 'rooms', 'roles' => ['owner', 'admin']],
                    ['icon' => 'users', 'label' => 'Penyewa', 'url' => 'tenants', 'roles' => ['owner', 'admin']],
                    ['icon' => 'file-text', 'label' => 'Tagihan', 'url' => 'invoices', 'roles' => ['owner', 'admin', 'tenant']],
                    ['icon' => 'credit-card', 'label' => 'Pembayaran', 'url' => 'payments', 'roles' => ['owner', 'admin', 'tenant']],
                    ['icon' => 'tool', 'label' => 'Maintenance', 'url' => 'maintenance', 'roles' => ['owner', 'admin', 'tenant']],
                    ['icon' => 'bar-chart', 'label' => 'Laporan', 'url' => 'reports', 'roles' => ['owner', 'admin']],
                    ['icon' => 'settings', 'label' => 'Pengaturan', 'url' => 'settings', 'roles' => ['owner']],
                ];
                
                $icons = [
                    'home' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                    'building' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    'door' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    'users' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                    'file-text' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'credit-card' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                    'tool' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'bar-chart' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                    'settings' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                ];
                
                $userRole = $auth->role ?? 'guest';
                foreach ($navItems as $item): 
                    if (!in_array($userRole, $item['roles'])) continue;
                    $isActive = strpos($currentUri, $item['url']) === 0;
                ?>
                <a href="<?= url($item['url']) ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isActive ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' ?>">
                    <?= $icons[$item['icon']] ?>
                    <?= $item['label'] ?>
                </a>
                <?php endforeach; ?>
            </nav>

        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 hidden lg:hidden" aria-hidden="true"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
        <!-- Top Bar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 lg:flex-none">
                    <h1 class="text-lg font-semibold text-gray-900"><?= $title ?? 'Dashboard' ?></h1>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Notifications Dropdown -->
                    <div class="relative">
                        <button id="notif-btn" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Notifikasi">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <?php if (($unreadCount ?? 0) > 0): ?>
                            <span class="absolute -top-1 -right-1 h-5 min-w-5 bg-red-500 text-white text-xs font-medium rounded-full px-1.5 text-center"><?= min($unreadCount, 99) ?></span>
                            <?php endif; ?>
                        </button>
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <?php
                            $dropdownNotifs = $notifications ?? [];
                            if (is_array($dropdownNotifs) && isset($dropdownNotifs['data'])) {
                                $dropdownNotifs = $dropdownNotifs['data'];
                            }
                            ?>
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">Notifikasi</h3>
                                <?php if (($unreadCount ?? 0) > 0): ?>
                                <button onclick="markAllRead()" class="text-xs text-primary-600 hover:underline">Tandai semua dibaca</button>
                                <?php endif; ?>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <?php if (!empty($dropdownNotifs) && is_array($dropdownNotifs)): ?>
                                    <?php foreach ($dropdownNotifs as $notif): ?>
                                    <?php if (!is_object($notif) || !method_exists($notif, 'getReferenceUrl')) continue; ?>
                                    <a href="<?= $notif->getReferenceUrl() ?>" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 <?= $notif->is_read ? '' : 'bg-primary-50/50' ?>">
                                        <div class="flex items-start gap-3">
                                            <div class="p-1.5 rounded-full bg-primary-100 text-primary-600">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($notif->title) ?></p>
                                                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($notif->message) ?></p>
                                                <p class="text-xs text-gray-400 mt-1"><?= time_ago($notif->created_at) ?></p>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="px-4 py-6 text-center text-gray-500 text-sm">Tidak ada notifikasi</div>
                                <?php endif; ?>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200">
                                <a href="<?= url('notifications') ?>" class="text-sm text-primary-600 hover:underline block text-center">Lihat semua notifikasi</a>
                            </div>
                        </div>
                    </div>
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="user-btn" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <?php if ($auth): ?>
                            <img src="<?= $auth->getAvatarUrl() ?>" alt="" class="h-8 w-8 rounded-full ring-2 ring-gray-200">
                            <span class="hidden sm:block text-sm font-medium text-gray-700"><?= htmlspecialchars($auth->name) ?></span>
                            <?php else: ?>
                            <span class="hidden sm:block text-sm font-medium text-gray-700">Masuk</span>
                            <?php endif; ?>
                            <svg class="hidden sm:block h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <?php if ($auth): ?>
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($auth->name) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($auth->email) ?></p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 mt-1 capitalize"><?= $auth->role ?></span>
                            </div>
                            <a href="<?= url('profile') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil Saya
                            </a>
                            <a href="<?= url('notifications') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Notifikasi
                                <?php if (($unreadCount ?? 0) > 0): ?>
                                <span class="ml-auto h-5 min-w-5 bg-primary-600 text-white text-xs font-medium rounded-full px-1.5 text-center"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form method="POST" action="<?= url('logout') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                            <?php else: ?>
                            <a href="<?= url('login') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Masuk</a>
                            <a href="<?= url('register') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Daftar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php if (has_flash('success')): ?>
            <div id="flash-success" class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800" role="alert">
                <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span><?= flash('success') ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-green-500 hover:text-green-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <?php endif; ?>
            
            <?php if (has_flash('error')): ?>
            <div id="flash-error" class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800" role="alert">
                <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span><?= flash('error') ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            sidebar.classList.remove('sidebar-closed');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('sidebar-closed');
            sidebar.classList.remove('sidebar-open');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        toggleBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Notification dropdown
        const notifBtn = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');

        notifBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!notifBtn?.contains(e.target) && !notifDropdown?.contains(e.target)) {
                notifDropdown?.classList.add('hidden');
            }
            if (!userBtn?.contains(e.target) && !userDropdown?.contains(e.target)) {
                userDropdown?.classList.add('hidden');
            }
        });

        // User dropdown
        const userBtn = document.getElementById('user-btn');
        const userDropdown = document.getElementById('user-dropdown');
        userBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown?.classList.add('hidden');
            userDropdown.classList.toggle('hidden');
        });

        // Mark all notifications as read
        async function markAllRead() {
            try {
                await fetch('<?= url('notifications/read-all') ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= csrf_token() ?>',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                location.reload();
            } catch (e) { console.error(e); }
        }

        // Auto-hide flash messages
        setTimeout(() => {
            document.querySelectorAll('#flash-success, #flash-error').forEach(el => {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            });
        }, 5000);

        // Confirm delete
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-confirm]') || e.target.closest('[data-confirm]')) {
                const btn = e.target.matches('[data-confirm]') ? e.target : e.target.closest('[data-confirm]');
                const msg = btn.getAttribute('data-confirm') || 'Yakin ingin menghapus?';
                if (!confirm(msg)) e.preventDefault();
            }
        });
    </script>
</body>
</html>