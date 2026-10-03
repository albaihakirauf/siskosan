<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($property->name) ?></h1>
            <p class="text-gray-600"><?= htmlspecialchars($property->address) ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= url('rooms/create?property_id=' . $property->id) ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Tambah Kamar</a>
            <a href="<?= url('properties/' . $property->id . '/edit') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Edit</a>
            <a href="<?= url('properties') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Kamar</p>
            <p class="text-2xl font-bold text-gray-900"><?= $totalRooms ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Terisi</p>
            <p class="text-2xl font-bold text-green-600"><?= $occupiedRooms ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Kosong</p>
            <p class="text-2xl font-bold text-blue-600"><?= $emptyRooms ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Occupancy Rate</p>
            <p class="text-2xl font-bold text-gray-900"><?= $occupancyRate ?>%</p>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Kamar</h3>
        </div>
        <?php if (!empty($rooms)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyewa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($rooms as $room): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($room->room_number) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $room->getTypeLabel() ?></td>
                        <td class="px-5 py-4 text-sm text-gray-900"><?= format_currency($room->price) ?></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $room->getStatusBadgeClass() ?>">
                                <?= $room->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500">
                            <?php $tenant = $room->tenant(); $tu = $tenant ? $tenant->user() : null; ?>
                            <?= $tu ? htmlspecialchars($tu->name) : '-' ?>
                        </td>
                        <td class="px-5 py-4">
                            <a href="<?= url('rooms/' . $room->id) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg inline-block" title="Detail">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">Belum ada kamar</p>
            <a href="<?= url('rooms/create?property_id=' . $property->id) ?>" class="mt-2 inline-block text-primary-600 hover:underline">Tambah Kamar</a>
        </div>
        <?php endif; ?>
    </div>
</div>
