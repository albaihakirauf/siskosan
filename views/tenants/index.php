<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Penyewa</h1>
            <p class="text-gray-600">Kelola data penyewa kos-kosan</p>
        </div>
        <a href="<?= url('tenants/create') ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Tambah Penyewa</a>
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
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                    <option value="checkout" <?= $status === 'checkout' ? 'selected' : '' ?>>Check-out</option>
                </select>
            </div>
            <div class="flex-1 relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, email, KTP..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
            <?php if ($propertyId || $status || $search): ?>
            <a href="<?= url('tenants') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($tenants['data'])): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">KTP</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properti</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontrak</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tagihan Bulan Ini</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($tenants['data'] as $tenant): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($tenant['tenant_name']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($tenant['tenant_phone']) ?></p>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($tenant['ktp_number'] ?? '-') ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($tenant['tenant_phone']) ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant['room_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($tenant['property_name']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= format_date($tenant['check_in_date'] ?? '-') ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500">
                            <?= format_date($tenant['contract_start_date'] ?? '-') ?> - Aktif
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($tenant['current_invoice_status'] === 'no_invoice'): ?>
                                <span class="text-gray-400 text-sm">Belum generate</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $tenant['current_invoice_status'] === 'paid' ? 'bg-green-100 text-green-800' : ($tenant['current_invoice_status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= format_currency($tenant['current_invoice_amount']) ?>
                                    <?php if ($tenant['current_invoice_status'] !== 'paid'): ?>
                                        <span class="ml-1">
                                            <?= $tenant['current_invoice_status'] === 'overdue' ? '⚠' : '⏳' ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tenant['status'] === 'active' ? 'bg-green-100 text-green-800' : ($tenant['status'] === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($tenant['status']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?= url('tenants/' . $tenant['id']) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Detail">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                                <a href="<?= url('tenants/' . $tenant['id'] . '/edit') ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <?php if ($tenant['status'] === 'active'): ?>
                                <form method="POST" action="<?= url('tenants/' . $tenant['id'] . '/checkout') ?>" class="inline" onsubmit="return confirm('Yakin check-out penyewa ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="check_out_date" value="<?= date('Y-m-d') ?>">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Check-out">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($tenants['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $tenants['from'] ?>-<?= $tenants['to'] ?> dari <?= $tenants['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($tenants['current_page'] > 1): ?>
                    <a href="?page=<?= $tenants['current_page'] - 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($tenants['has_more_pages']): ?>
                    <a href="?page=<?= $tenants['current_page'] + 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada penyewa</h3>
            <p class="mt-1 text-sm text-gray-500">Tambah penyewa baru untuk memulai.</p>
            <a href="<?= url('tenants/create') ?>" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Tambah Penyewa</a>
        </div>
        <?php endif; ?>
    </div>
</div>