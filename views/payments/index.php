<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pembayaran</h1>
            <p class="text-gray-600">Kelola pencatatan dan verifikasi pembayaran</p>
        </div>
        <div class="flex gap-2">
            <?php if ($summary['pending'] > 0): ?>
            <form method="POST" action="<?= url('payments/verify-all') ?>" onsubmit="return confirm('Verifikasi semua pembayaran pending? (Demo ACC)')">
                <?= csrf_field() ?>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Demo ACC Semua (<?= $summary['pending'] ?>)</button>
            </form>
            <?php endif; ?>
            <a href="<?= url('payments/create') ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Catat Pembayaran</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-900"><?= $summary['total'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Terverifikasi</p>
            <p class="text-2xl font-bold text-green-600"><?= $summary['verified'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Menunggu</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $summary['pending'] ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Terverifikasi</p>
            <p class="text-2xl font-bold text-gray-900"><?= format_currency($summary['verified_amount']) ?></p>
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
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="flex-1">
                <input type="date" name="start_date" value="<?= $startDate ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="flex-1">
                <input type="date" name="end_date" value="<?= $endDate ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Filter</button>
            <?php if ($propertyId || $status || $startDate != date('Y-m-01') || $endDate != date('Y-m-t')): ?>
            <a href="<?= url('payments') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($payments['data'])): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyewa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properti</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($payments['data'] as $payment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm text-gray-900"><?= format_date($payment['payment_date']) ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['invoice_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($payment['room_number']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($payment['property_name']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($payment['invoice_period']) ?></td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 text-right"><?= format_currency($payment['amount']) ?></td>
                        <td class="px-5 py-4 text-sm text-gray-500"><?= $payment->getMethodLabel() ?></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $payment->getStatusBadgeClass() ?>">
                                <?= $payment->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?= url('payments/' . $payment['id']) ?>" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Detail">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                                <?php if ($payment['status'] === 'pending' && auth()->role !== 'tenant'): ?>
                                <form method="POST" action="<?= url('payments/' . $payment['id'] . '/verify') ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Verifikasi">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="<?= url('payments/' . $payment['id'] . '/reject') ?>" class="inline" onsubmit="return confirm('Yakin tolak pembayaran ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="reason" value="Bukti tidak valid">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Tolak">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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
        <?php if ($payments['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $payments['from'] ?>-<?= $payments['to'] ?> dari <?= $payments['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($payments['current_page'] > 1): ?>
                    <a href="?page=<?= $payments['current_page'] - 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($payments['has_more_pages']): ?>
                    <a href="?page=<?= $payments['current_page'] + 1 ?>&property_id=<?= $propertyId ?>&status=<?= $status ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pembayaran</h3>
            <p class="mt-1 text-sm text-gray-500">Catat pembayaran pertama untuk memulai.</p>
        </div>
        <?php endif; ?>
    </div>
</div>