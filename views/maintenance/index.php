<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Maintenance & Komplain</h1>
            <p class="text-gray-600">Kelola komplain dan perbaikan</p>
        </div>
        <?php if (auth()->role !== 'tenant'): ?>
        <a href="<?= url('maintenance/create') ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Tambah Komplain</a>
        <?php endif; ?>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Dibuka</p>
            <p class="text-2xl font-bold text-blue-600"><?= $stats['open'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Diproses</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $stats['in_progress'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600"><?= $stats['resolved'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Ditutup</p>
            <p class="text-2xl font-bold text-gray-600"><?= $stats['closed'] ?></p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <select name="property_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Properti</option>
                    <?php foreach ($properties as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $propertyId == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Dibuka</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>Diproses</option>
                    <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Selesai</option>
                    <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Ditutup</option>
                </select>
            </div>
            <div class="flex-1">
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
            <?php if ($propertyId || $status || $category): ?>
            <a href="<?= url('maintenance') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- List -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($complaints['data'])): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($complaints['data'] as $complaint): ?>
            <a href="<?= url('maintenance/' . $complaint['id']) ?>" class="block px-5 py-4 hover:bg-gray-50 flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center <?= $complaint['category'] === 'electricity' ? 'bg-yellow-100 text-yellow-600' : ($complaint['category'] === 'water' ? 'bg-blue-100 text-blue-600' : ($complaint['category'] === 'internet' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600')) ?>">
                    <?php if ($complaint['category'] === 'electricity'): ?>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <?php elseif ($complaint['category'] === 'water'): ?>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 1010 10A10 10 0 0012 2z"/></svg>
                    <?php elseif ($complaint['category'] === 'internet'): ?>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <?php else: ?>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($complaint['title']) ?></h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint->getPriorityBadgeClass() ?>">
                            <?= $complaint->getPriorityLabel() ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($complaint['description']) ?></p>
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                        <span><?= htmlspecialchars($complaint['tenant_name']) ?> - Kamar <?= htmlspecialchars($complaint['room_number']) ?></span>
                        <span><?= htmlspecialchars($complaint['property_name']) ?></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint->getStatusBadgeClass() ?>">
                            <?= $complaint->getStatusLabel() ?>
                        </span>
                        <?php if ($complaint['assignee_name']): ?>
                        <span>PIC: <?= htmlspecialchars($complaint['assignee_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap"><?= time_ago($complaint['created_at']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($complaints['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $complaints['from'] ?>-<?= $complaints['to'] ?> dari <?= $complaints['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($complaints['current_page'] > 1): ?>
                    <a href="?page=<?= $complaints['current_page'] - 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&category=<?= $category ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($complaints['has_more_pages']): ?>
                    <a href="?page=<?= $complaints['current_page'] + 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&category=<?= $category ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada komplain</h3>
            <p class="mt-1 text-sm text-gray-500">Belum ada komplain untuk filter ini.</p>
        </div>
        <?php endif; ?>
    </div>
</div>