<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Properti</h1>
            <p class="text-gray-600">Kelola properti kos-kosan Anda</p>
        </div>
        <a href="<?= url('properties/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Properti
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, alamat, kota..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Cari</button>
            <?php if ($search): ?>
            <a href="<?= url('properties') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Properties Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($properties['data'])): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Occupancy</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($properties['data'] as $prop): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <img src="<?= $prop['photo'] ? url('uploads/properties/' . $prop['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($prop['name']) . '&size=128' ?>" alt="" class="h-16 w-16 object-cover rounded-lg">
                        </td>
                        <td class="px-5 py-4">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($prop['name']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars(trim(($prop['city'] ?? '') . ', ' . ($prop['province'] ?? ''))) ?></p>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($prop['address']) ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium"><?= $prop['rooms_count'] ?? 0 ?> kamar</span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800"><?= $prop['occupied_count'] ?? 0 ?> terisi</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="w-32">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-600 rounded-full" style="width: <?= $prop['occupancy_rate'] ?? 0 ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500"><?= $prop['occupancy_rate'] ?? 0 ?>%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= url('properties/' . $prop['id']) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Detail">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="<?= url('properties/' . $prop['id'] . '/edit') ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="<?= url('properties/' . $prop['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Yakin ingin menghapus properti ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
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
        <?php if ($properties['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $properties['from'] ?>-<?= $properties['to'] ?> dari <?= $properties['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($properties['current_page'] > 1): ?>
                    <a href="?page=<?= $properties['current_page'] - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($properties['has_more_pages']): ?>
                    <a href="?page=<?= $properties['current_page'] + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada properti</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambah properti pertama Anda.</p>
            <a href="<?= url('properties/create') ?>" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Properti
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>