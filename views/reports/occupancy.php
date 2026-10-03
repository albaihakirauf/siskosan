<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Occupancy</h1>
            <p class="text-gray-600">Tingkat hunian kamar per properti dan tipe</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <select name="property_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Properti</option>
                    <?php foreach ($properties as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $propertyId == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Kamar</p>
            <p class="text-3xl font-bold text-gray-900"><?= $totalRooms ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Terisi</p>
            <p class="text-3xl font-bold text-blue-600"><?= $totalOccupied ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Kosong</p>
            <p class="text-3xl font-bold text-green-600"><?= $totalRooms - $totalOccupied ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Occupancy Rate</p>
            <p class="text-3xl font-bold text-gray-900"><?= $overallRate ?>%</p>
        </div>
    </div>

    <!-- Occupancy by Property -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Occupancy per Properti</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Properti</th>
                        <th class="pb-3 text-center">Total</th>
                        <th class="pb-3 text-center">Terisi</th>
                        <th class="pb-3 text-center">Kosong</th>
                        <th class="pb-3 text-center">Maintenance</th>
                        <th class="pb-3 text-center">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($occupancy as $occ): ?>
                    <tr>
                        <td class="py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($occ['property_name']) ?></td>
                        <td class="py-3 text-sm text-gray-500 text-center"><?= $occ['total_rooms'] ?></td>
                        <td class="py-3 text-sm text-blue-600 text-center font-medium"><?= $occ['occupied'] ?></td>
                        <td class="py-3 text-sm text-green-600 text-center font-medium"><?= $occ['empty'] ?></td>
                        <td class="py-3 text-sm text-yellow-600 text-center font-medium"><?= $occ['maintenance'] ?></td>
                        <td class="py-3 text-sm font-bold text-gray-900 text-center">
                            <div class="w-24 mx-auto">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-600 rounded-full" style="width: <?= $occ['total_rooms'] > 0 ? round(($occ['occupied'] / $occ['total_rooms']) * 100) : 0 ?>%"></div>
                                </div>
                                <span class="text-xs"><?= $occ['total_rooms'] > 0 ? round(($occ['occupied'] / $occ['total_rooms']) * 100, 1) : 0 ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Occupancy by Room Type -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Occupancy per Tipe Kamar</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Tipe</th>
                        <th class="pb-3 text-center">Total</th>
                        <th class="pb-3 text-center">Terisi</th>
                        <th class="pb-3 text-center">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($occupancyByType as $occ): ?>
                    <tr>
                        <td class="py-3 text-sm font-medium text-gray-900 capitalize"><?= $occ['type'] ?></td>
                        <td class="py-3 text-sm text-gray-500 text-center"><?= $occ['total'] ?></td>
                        <td class="py-3 text-sm text-blue-600 text-center font-medium"><?= $occ['occupied'] ?></td>
                        <td class="py-3 text-sm font-bold text-gray-900 text-center"><?= $occ['total'] > 0 ? round(($occ['occupied'] / $occ['total']) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Occupancy Trend (Last 12 months) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Occupancy (12 Bulan Terakhir)</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Bulan</th>
                        <th class="pb-3 text-center">Penyewa Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($occupancyTrend as $trend): ?>
                    <tr>
                        <td class="py-3 text-sm text-gray-900"><?= $trend['year'] . '-' . $trend['month'] ?></td>
                        <td class="py-3 text-sm text-blue-600 text-center font-medium"><?= $trend['active_tenants'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($occupancyTrend)): ?>
                    <tr><td colspan="2" class="py-8 text-center text-gray-500">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>