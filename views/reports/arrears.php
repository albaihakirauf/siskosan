<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Tunggakan</h1>
            <p class="text-gray-600">Aging report dan penyewa bermasalah</p>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Tunggakan</p>
            <p class="text-3xl font-bold text-red-600"><?= format_currency($totalArrears) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Penyewa Bermasalah</p>
            <p class="text-3xl font-bold text-red-600"><?= $totalTenantsWithArrears ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Rata-rata/Penyewa</p>
            <p class="text-3xl font-bold text-red-600"><?= $totalTenantsWithArrears > 0 ? format_currency($totalArrears / $totalTenantsWithArrears) : format_currency(0) ?></p>
        </div>
    </div>

    <!-- Aging Report -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aging Report Tunggakan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <?php foreach ($aging as $key => $age): ?>
            <div class="p-4 rounded-xl border <?= $key === 'current' ? 'border-green-200 bg-green-50' : ($key === '1-30' ? 'border-yellow-200 bg-yellow-50' : ($key === '31-60' ? 'border-orange-200 bg-orange-50' : ($key === '61-90' ? 'border-red-200 bg-red-50' : 'border-red-300 bg-red-50'))) ?>">
                <p class="text-sm font-medium <?= $key === 'current' ? 'text-green-800' : ($key === '1-30' ? 'text-yellow-800' : ($key === '31-60' ? 'text-orange-800' : 'text-red-800')) ?>">
                    <?= $age['label'] ?>
                </p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= format_currency($age['amount']) ?></p>
                <p class="text-sm text-gray-500"><?= $age['count'] ?> tagihan</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Arrears by Tenant -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Tunggakan per Penyewa</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Penyewa</th>
                        <th class="pb-3">Telepon</th>
                        <th class="pb-3">Kamar</th>
                        <th class="pb-3">Properti</th>
                        <th class="pb-3 text-right">Jumlah Tagihan</th>
                        <th class="pb-3 text-right">Total Tunggakan</th>
                        <th class="pb-3">Tunggakan Terlama</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($arrears as $arr): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($arr['tenant_name']) ?></p>
                        </td>
                        <td class="py-3 text-sm text-gray-500"><?= htmlspecialchars($arr['tenant_phone']) ?></td>
                        <td class="py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($arr['room_number']) ?></td>
                        <td class="py-3 text-sm text-gray-500"><?= htmlspecialchars($arr['property_name']) ?></td>
                        <td class="py-3 text-sm text-gray-500 text-right"><?= $arr['unpaid_count'] ?> tagihan</td>
                        <td class="py-3 text-sm font-bold text-red-600 text-right"><?= format_currency($arr['total_arrears']) ?></td>
                        <td class="py-3 text-sm text-gray-500"><?= $arr['oldest_due_date'] ? format_date($arr['oldest_due_date']) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($arrears)): ?>
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">Tidak ada tunggakan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>