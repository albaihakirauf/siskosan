<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tagihan</h1>
            <p class="text-gray-600">Kelola tagihan sewa bulanan</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="<?= url('invoices/generate') ?>" onsubmit="return confirm('Generate tagihan bulan ini?')">
                <?= csrf_field() ?>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Generate Bulanan</button>
            </form>
            <a href="<?= url('invoices/create') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Tambah Manual</a>
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
                    <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Belum Bayar</option>
                    <option value="partial" <?= $status === 'partial' ? 'selected' : '' ?>>Cicilan</option>
                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Lunas</option>
                    <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Terlambat</option>
                </select>
            </div>
            <div class="flex-1">
                <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php foreach ($months as $m => $name): ?>
                    <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <input type="number" name="year" value="<?= $year ?>" min="2020" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Tahun">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
            <?php if ($propertyId || $status || $month != date('m') || $year != date('Y')): ?>
            <a href="<?= url('invoices') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Tagihan</p>
            <p class="text-2xl font-bold text-gray-900"><?= $summary['total'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Lunas</p>
            <p class="text-2xl font-bold text-green-600"><?= $summary['paid'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Belum Bayar</p>
            <p class="text-2xl font-bold text-red-600"><?= $summary['unpaid'] + $summary['overdue'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Cicilan</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $summary['partial'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Tagihan</p>
            <p class="text-2xl font-bold text-gray-900"><?= format_currency($summary['total_amount']) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Terkumpul</p>
            <p class="text-2xl font-bold text-green-600"><?= format_currency($summary['paid_amount']) ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($invoices['data'])): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyewa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properti</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dibayar</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sisa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($invoices['data'] as $i => $inv): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm text-gray-900"><?= $invoices['from'] + $i ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $inv['period_month'] . '/' . $inv['period_year'] ?></td>
                        <td class="px-5 py-4 text-sm text-gray-900"><?= htmlspecialchars($inv['tenant_name']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($inv['room_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($inv['property_name']) ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 text-right"><?= format_currency($inv['total_amount']) ?></td>
                        <td class="px-5 py-4 text-sm text-green-600 text-right"><?= format_currency($inv['paid_amount']) ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-red-600 text-right"><?= format_currency($inv['remaining_amount']) ?></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $inv->getStatusBadgeClass() ?>">
                                <?= $inv->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= format_date($inv['due_date']) ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?= url('invoices/' . $inv['id']) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Detail">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <?php if ($inv['status'] !== 'paid'): ?>
                                <a href="<?= url('payments/create?invoice_id=' . $inv['id']) ?>" class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg" title="Bayar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($invoices['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $invoices['from'] ?>-<?= $invoices['to'] ?> dari <?= $invoices['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($invoices['current_page'] > 1): ?>
                    <a href="?page=<?= $invoices['current_page'] - 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&month=<?= $month ?>&year=<?= $year ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($invoices['has_more_pages']): ?>
                    <a href="?page=<?= $invoices['current_page'] + 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&month=<?= $month ?>&year=<?= $year ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada tagihan</h3>
            <p class="mt-1 text-sm text-gray-500">Belum ada tagihan untuk filter ini.</p>
        </div>
        <?php endif; ?>
    </div>
</div>