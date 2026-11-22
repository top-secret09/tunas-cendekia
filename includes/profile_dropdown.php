<?php
// includes/profile_dropdown.php
// Dropdown menu profil pojok kanan atas konsisten untuk SEMUA akun
$user = get_current_user_data();
$display_name = $user['nama'] ?? $user['username'] ?? 'Pengguna';
$role_badge = $user['peran'] ?? 'Admin';
$username_sub = '@' . ($user['username'] ?? 'user');
?>
<!-- Admin Profile Dropdown Menu -->
<div id="user-profile-dropdown" class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 py-2 divide-y divide-slate-100 hidden opacity-0 translate-y-2 transition-all duration-200">
    <!-- User Card -->
    <div class="px-4 py-3 flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-[#143d73] flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 shadow-sm">
            <?= strtoupper(substr($display_name, 0, 1)) ?>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($display_name) ?></p>
            <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($username_sub) ?></p>
            <span class="inline-block mt-1.5 px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[11px] font-semibold tracking-wide border border-blue-100/60">
                <?= htmlspecialchars($role_badge) ?>
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="py-1.5">
        <button type="button" class="btn-trigger-profile w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors text-left">
            <span class="material-symbols-outlined text-[18px] text-slate-400">person</span>
            <span>Profil Saya</span>
        </button>
    </div>

    <!-- Logout -->
    <div class="pt-1.5">
        <button type="button" class="btn-trigger-logout w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left">
            <span class="material-symbols-outlined text-[18px] text-red-500">logout</span>
            <span>Keluar / Logout</span>
        </button>
    </div>
</div>
