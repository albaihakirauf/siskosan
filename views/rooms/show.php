<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kamar <?= htmlspecialchars($room->room_number) ?></h1>
            <p class="text-gray-600"><?= htmlspecialchars($property->name) ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= url('rooms/' . $room->id . '/edit') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Edit</a>
            <a href="<?= url('rooms') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Foto & Info -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex gap-6">
                    <img src="<?= $room->getPhotoUrl() ?>" alt="" class="h-48 w-48 object-cover rounded-xl flex-shrink-0">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900">Kamar <?= htmlspecialchars($room->room_number) ?></h3>
                        <p class="text-gray-500"><?= htmlspecialchars($property->name) ?></p>
                        <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-500">
                            <span><strong>Tipe:</strong> <?= $room->getTypeLabel() ?></span>
                            <span><strong>Harga:</strong> <?= format_currency($room->price) ?>/bulan</span>
                            <span><strong>Kapasitas:</strong> <?= $room->capacity ?> orang</span>
                            <span><strong>Lantai:</strong> <?= $room->floor ?? '-' ?></span>
                            <span><strong>Luas:</strong> <?= $room->size_sqm ?? '-' ?> m²</span>
                        </div>
                        <div class="mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $room->getStatusBadgeClass() ?>">
                                <?= $room->getStatusLabel() ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi & Fasilitas -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Deskripsi & Fasilitas</h3>
                <?php if ($room->description): ?>
                <p class="text-gray-600 mb-4"><?= htmlspecialchars($room->description) ?></p>
                <?php endif; ?>
                <?php if ($room->getFacilitiesArray()): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($room->getFacilitiesArray() as $facility): ?>
                    <span class="px-3 py-1 bg-primary-50 text-primary-700 text-sm rounded-full"><?= htmlspecialchars($facility) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500">Tidak ada fasilitas tambahan</p>
                <?php endif; ?>
            </div>

            <!-- Penyewa Saat Ini -->
            <?php if ($tenant): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Penyewa Saat Ini</h3>
                <div class="flex items-center gap-4">
                    <img src="<?= $tenant->user->getAvatarUrl() ?>" alt="" class="h-16 w-16 rounded-full">
                    <div>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($tenant->user->name) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($tenant->user->email) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($tenant->phone ?? '-') ?></p>
                        <div class="mt-2 text-sm">
                            <span class="text-gray-500">Check-in: </span>
                            <span class="font-medium"><?= format_date($tenant->check_in_date) ?></span>
                            <span class="ml-4 text-gray-500">Kontrak: </span>
                            <span class="font-medium"><?= format_date($tenant->contract_start_date) ?> - Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Kamar Kosong</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada penyewa</p>
                    <a href="<?= url('tenants/create?room_id=' . $room->id) ?>" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Tambah Penyewa</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tagihan Bulan Ini -->
            <?php if ($currentInvoice): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tagihan Bulan Ini</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Periode</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= $currentInvoice->getPeriodLabel() ?></dd>
                    
                    <dt class="text-sm text-gray-500">Total</dt>
                    <dd class="text-sm font-bold text-gray-900"><?= format_currency($currentInvoice->total_amount) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Dibayar</dt>
                    <dd class="text-sm font-medium text-green-600"><?= format_currency($currentInvoice->getPaidAmount()) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Sisa</dt>
                    <dd class="text-sm font-bold text-red-600"><?= format_currency($currentInvoice->getRemainingAmount()) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $currentInvoice->getStatusBadgeClass() ?>">
                            <?= $currentInvoice->getStatusLabel() ?>
                        </span>
                    </dd>
                    
                    <dt class="text-sm text-gray-500">Jatuh Tempo</dt>
                    <dd class="text-sm text-gray-900"><?= format_date($currentInvoice->due_date) ?></dd>
                </dl>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <?php if (!$tenant): ?>
                    <a href="<?= url('tenants/create?room_id=' . $room->id) ?>" class="block w-full py-2 bg-primary-600 text-white text-center font-medium rounded-lg hover:bg-primary-700">Tambah Penyewa</a>
                    <?php else: ?>
                    <a href="<?= url('tenants/' . $tenant->id) ?>" class="block w-full py-2 border border-gray-300 text-center text-gray-700 font-medium rounded-lg hover:bg-gray-50">Lihat Penyewa</a>
                    <?php endif; ?>
                    
                    <?php if ($currentInvoice && $currentInvoice->status !== 'paid'): ?>
                    <a href="<?= url('payments/create?invoice_id=' . $currentInvoice->id) ?>" class="block w-full py-2 bg-green-600 text-white text-center font-medium rounded-lg hover:bg-green-700">Catat Pembayaran</a>
                    <?php endif; ?>
                    
                    <a href="<?= url('maintenance/create?room_id=' . $room->id) ?>" class="block w-full py-2 border border-gray-300 text-center text-gray-700 font-medium rounded-lg hover:bg-gray-50">Buat Komplain</a>
                </div>
            </div>

            <!-- Ubah Status -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ubah Status</h3>
                <form method="POST" action="<?= url('rooms/' . $room->id . '/status') ?>" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="empty" <?= $room->status === 'empty' ? 'selected' : '' ?>>Kosong</option>
                        <option value="occupied" <?= $room->status === 'occupied' ? 'selected' : '' ?>>Terisi</option>
                        <option value="maintenance" <?= $room->status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                    <button type="submit" class="w-full py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>