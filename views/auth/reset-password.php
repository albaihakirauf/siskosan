<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
        <p class="mt-1 text-sm text-gray-600">Masukkan password baru Anda</p>
    </div>

    <div class="space-y-4">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Minimal 6 karakter">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Ulangi password">
        </div>

        <button type="submit" class="w-full py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
            Reset Password
        </button>
    </div>

    <div class="text-center">
        <a href="<?= url('login') ?>" class="text-sm text-primary-600 hover:underline">Kembali ke Login</a>
    </div>
</div>
