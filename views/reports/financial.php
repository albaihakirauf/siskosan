<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h1>
            <p class="text-gray-600">Ringkasan pendapatan dan piutang</p>
        </div>
        <a href="<?= url('reports/export/financial') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Export CSV</a>
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
                <input type="date" name="start_date" value="<?= $startDate ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="flex-1">
                <input type="date" name="end_date" value="<?= $endDate ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Pendapatan</p>
            <p class="text-2xl font-bold text-gray-900"><?= format_currency($totalRevenue) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Tunggakan</p>
            <p class="text-2xl font-bold text-red-600"><?= format_currency($totalOutstanding) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Rata-rata/Bulan</p>
            <p class="text-2xl font-bold text-gray-900"><?= format_currency(count($revenueByMonth) ? $totalRevenue / count($revenueByMonth) : 0) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Properti Aktif</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($properties) ?></p>
        </div>
    </div>

    <!-- Revenue by Month Chart (Table) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan per Bulan</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Bulan</th>
                        <th class="pb-3 text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($revenueByMonth as $rev): ?>
                    <tr>
                        <td class="py-3 text-sm text-gray-900"><?= date('F Y', strtotime($rev['month'] . '-01')) ?></td>
                        <td class="py-3 text-sm font-medium text-gray-900 text-right"><?= format_currency($rev['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($revenueByMonth)): ?>
                    <tr><td colspan="2" class="py-8 text-center text-gray-500">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue by Property -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan per Properti</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Properti</th>
                        <th class="pb-3 text-right">Pendapatan</th>
                        <th class="pb-3 text-right">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($revenueByProperty as $rev): ?>
                    <tr>
                        <td class="py-3 text-sm text-gray-900"><?= htmlspecialchars($rev['property_name']) ?></td>
                        <td class="py-3 text-sm font-medium text-gray-900 text-right"><?= format_currency($rev['total']) ?></td>
                        <td class="py-3 text-sm text-gray-500 text-right"><?= $totalRevenue > 0 ? round(($rev['total'] / $totalRevenue) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($revenueByProperty)): ?>
                    <tr><td colspan="3" class="py-8 text-center text-gray-500">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue by Payment Method -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan per Metode Pembayaran</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Metode</th>
                        <th class="pb-3 text-right">Jumlah Transaksi</th>
                        <th class="pb-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($revenueByMethod as $rev): ?>
                    <tr>
                        <td class="py-3 text-sm text-gray-900 capitalize"><?= $rev['payment_method'] ?></td>
                        <td class="py-3 text-sm text-gray-500 text-right"><?= $rev['count'] ?></td>
                        <td class="py-3 text-sm font-medium text-gray-900 text-right"><?= format_currency($rev['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($revenueByMethod)): ?>
                    <tr><td colspan="3" class="py-8 text-center text-gray-500">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Outstanding Invoices -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tagihan Belum Lunas (Outstanding)</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                        <th class="pb-3">Invoice</th>
                        <th class="pb-3">Penyewa</th>
                        <th class="pb-3">Kamar</th>
                        <th class="pb-3">Properti</th>
                        <th class="pb-3 text-right">Total</th>
                        <th class="pb-3 text-right">Sisa</th>
                        <th class="pb-3">Jatuh Tempo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($outstanding as $inv): ?>
                    <tr>
                        <td class="py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                        <td class="py-3 text-sm text-gray-900"><?= htmlspecialchars($inv['tenant_name']) ?></td>
                        <td class="py-3 text-sm text-gray-500"><?= htmlspecialchars($inv['room_number']) ?></td>
                        <td class="py-3 text-sm text-gray-500"><?= htmlspecialchars($inv['property_name']) ?></td>
                        <td class="py-3 text-sm text-gray-900 text-right"><?= format_currency($inv['total_amount']) ?></td>
                        <td class="py-3 text-sm font-bold text-red-600 text-right"><?= format_currency($inv['remaining']) ?></td>
                        <td class="py-3 text-sm text-gray-500"><?= format_date($inv['due_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($outstanding)): ?>
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">Semua tagihan lunas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>