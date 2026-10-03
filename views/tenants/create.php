<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Penyewa</h1>
            <p class="text-gray-600">Isi form di bawah untuk menambah penyewa baru</p>
        </div>
        <a href="<?= url('tenants') ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('tenants/store') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div>
            <label for="room_id" class="block text-sm font-medium text-gray-700">Kamar <span class="text-red-500">*</span></label>
            <select name="room_id" id="room_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Pilih Kamar</option>
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $selectedRoom == $r['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['property_name'] . ' - ' . $r['room_number'] . ' (' . $r['type'] . ' - ' . format_currency($r['price']) . ')') ?>
                </option>
                <?php endforeach; ?>
            </select>
            <p class="mt-1 text-sm text-gray-500">Hanya kamar kosong yang ditampilkan</p>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Data Pribadi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required value="<?= htmlspecialchars(old('name')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" required value="<?= htmlspecialchars(old('email')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" id="phone" required value="<?= htmlspecialchars(old('phone')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label for="ktp_number" class="block text-sm font-medium text-gray-700">No KTP <span class="text-red-500">*</span></label>
                    <input type="text" name="ktp_number" id="ktp_number" required value="<?= htmlspecialchars(old('ktp_number')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Nomor KTP 16 digit">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Kontak Darurat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" required value="<?= htmlspecialchars(old('emergency_contact_name')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700">Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" required value="<?= htmlspecialchars(old('emergency_contact_phone')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="emergency_contact_relation" class="block text-sm font-medium text-gray-700">Hubungan</label>
                    <input type="text" name="emergency_contact_relation" id="emergency_contact_relation" value="<?= htmlspecialchars(old('emergency_contact_relation')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Contoh: Ayah, Ibu, Suami">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dokumen</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="ktp_photo" class="block text-sm font-medium text-gray-700">Foto KTP</label>
                    <input type="file" name="ktp_photo" id="ktp_photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <div>
                    <label for="kk_photo" class="block text-sm font-medium text-gray-700">Foto KK</label>
                    <input type="file" name="kk_photo" id="kk_photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Kontrak & Pembayaran</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="check_in_date" class="block text-sm font-medium text-gray-700">Tanggal Check-in <span class="text-red-500">*</span></label>
                    <input type="date" name="check_in_date" id="check_in_date" required value="<?= htmlspecialchars(old('check_in_date', date('Y-m-d'))) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="contract_start_date" class="block text-sm font-medium text-gray-700">Mulai Kontrak <span class="text-red-500">*</span></label>
                    <input type="date" name="contract_start_date" id="contract_start_date" required value="<?= htmlspecialchars(old('contract_start_date', date('Y-m-d'))) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label for="monthly_rent" class="block text-sm font-medium text-gray-700">Sewa Bulanan <span class="text-red-500">*</span></label>
                    <input type="number" name="monthly_rent" id="monthly_rent" required min="0" step="1000" value="<?= htmlspecialchars(old('monthly_rent')) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Contoh: 1500000">
                </div>
                <div>
                    <label for="deposit_amount" class="block text-sm font-medium text-gray-700">Deposit</label>
                    <input type="number" name="deposit_amount" id="deposit_amount" min="0" step="1000" value="<?= htmlspecialchars(old('deposit_amount', 0)) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="deposit_paid" id="deposit_paid" <?= old('deposit_paid') ? 'checked' : '' ?> class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <label for="deposit_paid" class="ml-2 text-sm text-gray-700">Deposit sudah dibayar</label>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Akun Login</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required minlength="6"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Minimal 6 karakter">
                </div>
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Catatan tambahan..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('tenants') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Simpan Penyewa</button>
        </div>
    </form>
</div>