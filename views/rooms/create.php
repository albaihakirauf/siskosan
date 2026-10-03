<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Kamar</h1>
            <p class="text-gray-600">Isi form di bawah untuk menambah kamar baru</p>
        </div>
        <a href="<?= url('rooms') ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('rooms/store') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="property_id" class="block text-sm font-medium text-gray-700">Properti <span class="text-red-500">*</span></label>
                <select name="property_id" id="property_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Pilih Properti</option>
                    <?php foreach ($properties as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $selectedProperty == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="room_number" class="block text-sm font-medium text-gray-700">Nomor Kamar <span class="text-red-500">*</span></label>
                <input type="text" name="room_number" id="room_number" required value="<?= htmlspecialchars(old('room_number')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Contoh: A-101">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Tipe Kamar <span class="text-red-500">*</span></label>
                <select name="type" id="type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="single" <?= old('type') === 'single' ? 'selected' : '' ?>>Single</option>
                    <option value="double" <?= old('type') === 'double' ? 'selected' : '' ?>>Double</option>
                    <option value="suite" <?= old('type') === 'suite' ? 'selected' : '' ?>>Suite</option>
                </select>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Harga Bulanan <span class="text-red-500">*</span></label>
                <input type="number" name="price" id="price" required min="0" step="1000" value="<?= htmlspecialchars(old('price')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Contoh: 1500000">
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700">Kapasitas</label>
                <input type="number" name="capacity" id="capacity" min="1" max="10" value="<?= htmlspecialchars(old('capacity', 1)) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="1">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="floor" class="block text-sm font-medium text-gray-700">Lantai</label>
                <input type="number" name="floor" id="floor" min="0" max="100" value="<?= htmlspecialchars(old('floor')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="1">
            </div>

            <div>
                <label for="size_sqm" class="block text-sm font-medium text-gray-700">Luas (m²)</label>
                <input type="number" name="size_sqm" id="size_sqm" min="0" step="0.5" value="<?= htmlspecialchars(old('size_sqm')) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="12.5">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="empty" <?= old('status') === 'empty' ? 'selected' : '' ?>>Kosong</option>
                    <option value="maintenance" <?= old('status') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Deskripsi kamar, fasilitas khusus, dll."><?= htmlspecialchars(old('description')) ?></textarea>
        </div>

        <div>
            <label for="facilities" class="block text-sm font-medium text-gray-700">Fasilitas (pisahkan dengan koma)</label>
            <input type="text" name="facilities" id="facilities" value="<?= htmlspecialchars(old('facilities')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="WiFi, AC, Kamar Mandi Dalam, Kasur, Lemari, Parkir Motor">
            <p class="mt-1 text-sm text-gray-500">Contoh: WiFi, AC, Kamar Mandi Dalam, Kasur, Lemari, Parkir Motor</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Foto Kamar</label>
            <div class="mt-1">
                <input type="file" name="photo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('rooms') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Simpan Kamar</button>
        </div>
    </form>
</div>