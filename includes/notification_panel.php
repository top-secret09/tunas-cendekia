<?php
// includes/notification_panel.php
// Panel notifikasi konsisten untuk SEMUA akun (Admin, Guru, Siswa)
if (!isset($notifications)) {
    $notifications = get_user_notifications();
    $unread_count = 0;
    foreach ($notifications as $n) {
        if (!empty($n['unread'])) $unread_count++;
    }
}
?>
<!-- Notification Popover Overlay Anchored Under the Bell -->
<div id="notification-popover" class="absolute top-full right-0 mt-2 z-50 w-80 sm:w-96 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transition-all duration-200 hidden opacity-0 translate-y-2">
    <!-- Header -->
    <div class="p-4 bg-slate-50/90 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="font-display text-sm sm:text-base text-slate-900 font-bold">Notifikasi</h2>
            <span id="notif-badge-pill" class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                <?= $unread_count ?> Baru
            </span>
        </div>
        <button id="mark-all-read-btn" type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors cursor-pointer focus:outline-none">
            Tandai Dibaca
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex px-4 bg-slate-50/90 border-b border-slate-100 gap-2">
        <button id="tab-notif-all" type="button" class="tab-notif active text-xs font-semibold text-blue-600 pb-2.5 px-3 relative focus:outline-none">
            Semua
            <span class="tab-indicator absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></span>
        </button>
        <button id="tab-notif-unread" type="button" class="tab-notif text-xs font-medium text-slate-500 hover:text-slate-800 pb-2.5 px-3 transition-colors focus:outline-none">
            Belum Dibaca
        </button>
    </div>

    <!-- Notification Items List -->
    <div id="notification-items-wrapper" class="max-h-[380px] overflow-y-auto divide-y-0">
        <?php if (empty($notifications)): ?>
            <div class="p-6 text-center text-slate-400 text-xs">
                <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">notifications_off</span>
                <p>Tidak ada notifikasi saat ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $idx => $notif): 
                $is_unread = !empty($notif['unread']);
            ?>
                <div class="notification-item p-3.5 bg-white hover:bg-slate-50 transition-colors cursor-pointer flex items-start gap-3 relative border-b border-slate-100 <?= $is_unread ? 'is-unread' : 'is-read opacity-85' ?>" data-unread="<?= $is_unread ? '1' : '0' ?>">
                    <div class="w-9 h-9 rounded-full <?= $notif['color'] ?? 'bg-blue-100 text-blue-600' ?> flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                        <span class="material-symbols-outlined text-[19px]"><?= $notif['icon'] ?? 'notifications' ?></span>
                    </div>
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($notif['title']) ?></span>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap ml-2"><?= htmlspecialchars($notif['time']) ?></span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5 leading-snug line-clamp-2"><?= htmlspecialchars($notif['desc']) ?></p>
                    </div>
                    <?php if ($is_unread): ?>
                        <span class="w-2 h-2 rounded-full bg-blue-600 absolute top-4 right-3 unread-dot"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
