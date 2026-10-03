<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Tagihan Manual</h1>
            <p class="text-gray-600">Buat tagihan baru untuk penyewa</p>
        </div>
        <a href="<?= url('invoices') ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('invoices/store') ?>" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700">Penyewa <span class="text-red-500">*</span></label>
                <select name="tenant_id" id="tenant_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Penyewa</option>
                    <?php foreach (($tenants ?? []) as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= old('tenant_id') == $t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['tenant_name'] . ' - Kamar ' . $t['room_number']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700">Jatuh Tempo <span class="text-red-500">*</span></label>
                <input type="date" name="due_date" id="due_date" required value="<?= old('due_date', date('Y-m-d')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="period_month" class="block text-sm font-medium text-gray-700">Bulan <span class="text-red-500">*</span></label>
                <select name="period_month" id="period_month" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= (old('period_month', date('m')) * 1) == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="period_year" class="block text-sm font-medium text-gray-700">Tahun <span class="text-red-500">*</span></label>
                <input type="number" name="period_year" id="period_year" required value="<?= old('period_year', date('Y')) ?>" min="2020" max="2030"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="rent_amount" class="block text-sm font-medium text-gray-700">Sewa Bulanan <span class="text-red-500">*</span></label>
                <input type="number" name="rent_amount" id="rent_amount" required min="0" step="1000" value="<?= old('rent_amount') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="electricity_amount" class="block text-sm font-medium text-gray-700">Listrik</label>
                <input type="number" name="electricity_amount" id="electricity_amount" min="0" step="1000" value="<?= old('electricity_amount', 0) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="water_amount" class="block text-sm font-medium text-gray-700">Air</label>
                <input type="number" name="water_amount" id="water_amount" min="0" step="1000" value="<?= old('water_amount', 0) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="wifi_amount" class="block text-sm font-medium text-gray-700">WiFi</label>
                <input type="number" name="wifi_amount" id="wifi_amount" min="0" step="1000" value="<?= old('wifi_amount', 0) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="other_amount" class="block text-sm font-medium text-gray-700">Lainnya</label>
                <input type="number" name="other_amount" id="other_amount" min="0" step="1000" value="<?= old('other_amount', 0) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label for="other_description" class="block text-sm font-medium text-gray-700">Deskripsi Lainnya</label>
                <input type="text" name="other_description" id="other_description" value="<?= old('other_description') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"><?= old('notes') ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('invoices') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Buat Tagihan</button>
        </div>
    </form>
</div>
