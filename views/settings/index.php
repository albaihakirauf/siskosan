<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600">Konfigurasi aplikasi KosManager</p>
        </div>
    </div>

    <form method="POST" action="<?= url('settings/update') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- General Settings -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Umum</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                    <input type="text" name="app_name" id="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'KosManager') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="currency_symbol" class="block text-sm font-medium text-gray-700">Simbol Mata Uang</label>
                    <input type="text" name="currency_symbol" id="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'Rp') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Billing Settings -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Penagihan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="invoice_due_day" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo (hari)</label>
                    <input type="number" name="invoice_due_day" id="invoice_due_day" min="1" max="31" value="<?= htmlspecialchars($settings['invoice_due_day'] ?? '5') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="late_fee_percentage" class="block text-sm font-medium text-gray-700">Denda Keterlambatan (%/bulan)</label>
                    <input type="number" name="late_fee_percentage" id="late_fee_percentage" min="0" max="100" step="0.1" value="<?= htmlspecialchars($settings['late_fee_percentage'] ?? '2') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="reminder_days" class="block text-sm font-medium text-gray-700">Reminder Sebelum Jatuh Tempo (pisahkan koma)</label>
                    <input type="text" name="reminder_days_before" id="reminder_days_before" value="<?= htmlspecialchars($settings['reminder_days_before'] ?? '3,1') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="3,1">
                    <p class="mt-1 text-sm text-gray-500">Contoh: 3,1 = H-3 dan H-1</p>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notifikasi</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Notifikasi Email</p>
                        <p class="text-sm text-gray-500">Kirim notifikasi via email (butuh konfigurasi SMTP)</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notification_email_enabled" value="true" <?= ($settings['notification_email_enabled'] ?? 'false') === 'true' ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Notifikasi WhatsApp (Fonnte)</p>
                        <p class="text-sm text-gray-500">Kirim notifikasi via WhatsApp API</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notification_whatsapp_enabled" value="true" <?= ($settings['notification_whatsapp_enabled'] ?? 'false') === 'true' ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="whatsapp_api_url" class="block text-sm font-medium text-gray-700">WhatsApp API URL</label>
                        <input type="url" name="whatsapp_api_url" id="whatsapp_api_url" value="<?= htmlspecialchars($settings['whatsapp_api_url'] ?? 'https://api.fonnte.com/send') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="whatsapp_api_token" class="block text-sm font-medium text-gray-700">WhatsApp API Token</label>
                        <input type="password" name="whatsapp_api_token" id="whatsapp_api_token" value="<?= htmlspecialchars($settings['whatsapp_api_token'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Token dari Fonnte">
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Info -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Database</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <dt class="text-gray-500">Host</dt>
                <dd class="font-medium"><?= $_ENV['DB_HOST'] ?? 'localhost' ?></dd>
                <dt class="text-gray-500">Database</dt>
                <dd class="font-medium"><?= $_ENV['DB_DATABASE'] ?? 'kosmanager' ?></dd>
                <dt class="text-gray-500">Username</dt>
                <dd class="font-medium"><?= $_ENV['DB_USERNAME'] ?? 'root' ?></dd>
            </dl>
            <p class="mt-4 text-sm text-gray-500">Edit file <code>.env</code> untuk mengubah konfigurasi database.</p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Simpan Pengaturan</button>
        </div>
    </form>
</div>