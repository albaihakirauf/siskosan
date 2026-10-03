<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pembayaran</h1>
            <p class="text-gray-600">Invoice #<?= htmlspecialchars($invoice->invoice_number) ?></p>
        </div>
        <a href="<?= url('payments') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Tanggal Bayar</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_date($payment->payment_date) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Jumlah</dt>
                    <dd class="text-sm font-bold text-gray-900 text-lg"><?= format_currency($payment->amount) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Metode</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= $payment->getMethodLabel() ?></dd>
                    
                    <dt class="text-sm text-gray-500">No Referensi</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment->reference_number ?? '-') ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $payment->getStatusBadgeClass() ?>">
                            <?= $payment->getStatusLabel() ?>
                        </span>
                    </dd>
                    
                    <dt class="text-sm text-gray-500">Catatan</dt>
                    <dd class="text-sm text-gray-500"><?= htmlspecialchars($payment->notes ?? '-') ?></dd>
                </dl>
            </div>

            <?php if ($payment->proof_photo): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bukti Pembayaran</h3>
                <div class="text-center">
                    <img src="<?= $payment->getProofUrl() ?>" alt="Bukti pembayaran" class="max-w-full h-auto rounded-lg border border-gray-200 cursor-pointer" onclick="window.open(this.src, '_blank')">
                    <p class="text-sm text-gray-500 mt-2">Klik untuk perbesar</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Info Tagihan</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Nomor Invoice</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($invoice->invoice_number) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Periode</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= $invoice->getPeriodLabel() ?></dd>
                    
                    <dt class="text-sm text-gray-500">Total Tagihan</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_currency($invoice->total_amount) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Sudah Dibayar</dt>
                    <dd class="text-sm font-medium text-green-600"><?= format_currency($invoice->getPaidAmount()) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Sisa</dt>
                    <dd class="text-sm font-bold text-red-600"><?= format_currency($invoice->getRemainingAmount()) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status Tagihan</dt>
                    <dd class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $invoice->getStatusBadgeClass() ?>">
                            <?= $invoice->getStatusLabel() ?>
                        </span>
                    </dd>
                </dl>
            </div>

            <?php if ($verifier): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Verifikasi</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Diverifikasi Oleh</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($verifier->name) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Waktu Verifikasi</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_datetime($payment->verified_at) ?></dd>
                </dl>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Penyewa</h3>
                <div class="flex items-center gap-4">
                    <img src="<?= $tenant->user->getAvatarUrl() ?>" alt="" class="h-16 w-16 rounded-full">
                    <div>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($tenant->user->name) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($tenant->user->email) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($tenant->phone ?? '-') ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kamar & Properti</h3>
                <dl class="space-y-2">
                    <dt class="text-sm text-gray-500">Kamar</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($room->room_number) ?> (<?= $room->getTypeLabel() ?>)</dd>
                    
                    <dt class="text-sm text-gray-500">Properti</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($property->name) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Alamat</dt>
                    <dd class="text-sm text-gray-500"><?= htmlspecialchars($property->address) ?></dd>
                </dl>
            </div>

            <?php if ($payment->status === 'pending' && auth()->role !== 'tenant'): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Verifikasi</h3>
                <div class="space-y-3">
                    <form method="POST" action="<?= url('payments/' . $payment->id . '/verify') ?>" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                            Verifikasi Pembayaran
                        </button>
                    </form>
                    <form method="POST" action="<?= url('payments/' . $payment->id . '/reject') ?>" class="inline" onsubmit="return confirm('Yakin tolak pembayaran ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="reason" value="Bukti tidak valid / tidak sesuai">
                        <button type="submit" class="w-full py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50">
                            Tolak Pembayaran
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>