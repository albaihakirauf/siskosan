<?php
$role = $auth->role;
?>

<!-- Header Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php if ($role === 'owner' || $role === 'admin'): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Kamar</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= $totalRooms ?? 0 ?></p>
            </div>
            <div class="p-3 bg-blue-100 rounded-xl">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-green-600 font-medium"><?= $occupiedRooms ?? 0 ?> Terisi</span>
            <span class="text-gray-500"><?= $emptyRooms ?? 0 ?> Kosong</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= $occupancyRate ?? 0 ?>%</p>
            </div>
            <div class="p-3 bg-green-100 rounded-xl">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-primary-600 rounded-full" style="width: <?= $occupancyRate ?? 0 ?>%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Pendapatan Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= format_currency($totalRevenue ?? 0) ?></p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-xl">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">Tunggakan: <?= format_currency($totalArrears ?? 0) ?></p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Penyewa Aktif</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= $activeTenants ?? 0 ?></p>
            </div>
            <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
        <div class="mt-2 flex gap-4 text-sm">
            <span class="text-green-600"><?= $paidInvoices ?? 0 ?> Lunas</span>
            <span class="text-red-600"><?= $overdueInvoices ?? 0 ?> Terlambat</span>
        </div>
    </div>
    <?php elseif ($role === 'tenant'): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Tagihan Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    <?php if ($currentInvoice): ?>
                        <?= format_currency($currentInvoice->total_amount) ?>
                    <?php else: ?>
                        <span class="text-gray-400">Tidak ada</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="p-3 bg-primary-100 rounded-xl">
                <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <?php if ($currentInvoice && $currentInvoice->status !== 'paid'): ?>
        <a href="<?= url('invoices/' . $currentInvoice->id) ?>" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
            Bayar Sekarang
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Tunggakan</p>
                <p class="text-2xl font-bold text-red-600 mt-1"><?= format_currency($totalArrears ?? 0) ?></p>
            </div>
            <div class="p-3 bg-red-100 rounded-xl">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <?php if ($totalArrears > 0): ?>
        <a href="<?= url('invoices') ?>" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
            Lihat Detail
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Kamar Anda</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    <?php if ($tenant): ?>
                        <?= htmlspecialchars($tenant->room()->room_number ?? '-') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div class="p-3 bg-gray-100 rounded-xl">
                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Komplain Saya</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= count($recentComplaints ?? []) ?></p>
            </div>
            <div class="p-3 bg-orange-100 rounded-xl">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <a href="<?= url('maintenance/create') ?>" class="mt-3 inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            Buat Komplain
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <?php if ($role === 'owner' || $role === 'admin'): ?>
        <!-- Recent Payments -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pembayaran Terbaru</h3>
                <a href="<?= url('payments') ?>" class="text-sm text-primary-600 hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyewa</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($recentPayments)): ?>
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 text-sm text-gray-900"><?= format_date($payment['payment_date']) ?></td>
                                <td class="px-5 py-4 text-sm text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></td>
                                <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($payment['room_number']) ?></td>
                                <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($payment['invoice_period'] ?? '-') ?></td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 text-right"><?= format_currency($payment['amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada pembayaran</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Komplain Terbaru</h3>
                <a href="<?= url('maintenance') ?>" class="text-sm text-primary-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (!empty($recentComplaints)): ?>
                    <?php foreach ($recentComplaints as $complaint): ?>
                    <div class="px-5 py-4 hover:bg-gray-50 flex items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($complaint['title']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($complaint['tenant_name']) ?> - Kamar <?= htmlspecialchars($complaint['room_number']) ?></p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"><?= $complaint['category_label'] ?></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint['priority_badge'] ?>">
                                    <?= $complaint['priority_label'] ?>
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint['status_badge'] ?>">
                                    <?= $complaint['status_label'] ?>
                                </span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap"><?= time_ago($complaint['created_at']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">Belum ada komplain</div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($role === 'tenant'): ?>
        <!-- Unpaid Invoices -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Tagihan Belum Lunas</h3>
                <a href="<?= url('invoices') ?>" class="text-sm text-primary-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (!empty($unpaidInvoices)): ?>
                    <?php foreach ($unpaidInvoices as $invoice): ?>
                    <div class="px-5 py-4 hover:bg-gray-50 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900"><?= $invoice->getPeriodLabel() ?></p>
                            <p class="text-sm text-gray-500">
                                Status: <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $invoice->getStatusBadgeClass() ?>"><?= $invoice->getStatusLabel() ?></span>
                                <?php if ($invoice->isOverdue()): ?>
                                    <span class="ml-2 text-red-600 text-xs">Terlambat <?= $invoice->getDaysOverdue() ?> hari</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900"><?= format_currency($invoice->getRemainingAmount()) ?></p>
                            <a href="<?= url('invoices/' . $invoice->id) ?>" class="text-sm text-primary-600 hover:underline">Bayar</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">Semua tagihan lunas 🎉</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Complaints for Tenant -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Komplain Saya</h3>
                <a href="<?= url('maintenance/create') ?>" class="text-sm text-primary-600 hover:underline">Buat baru</a>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (!empty($recentComplaints)): ?>
                    <?php foreach ($recentComplaints as $complaint): ?>
                    <div class="px-5 py-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($complaint->title) ?></p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint->getStatusBadgeClass() ?>"><?= $complaint->getStatusLabel() ?></span>
                            <span class="text-xs text-gray-500"><?= time_ago($complaint->created_at) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">Belum ada komplain</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Quick Actions -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
                <?php if ($role === 'owner' || $role === 'admin'): ?>
                <a href="<?= url('properties/create') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-primary-100 rounded-lg"><svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Tambah Properti</span>
                </a>
                <a href="<?= url('rooms/create') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-green-100 rounded-lg"><svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Tambah Kamar</span>
                </a>
                <a href="<?= url('tenants/create') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-blue-100 rounded-lg"><svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Tambah Penyewa</span>
                </a>
                <form method="POST" action="<?= url('invoices/generate') ?>" onsubmit="return confirm('Generate tagihan bulan ini?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                        <div class="p-2 bg-yellow-100 rounded-lg"><svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                        <span class="text-sm font-medium text-gray-700">Generate Tagihan Bulanan</span>
                    </button>
                </form>
                <a href="<?= url('reports/financial') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-purple-100 rounded-lg"><svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Lihat Laporan Keuangan</span>
                </a>
                <?php elseif ($role === 'tenant'): ?>
                <a href="<?= url('invoices') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-primary-100 rounded-lg"><svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Lihat Tagihan</span>
                </a>
                <a href="<?= url('payments/create') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-green-100 rounded-lg"><svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Bayar Tagihan</span>
                </a>
                <a href="<?= url('maintenance/create') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-orange-100 rounded-lg"><svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Ajukan Komplain</span>
                </a>
                <a href="<?= url('profile') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-gray-100 rounded-lg"><svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
                    <span class="text-sm font-medium text-gray-700">Profil Saya</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Property List for Owner/Admin -->
        <?php if (($role === 'owner' || $role === 'admin') && !empty($properties)): ?>
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Properti Anda</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($properties as $prop): ?>
                <a href="<?= url('properties/' . $prop['id']) ?>" class="block px-5 py-3 hover:bg-gray-50 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($prop['name']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($prop['address']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900"><?= $prop['occupancy_rate'] ?? 0 ?>%</p>
                        <p class="text-xs text-gray-500"><?= $prop['occupied_count'] ?? 0 ?>/<?= $prop['rooms_count'] ?? 0 ?> kamar</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>