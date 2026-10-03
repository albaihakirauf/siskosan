<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Komplain</h1>
            <p class="text-gray-600">Ajukan komplain atau permintaan perbaikan</p>
        </div>
        <a href="<?= url('maintenance') ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('maintenance/store') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <input type="hidden" name="room_id" value="<?= $room->id ?? '' ?>">

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Judul <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" required value="<?= htmlspecialchars(old('title')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Contoh: AC tidak dingin, Lampu mati, Kebocoran air">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi <span class="text-red-500">*</span></label>
            <textarea name="description" id="description" required rows="4"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Jelaskan detail masalah yang dialami..."></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                <select name="category" id="category" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Pilih Kategori</option>
                    <option value="electricity" <?= old('category') === 'electricity' ? 'selected' : '' ?>>Listrik</option>
                    <option value="water" <?= old('category') === 'water' ? 'selected' : '' ?>>Air</option>
                    <option value="internet" <?= old('category') === 'internet' ? 'selected' : '' ?>>Internet</option>
                    <option value="furniture" <?= old('category') === 'furniture' ? 'selected' : '' ?>>Furniture</option>
                    <option value="cleanliness" <?= old('category') === 'cleanliness' ? 'selected' : '' ?>>Kebersihan</option>
                    <option value="security" <?= old('category') === 'security' ? 'selected' : '' ?>>Keamanan</option>
                    <option value="other" <?= old('category') === 'other' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                <select name="priority" id="priority" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Rendah</option>
                    <option value="medium" <?= old('priority') === 'medium' ? 'selected' : '' ?>>Sedang</option>
                    <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>Tinggi</option>
                    <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Mendesak</option>
                </select>
            </div>
        </div>

        <div>
            <label for="photo_before" class="block text-sm font-medium text-gray-700">Foto Masalah (opsional)</label>
            <div class="mt-1">
                <input type="file" name="photo_before" id="photo_before" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('maintenance') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Kirim Komplain</button>
        </div>
    </form>
</div>