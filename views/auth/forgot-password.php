<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Lupa Password</h3>
        <p class="mt-1 text-sm text-gray-600">Masukkan email Anda untuk mereset password</p>
    </div>

    <div class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   value="<?= htmlspecialchars(old('email')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="email@contoh.com">
        </div>

        <button type="submit" class="w-full py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
            Kirim Link Reset
        </button>
    </div>

    <div class="text-center">
        <a href="<?= url('login') ?>" class="text-sm text-primary-600 hover:underline">Kembali ke Login</a>
    </div>
</div>
