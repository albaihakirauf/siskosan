<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Catat Pembayaran</h1>
            <p class="text-gray-600">Form pencatatan pembayaran tagihan</p>
        </div>
        <a href="<?= url('payments') ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <?php if ($invoice): ?>
    <!-- Invoice Info -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tagihan</h3>
        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-sm text-gray-500">Invoice</dt>
            <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($invoice->invoice_number) ?></dd>
            <dt class="text-sm text-gray-500">Periode</dt>
            <dd class="text-sm font-medium text-gray-900"><?= $invoice->getPeriodLabel() ?></dd>
            <dt class="text-sm text-gray-500">Total Tagihan</dt>
            <dd class="text-sm font-bold text-gray-900"><?= format_currency($invoice->total_amount) ?></dd>
            <dt class="text-sm text-gray-500">Sudah Dibayar</dt>
            <dd class="text-sm font-medium text-green-600"><?= format_currency($invoice->getPaidAmount()) ?></dd>
            <dt class="text-sm text-gray-500">Sisa Tagihan</dt>
            <dd class="text-sm font-bold text-red-600"><?= format_currency($invoice->getRemainingAmount()) ?></dd>
        </dl>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('payments/store') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <input type="hidden" name="invoice_id" value="<?= $invoice->id ?? '' ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah Pembayaran <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount" required min="1" step="1000"
                       value="<?= old('amount', $invoice ? $invoice->getRemainingAmount() : '') ?>"
                       max="<?= $invoice ? $invoice->getRemainingAmount() : '' ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="payment_method" id="payment_method" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Metode</option>
                    <option value="cash" <?= old('payment_method') === 'cash' ? 'selected' : '' ?>>Tunai</option>
                    <option value="transfer" <?= old('payment_method') === 'transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                    <option value="ewallet" <?= old('payment_method') === 'ewallet' ? 'selected' : '' ?>>E-Wallet</option>
                    <option value="other" <?= old('payment_method') === 'other' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" id="payment_date" required value="<?= old('payment_date', date('Y-m-d')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="reference_number" class="block text-sm font-medium text-gray-700">Nomor Referensi</label>
                <input type="text" name="reference_number" id="reference_number" value="<?= old('reference_number') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="Nomor transfer/bukti">
            </div>
        </div>

        <div>
            <label for="proof_photo" class="block text-sm font-medium text-gray-700">Bukti Pembayaran (opsional)</label>
            <input type="file" name="proof_photo" id="proof_photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                      placeholder="Catatan tambahan..."><?= old('notes') ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('payments') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Catat Pembayaran</button>
        </div>
    </form>
</div>
