<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kamar</h1>
            <p class="text-gray-600">Kelola kamar kos-kosan</p>
        </div>
        <a href="<?= url('rooms/create') ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Tambah Kamar</a>
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
                    <option value="empty" <?= $status === 'empty' ? 'selected' : '' ?>>Kosong</option>
                    <option value="occupied" <?= $status === 'occupied' ? 'selected' : '' ?>>Terisi</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>
            <div class="flex-1 relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nomor kamar..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
            <?php if ($propertyId || $status || $search): ?>
            <a href="<?= url('rooms') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($rooms['data'])): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lantai</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properti</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyewa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($rooms['data'] as $room): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <img src="<?= $room['photo'] ? asset('uploads/rooms/' . $room['photo']) : asset('assets/images/room-placeholder.svg') ?>" alt="" class="h-16 w-16 object-cover rounded-lg">
                        </td>
                        <td class="px-5 py-4 font-medium text-gray-900"><?= htmlspecialchars($room['room_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $room['type'] === 'single' ? 'Single' : ($room['type'] === 'double' ? 'Double' : 'Suite') ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900"><?= format_currency($room['price']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $room['capacity'] ?> orang</td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $room['floor'] ?? '-' ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($room['property_name'] ?? '-') ?></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $room['status'] === 'empty' ? 'bg-green-100 text-green-800' : ($room['status'] === 'occupied' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= $room['status'] === 'empty' ? 'Kosong' : ($room['status'] === 'occupied' ? 'Terisi' : 'Maintenance') ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500">
                            <?php if ($room['status'] === 'occupied'): ?>
                                <?php 
                                $db = db();
                                $tenant = $db->table('tenants t')
                                    ->join('users u', 't.user_id', '=', 'u.id')
                                    ->where('t.room_id', $room['id'])
                                    ->where('t.status', 'active')
                                    ->select('u.name')
                                    ->first();
                                echo htmlspecialchars($tenant['name'] ?? '-');
                                ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?= url('rooms/' . $room['id']) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Detail">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                                <a href="<?= url('rooms/' . $room['id'] . '/edit') ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <?php if ($room['status'] !== 'occupied'): ?>
                                <form method="POST" action="<?= url('rooms/' . $room['id'] . '/status') ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <select name="status" class="text-sm border border-gray-300 rounded-lg py-1 px-2" onchange="this.form.submit()">
                                        <option value="empty" <?= $room['status'] === 'empty' ? 'selected' : '' ?>>Kosong</option>
                                        <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                    </select>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= url('rooms/' . $room['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Yakin hapus kamar ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($rooms['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $rooms['from'] ?>-<?= $rooms['to'] ?> dari <?= $rooms['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($rooms['current_page'] > 1): ?>
                    <a href="?page=<?= $rooms['current_page'] - 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($rooms['has_more_pages']): ?>
                    <a href="?page=<?= $rooms['current_page'] + 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada kamar</h3>
            <p class="mt-1 text-sm text-gray-500">Tambah kamar baru untuk memulai.</p>
            <a href="<?= url('rooms/create') ?>" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Tambah Kamar</a>
        </div>
        <?php endif; ?>
    </div>
</div>