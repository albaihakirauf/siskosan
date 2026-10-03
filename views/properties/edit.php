<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Properti</h1>
            <p class="text-gray-600">Perbarui informasi properti</p>
        </div>
        <a href="<?= url('properties/' . $property->id) ?>" class="text-sm text-primary-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="<?= url('properties/' . $property->id . '/update') ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="block text-sm font-medium text-gray-700">Foto Properti</label>
            <?php if ($property->photo): ?>
            <img src="<?= $property->getPhotoUrl() ?>" alt="" class="h-24 w-24 object-cover rounded-lg mt-2">
            <?php endif; ?>
            <input type="file" name="photo" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Properti <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($property->name) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Alamat Lengkap <span class="text-red-500">*</span></label>
            <textarea name="address" id="address" required rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($property->address) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700">Kota <span class="text-red-500">*</span></label>
                <input type="text" name="city" id="city" required value="<?= htmlspecialchars($property->city) ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label for="province" class="block text-sm font-medium text-gray-700">Provinsi</label>
                <input type="text" name="province" id="province" value="<?= htmlspecialchars($property->province ?? '') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label for="postal_code" class="block text-sm font-medium text-gray-700">Kode Pos</label>
                <input type="text" name="postal_code" id="postal_code" value="<?= htmlspecialchars($property->postal_code ?? '') ?>"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($property->description ?? '') ?></textarea>
        </div>

        <div>
            <label for="facilities" class="block text-sm font-medium text-gray-700">Fasilitas (pisahkan dengan koma)</label>
            <input type="text" name="facilities" id="facilities" value="<?= htmlspecialchars(implode(', ', $property->getFacilitiesArray())) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('properties/' . $property->id) ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
