<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Komplain</h1>
            <p class="text-gray-600">#<?= $complaint->id ?></p>
        </div>
        <div class="flex gap-2">
            <?php if (auth()->role !== 'tenant' && $complaint->status !== 'resolved' && $complaint->status !== 'closed'): ?>
            <button type="button" onclick="resolveComplaint()" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tandai Selesai</button>
            <?php endif; ?>
            <a href="<?= url('maintenance') ?>" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($complaint->title) ?></h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $complaint->getStatusBadgeClass() ?>">
                                <?= $complaint->getStatusLabel() ?>
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $complaint->getPriorityBadgeClass() ?>">
                                <?= $complaint->getPriorityLabel() ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Kategori: <span class="font-medium text-gray-900"><?= $complaint->getCategoryLabel() ?></span></p>
                    </div>
                    <span class="text-sm text-gray-400"><?= time_ago($complaint->created_at) ?></span>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-gray-600 whitespace-pre-wrap"><?= htmlspecialchars($complaint->description) ?></p>
                </div>
                
                <?php if ($complaint->getPhotoBeforeUrl()): ?>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-900 mb-2">Foto Masalah:</p>
                    <img src="<?= $complaint->getPhotoBeforeUrl() ?>" alt="Foto masalah" class="max-w-full h-auto rounded-lg border border-gray-200 cursor-pointer" onclick="window.open(this.src, '_blank')">
                </div>
                <?php endif; ?>
            </div>

            <!-- Foto After (Resolved) -->
            <?php if ($complaint->photo_after): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Foto Setelah Perbaikan</h3>
                <img src="<?= $complaint->getPhotoAfterUrl() ?>" alt="Foto setelah perbaikan" class="max-w-full h-auto rounded-lg border border-gray-200 cursor-pointer" onclick="window.open(this.src, '_blank')">
            </div>
            <?php endif; ?>

            <!-- Progress & Updates -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Komplain Dibuat</p>
                            <p class="text-sm text-gray-500">Oleh <?= htmlspecialchars($tenant->user->name) ?> pada <?= format_datetime($complaint->created_at) ?></p>
                        </div>
                    </div>
                    
                    <?php if ($complaint->assigned_to && $assignee): ?>
                    <div class="flex gap-4 border-l-2 border-gray-200 pl-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center -ml-6">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Ditugaskan ke <?= htmlspecialchars($assignee->name) ?></p>
                            <p class="text-sm text-gray-500">Status: Diproses</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($complaint->status === 'resolved' || $complaint->status === 'closed'): ?>
                    <div class="flex gap-4 border-l-2 border-green-200 pl-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center -ml-6">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Komplain Diselesaikan</p>
                            <p class="text-sm text-gray-500"><?= $complaint->resolved_at ? format_datetime($complaint->resolved_at) : '' ?></p>
                            <?php if ($complaint->resolved_notes): ?>
                            <p class="text-sm text-gray-600 mt-1">Catatan: <?= htmlspecialchars($complaint->resolved_notes) ?></p>
                            <?php endif; ?>
                            <?php if ($complaint->actual_cost > 0): ?>
                            <p class="text-sm text-gray-600 mt-1">Biaya: <?= format_currency($complaint->actual_cost) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assign Form (Owner/Admin) -->
            <?php if (auth()->role !== 'tenant' && $complaint->status === 'open'): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tugaskan ke Petugas</h3>
                <form method="POST" action="<?= url('maintenance/' . $complaint->id . '/assign') ?>" class="flex gap-3">
                    <?= csrf_field() ?>
                    <select name="assigned_to" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih Petugas</option>
                        <?php foreach ($staff as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= ucfirst($s['role']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">Tugaskan</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Resolve Form (Owner/Admin) -->
            <?php if (auth()->role !== 'tenant' && ($complaint->status === 'in_progress' || $complaint->status === 'open')): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tandai Selesai</h3>
                <form method="POST" action="<?= url('maintenance/' . $complaint->id . '/resolve') ?>" enctype="multipart/form-data" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label for="resolved_notes" class="block text-sm font-medium text-gray-700">Catatan Penyelesaian</label>
                        <textarea name="resolved_notes" id="resolved_notes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Jelaskan apa yang telah dilakukan..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="actual_cost" class="block text-sm font-medium text-gray-700">Biaya Aktual</label>
                            <input type="number" name="actual_cost" id="actual_cost" min="0" step="1000" value="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="photo_after" class="block text-sm font-medium text-gray-700">Foto Hasil (opsional)</label>
                            <input type="file" name="photo_after" id="photo_after" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">Selesaikan</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Pelapor</dt>
                        <dd class="font-medium text-gray-900"><?= htmlspecialchars($tenant->user->name) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Telepon</dt>
                        <dd class="font-medium text-gray-900"><?= htmlspecialchars($tenant->phone ?? '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Kamar</dt>
                        <dd class="font-medium text-gray-900"><?= htmlspecialchars($room->room_number) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Properti</dt>
                        <dd class="font-medium text-gray-900"><?= htmlspecialchars($property->name) ?></dd>
                    </div>
                    <?php if ($assignee): ?>
                    <div>
                        <dt class="text-sm text-gray-500">Petugas</dt>
                        <dd class="font-medium text-gray-900"><?= htmlspecialchars($assignee->name) ?></dd>
                    </div>
                    <?php endif; ?>
                    <div>
                        <dt class="text-sm text-gray-500">Estimasi Biaya</dt>
                        <dd class="font-medium text-gray-900"><?= format_currency($complaint->estimated_cost) ?></dd>
                    </div>
                    <?php if ($complaint->actual_cost > 0): ?>
                    <div>
                        <dt class="text-sm text-gray-500">Biaya Aktual</dt>
                        <dd class="font-medium text-gray-900"><?= format_currency($complaint->actual_cost) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
function resolveComplaint() {
    const notes = prompt('Catatan penyelesaian:');
    if (notes === null) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('maintenance/' . $complaint->id . '/resolve') ?>';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '<?= csrf_token() ?>';
    form.appendChild(csrf);
    
    const notesInput = document.createElement('input');
    notesInput.type = 'hidden';
    notesInput.name = 'resolved_notes';
    notesInput.value = notes;
    form.appendChild(notesInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>