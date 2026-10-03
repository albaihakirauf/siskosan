<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Penyewa</h1>
            <p class="text-gray-600"><?= htmlspecialchars($tenant->user->name) ?></p>
        </div>
        <div class="flex gap-2">
            <?php if ($tenant->status === 'active'): ?>
            <form method="POST" action="<?= url('tenants/' . $tenant->id . '/checkout') ?>" class="inline" onsubmit="return confirm('Yakin check-out penyewa ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="check_out_date" value="<?= date('Y-m-d') ?>">
                <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50">Check-out</button>
            </form>
            <?php endif; ?>
            <a href="<?= url('tenants/' . $tenant->id . '/edit') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Edit</a>
            <a href="<?= url('tenants') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Penyewa</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Nama</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->user->name) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->user->email) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Telepon</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->phone ?? '-') ?></dd>
                    
                    <dt class="text-sm text-gray-500">No KTP</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->ktp_number ?? '-') ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tenant->getStatusBadgeClass() ?>">
                            <?= $tenant->getStatusLabel() ?>
                        </span>
                    </dd>
                    
                    <dt class="text-sm text-gray-500">Check-in</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_date($tenant->check_in_date) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Kontrak</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_date($tenant->contract_start_date) ?> - Aktif</dd>
                    
                    <dt class="text-sm text-gray-500">Sewa Bulanan</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_currency($tenant->monthly_rent) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Deposit</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= format_currency($tenant->deposit_amount) ?> <?= $tenant->deposit_paid ? '(Lunas)' : '(Belum)' ?></dd>
                </dl>
            </div>

            <!-- Kontak Darurat -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak Darurat</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-sm text-gray-500">Nama</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->emergency_contact_name ?? '-') ?></dd>
                    
                    <dt class="text-sm text-gray-500">Telepon</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->emergency_contact_phone ?? '-') ?></dd>
                    
                    <dt class="text-sm text-gray-500">Hubungan</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tenant->emergency_contact_relation ?? '-') ?></dd>
                </dl>
            </div>

            <!-- Dokumen -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Dokumen</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Foto KTP</p>
                        <?php if ($tenant->getKtpPhotoUrl()): ?>
                        <a href="<?= $tenant->getKtpPhotoUrl() ?>" target="_blank" class="block mt-1 text-primary-600 hover:underline text-sm">Lihat</a>
                        <?php else: ?>
                        <p class="text-sm text-gray-400 mt-1">Belum upload</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Foto KK</p>
                        <?php if ($tenant->getKkPhotoUrl()): ?>
                        <a href="<?= $tenant->getKkPhotoUrl() ?>" target="_blank" class="block mt-1 text-primary-600 hover:underline text-sm">Lihat</a>
                        <?php else: ?>
                        <p class="text-sm text-gray-400 mt-1">Belum upload</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tagihan -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Tagihan</h3>
                <?php if (!empty($invoices)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-3">Periode</th>
                                <th class="pb-3">Total</th>
                                <th class="pb-3">Dibayar</th>
                                <th class="pb-3">Sisa</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="py-3 text-sm text-gray-900"><?= $inv->getPeriodLabel() ?></td>
                                <td class="py-3 text-sm text-gray-900"><?= format_currency($inv->total_amount) ?></td>
                                <td class="py-3 text-sm text-green-600"><?= format_currency($inv->getPaidAmount()) ?></td>
                                <td class="py-3 text-sm font-medium text-red-600"><?= format_currency($inv->getRemainingAmount()) ?></td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $inv->getStatusBadgeClass() ?>">
                                        <?= $inv->getStatusLabel() ?>
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500"><?= format_date($inv->due_date) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada tagihan</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Tunggakan</p>
                        <p class="text-2xl font-bold text-red-600"><?= format_currency($tenant->getTotalArrears()) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tagihan Belum Lunas</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= count($unpaidInvoices) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kamar</h3>
                <dl class="space-y-2">
                    <dt class="text-sm text-gray-500">Nomor</dt>
                    <dd class="font-medium text-gray-900"><?= htmlspecialchars($room->room_number) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Tipe</dt>
                    <dd class="font-medium text-gray-900"><?= $room->getTypeLabel() ?></dd>
                    
                    <dt class="text-sm text-gray-500">Harga</dt>
                    <dd class="font-medium text-gray-900"><?= format_currency($room->price) ?></dd>
                    
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="font-medium">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $room->getStatusBadgeClass() ?>">
                            <?= $room->getStatusLabel() ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>