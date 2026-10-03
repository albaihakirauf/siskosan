<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
            <p class="text-gray-600">Kelola informasi akun Anda</p>
        </div>
        <a href="<?= url('profile/edit') ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Edit Profil</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-6 border-b border-gray-200 flex items-center gap-6">
            <img src="<?= $user->getAvatarUrl() ?>" alt="" class="h-24 w-24 rounded-full">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user->name) ?></h2>
                <p class="text-gray-500"><?= htmlspecialchars($user->email) ?></p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 bg-primary-100 text-primary-800"><?= ucfirst($user->role) ?></span>
            </div>
        </div>
        
        <div class="p-6 space-y-4">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">Nama Lengkap</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($user->name) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($user->email) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Telepon</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($user->phone ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Peran</dt>
                    <dd class="font-medium text-gray-900"><?= ucfirst($user->role) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Bergabung Sejak</dt>
                    <dd class="font-medium text-gray-900"><?= format_date($user->created_at) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Terakhir Login</dt>
                    <dd class="font-medium text-gray-900"><?= $user->updated_at ? format_datetime($user->updated_at) : '-' ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <?php if ($user->role === 'tenant'): ?>
    <!-- Tenant Info -->
    <?php 
    $tenant = Tenant::where('user_id', $user->id)->where('status', 'active')->first();
    if ($tenant): 
        $tRoom = $tenant->room();
        $tProperty = $tenant->property();
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Penyewa</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Kamar</dt>
                <dd class="font-medium text-gray-900"><?= htmlspecialchars($tRoom->room_number ?? '-') ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Properti</dt>
                <dd class="font-medium text-gray-900"><?= htmlspecialchars($tProperty->name ?? '-') ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Check-in</dt>
                <dd class="font-medium text-gray-900"><?= format_date($tenant->check_in_date) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Status Kontrak</dt>
                <dd class="font-medium text-gray-900">Aktif</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Sewa Bulanan</dt>
                <dd class="font-medium text-gray-900"><?= format_currency($tenant->monthly_rent) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Total Tunggakan</dt>
                <dd class="font-medium text-red-600"><?= format_currency($tenant->getTotalArrears()) ?></dd>
            </div>
        </dl>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>