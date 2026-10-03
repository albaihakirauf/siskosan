<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Tagihan</h1>
            <p class="text-gray-600">Invoice #<?= htmlspecialchars($invoice->invoice_number) ?></p>
        </div>
        <div class="flex gap-2">
            <?php if ($invoice->status !== 'paid' && auth()->role !== 'tenant'): ?>
            <a href="<?= url('payments/create?invoice_id=' . $invoice->id) ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Catat Pembayaran</a>
            <?php endif; ?>
            <a href="<?= url('invoices') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Tagihan -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tagihan</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Nomor Invoice</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($invoice->invoice_number) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Periode</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= $invoice->getPeriodLabel() ?></dd>
                    
                    <dt class="text-sm text-gray-500">Jatuh Tempo</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_date($invoice->due_date) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $invoice->getStatusBadgeClass() ?>">
                            <?= $invoice->getStatusLabel() ?>
                        </span>
                        <?php if ($invoice->isOverdue()): ?>
                        <span class="ml-2 text-xs text-red-600">Terlambat <?= $invoice->getDaysOverdue() ?> hari</span>
                        <?php endif; ?>
                    </dd>
                    
                    <dt class="text-sm text-gray-500">Penyewa</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->user->name) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Kamar</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($room->room_number) ?> (<?= $room->getTypeLabel() ?>)</dd>
                    
                    <dt class="text-sm text-gray-500">Properti</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($property->name) ?></dd>
                </dl>
            </div>

            <!-- Rincian Biaya -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Rincian Biaya</h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="text-gray-600">Sewa Bulanan</span>
                        <span class="font-medium"><?= format_currency($invoice->rent_amount) ?></span>
                    </div>
                    <?php if ($invoice->electricity_amount > 0): ?>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="text-gray-600">Listrik</span>
                        <span class="font-medium"><?= format_currency($invoice->electricity_amount) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($invoice->water_amount > 0): ?>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="text-gray-600">Air</span>
                        <span class="font-medium"><?= format_currency($invoice->water_amount) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($invoice->wifi_amount > 0): ?>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="text-gray-600">WiFi</span>
                        <span class="font-medium"><?= format_currency($invoice->wifi_amount) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($invoice->other_amount > 0): ?>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="text-gray-600"><?= htmlspecialchars($invoice->other_description ?? 'Lainnya') ?></span>
                        <span class="font-medium"><?= format_currency($invoice->other_amount) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-lg font-bold pt-3 border-t-2 border-gray-200">
                        <span>Total</span>
                        <span><?= format_currency($invoice->total_amount) ?></span>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pembayaran -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pembayaran</h3>
                <?php if (!empty($payments)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-3">Tanggal</th>
                                <th class="pb-3">Jumlah</th>
                                <th class="pb-3">Metode</th>
                                <th class="pb-3">Referensi</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Diverifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="py-3 text-sm text-gray-900"><?= format_datetime($payment->payment_date) ?></td>
                                <td class="py-3 text-sm font-medium text-gray-900"><?= format_currency($payment->amount) ?></td>
                                <td class="py-3 text-sm text-gray-500"><?= $payment->getMethodLabel() ?></td>
                                <td class="py-3 text-sm text-gray-500"><?= htmlspecialchars($payment->reference_number ?? '-') ?></td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $payment->getStatusBadgeClass() ?>">
                                        <?= $payment->getStatusLabel() ?>
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500">
                                    <?php if ($payment->verified_at): ?>
                                        <?= format_datetime($payment->verified_at) ?>
                                        <?php if ($payment->verifier): ?>
                                            oleh <?= htmlspecialchars($payment->verifier->name) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada pembayaran</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Tagihan</p>
                        <p class="text-2xl font-bold text-gray-900"><?= format_currency($invoice->total_amount) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Sudah Dibayar</p>
                        <p class="text-2xl font-bold text-green-600"><?= format_currency($invoice->getPaidAmount()) ?></p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500">Sisa Tagihan</p>
                        <p class="text-2xl font-bold text-red-600"><?= format_currency($invoice->getRemainingAmount()) ?></p>
                    </div>
                </div>
            </div>

            <?php if ($invoice->status !== 'paid' && auth()->role !== 'tenant'): ?>
            <a href="<?= url('payments/create?invoice_id=' . $invoice->id) ?>" class="block w-full text-center py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">
                Catat Pembayaran
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>