<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Komplain</h1>
            <p class="text-gray-600">#<?= $complaint->id ?></p>
        </div>
        <a href="<?= url('maintenance/' . $complaint->id) ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('maintenance/' . $complaint->id . '/update') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Judul <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" required value="<?= htmlspecialchars($complaint->title) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi <span class="text-red-500">*</span></label>
            <textarea name="description" id="description" required rows="4"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($complaint->description) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                <select name="category" id="category" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $complaint->category === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                <select name="priority" id="priority" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="low" <?= $complaint->priority === 'low' ? 'selected' : '' ?>>Rendah</option>
                    <option value="medium" <?= $complaint->priority === 'medium' ? 'selected' : '' ?>>Sedang</option>
                    <option value="high" <?= $complaint->priority === 'high' ? 'selected' : '' ?>>Tinggi</option>
                    <option value="urgent" <?= $complaint->priority === 'urgent' ? 'selected' : '' ?>>Mendesak</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="open" <?= $complaint->status === 'open' ? 'selected' : '' ?>>Dibuka</option>
                    <option value="in_progress" <?= $complaint->status === 'in_progress' ? 'selected' : '' ?>>Diproses</option>
                    <option value="resolved" <?= $complaint->status === 'resolved' ? 'selected' : '' ?>>Selesai</option>
                    <option value="closed" <?= $complaint->status === 'closed' ? 'selected' : '' ?>>Ditutup</option>
                </select>
            </div>
            <div>
                <label for="estimated_cost" class="block text-sm font-medium text-gray-700">Estimasi Biaya</label>
                <input type="number" name="estimated_cost" id="estimated_cost" min="0" step="1000" value="<?= $complaint->estimated_cost ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label for="photo_before" class="block text-sm font-medium text-gray-700">Foto Masalah (opsional)</label>
            <?php if ($complaint->photo_before): ?>
            <img src="<?= $complaint->getPhotoBeforeUrl() ?>" alt="" class="h-24 w-24 object-cover rounded-lg mt-2">
            <?php endif; ?>
            <input type="file" name="photo_before" id="photo_before" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('maintenance/' . $complaint->id) ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
