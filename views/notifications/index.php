<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
            <p class="text-gray-600">Kelola notifikasi Anda</p>
        </div>
        <?php if ($unreadCount > 0): ?>
        <form method="POST" action="<?= url('notifications/read-all') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="px-4 py-2 text-sm text-primary-600 hover:text-primary-700 font-medium">Tandai Semua Dibaca</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (!empty($notifications['data'])): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($notifications['data'] as $notif): ?>
            <div class="px-5 py-4 hover:bg-gray-50 flex items-start gap-4 <?= $notif->is_read ? '' : 'bg-primary-50/50' ?>">
                <a href="<?= $notif->getReferenceUrl() ?>" class="flex items-start gap-4 flex-1 min-w-0">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center <?= $notif->is_read ? 'bg-gray-100 text-gray-500' : 'bg-primary-100 text-primary-600' ?>">
                        <?php if ($notif->type === 'invoice_due' || $notif->type === 'invoice_overdue'): ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <?php elseif ($notif->type === 'payment_received' || $notif->type === 'payment_verified'): ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php elseif ($notif->type === 'complaint_created' || $notif->type === 'complaint_updated'): ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <?php else: ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($notif->title) ?></h3>
                            <span class="text-xs text-gray-400 whitespace-nowrap"><?= time_ago($notif->created_at) ?></span>
                        </div>
                        <p class="text-sm text-gray-500 truncate mt-1"><?= htmlspecialchars($notif->message) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= $notif->getTypeLabel() ?></p>
                    </div>
                </a>
                <?php if (!$notif->is_read): ?>
                <form method="POST" action="<?= url('notifications/' . $notif->id . '/read') ?>" class="inline flex-shrink-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="p-2 text-gray-400 hover:text-gray-600" title="Tandai dibaca">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($notifications['last_page'] > 1): ?>
        <div class="px-5 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan <?= $notifications['from'] ?>-<?= $notifications['to'] ?> dari <?= $notifications['total'] ?></p>
                <div class="flex gap-2">
                    <?php if ($notifications['current_page'] > 1): ?>
                    <a href="?page=<?= $notifications['current_page'] - 1 ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($notifications['has_more_pages']): ?>
                    <a href="?page=<?= $notifications['current_page'] + 1 ?>" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada notifikasi</h3>
            <p class="mt-1 text-sm text-gray-500">Semua tertangkap, santai saja.</p>
        </div>
        <?php endif; ?>
    </div>
</div>