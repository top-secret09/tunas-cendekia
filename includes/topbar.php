<?php
// includes/topbar.php
// Header topbar konsisten di setiap halaman untuk SEMUA akun
$user = get_current_user_data();
$display_name = $user['nama'] ?? $user['username'] ?? 'Pengguna';
$role_badge = $user['peran'] ?? 'Admin';
$notifications = get_user_notifications();
$unread_count = 0;
foreach ($notifications as $n) {
    if (!empty($n['unread'])) $unread_count++;
}
?>
<!-- BEGIN: Topbar -->
<header class="bg-white border-b border-slate-200/80 px-4 sm:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-xs" data-purpose="top-navigation">
    <!-- Left: Hamburger icon toggle for mobile -->
    <div class="flex items-center gap-3">
        <button id="toggle-sidebar-btn" aria-label="Toggle Menu" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer" type="button">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
        <span class="text-sm font-semibold text-slate-800 lg:hidden">Bimbel Tunas Cendekia</span>
    </div>



    <!-- Right: Date, Notification & User Profile -->
    <div class="flex items-center gap-3 sm:gap-6 relative">
        <!-- Date Badge -->
        <div id="date-badge" class="hidden md:flex items-center gap-2 text-slate-600 font-medium text-xs bg-slate-100/80 px-3.5 py-1.5 rounded-full border border-slate-200/60 cursor-pointer">
            <span class="material-symbols-outlined text-[16px] text-slate-500">calendar_today</span>
            <span><?= get_hari_tanggal_sekarang() ?></span>
        </div>



        <!-- Notification Bell (Interactive Popover) -->
        <div class="relative" id="notification-wrapper">
            <button id="notification-btn" aria-label="Notifikasi" class="relative text-slate-700 hover:text-slate-900 transition-colors p-2 rounded-xl hover:bg-slate-100 cursor-pointer focus:outline-none" type="button">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span id="notification-bell-dot" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-600 rounded-full ring-2 ring-white <?= ($unread_count > 0) ? '' : 'hidden' ?>"></span>
            </button>

            <!-- Dropdown Panel Notifikasi -->
            <?php include __DIR__ . '/notification_panel.php'; ?>
        </div>



        <!-- User Profile Pill & Dropdown -->
        <div class="relative flex items-center select-none" id="user-profile-wrapper">
            <button id="user-profile-btn" aria-expanded="false" class="flex items-center gap-2 sm:gap-2.5 cursor-pointer p-1 sm:px-2 sm:py-1 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none" type="button">
                <div class="w-8 h-8 rounded-full bg-[#143d73] flex items-center justify-center text-white text-xs sm:text-sm font-semibold shadow-xs">
                    <?= strtoupper(substr($display_name, 0, 1)) ?>
                </div>


                <div class="hidden sm:flex flex-col text-left leading-tight">
                    <span class="text-xs font-bold text-slate-800"><?= htmlspecialchars($display_name) ?></span>
                    <span class="text-[10px] text-slate-500 font-medium"><?= htmlspecialchars($role_badge) ?></span>
                </div>


                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>

            <!-- Dropdown Profil -->
            <?php include __DIR__ . '/profile_dropdown.php'; ?>
        </div>


    </div>


</header>
<!-- Date Modal -->
<div id="date-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg p-4 max-w-sm w-full">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold">Tanggal</h3>
      <button id="close-date-modal" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <p class="mt-2"><?= get_hari_tanggal_sekarang() ?></p>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggleBtn = document.getElementById('toggle-sidebar-btn');
    const sidebar = document.getElementById('main-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const closeBtn = document.getElementById('close-sidebar-btn');

    const dateBadge = document.getElementById('date-badge');
    const dateModal = document.getElementById('date-modal');
    const closeDateModal = document.getElementById('close-date-modal');

    // =========================
    // SIDEBAR TOGGLE
    // =========================

    if (toggleBtn && sidebar) {

        toggleBtn.addEventListener('click', function () {

            // MODE DESKTOP
            if (window.innerWidth >= 1024) {

                sidebar.classList.toggle('sidebar-collapsed');

            } 
            // MODE MOBILE
            else {

                sidebar.classList.remove('-translate-x-full');

                if (backdrop) {
                    backdrop.classList.remove('hidden');
                    backdrop.classList.remove('opacity-0');
                }
            }
        });
    }

    // TUTUP SIDEBAR MOBILE
    function closeSidebar() {

        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
        }

        if (backdrop) {
            backdrop.classList.add('opacity-0');

            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // =========================
    // DATE MODAL
    // =========================

    if (dateBadge && dateModal) {
        dateBadge.addEventListener('click', function () {
            dateModal.classList.remove('hidden');
        });
    }

    if (closeDateModal && dateModal) {
        closeDateModal.addEventListener('click', function () {
            dateModal.classList.add('hidden');
        });
    }

});
</script>
<!-- END: Topbar -->
