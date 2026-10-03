<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Tagihan</h1>
            <p class="text-gray-600">Invoice #<?= htmlspecialchars($invoice->invoice_number) ?></p>
        </div>
        <a href="<?= url('invoices/' . $invoice->id) ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('invoices/' . $invoice->id . '/update') ?>" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="rent_amount" class="block text-sm font-medium text-gray-700">Sewa Bulanan <span class="text-red-500">*</span></label>
                <input type="number" name="rent_amount" id="rent_amount" required min="0" step="1000" value="<?= $invoice->rent_amount ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="electricity_amount" class="block text-sm font-medium text-gray-700">Listrik</label>
                <input type="number" name="electricity_amount" id="electricity_amount" min="0" step="1000" value="<?= $invoice->electricity_amount ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="water_amount" class="block text-sm font-medium text-gray-700">Air</label>
                <input type="number" name="water_amount" id="water_amount" min="0" step="1000" value="<?= $invoice->water_amount ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="wifi_amount" class="block text-sm font-medium text-gray-700">WiFi</label>
                <input type="number" name="wifi_amount" id="wifi_amount" min="0" step="1000" value="<?= $invoice->wifi_amount ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="other_amount" class="block text-sm font-medium text-gray-700">Lainnya</label>
                <input type="number" name="other_amount" id="other_amount" min="0" step="1000" value="<?= $invoice->other_amount ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="other_description" class="block text-sm font-medium text-gray-700">Deskripsi Lainnya</label>
                <input type="text" name="other_description" id="other_description" value="<?= htmlspecialchars($invoice->other_description ?? '') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700">Jatuh Tempo <span class="text-red-500">*</span></label>
            <input type="date" name="due_date" id="due_date" required value="<?= $invoice->due_date ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($invoice->notes ?? '') ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('invoices/' . $invoice->id) ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
