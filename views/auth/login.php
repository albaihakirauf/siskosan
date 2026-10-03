<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Masuk ke Akun</h3>
        <p class="mt-1 text-sm text-gray-600">Atau <a href="<?= url('register') ?>" class="text-primary-600 hover:underline">daftar akun baru</a></p>
    </div>

    <div class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required autocomplete="email"
                   value="<?= htmlspecialchars(old('email')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="email@contoh.com">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="********">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <span class="text-sm text-gray-600">Ingat saya</span>
            </label>
            <a href="<?= url('forgot-password') ?>" class="text-sm text-primary-600 hover:underline">Lupa password?</a>
        </div>

        <button type="submit" class="w-full py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
            Masuk
        </button>
    </div>
</div>