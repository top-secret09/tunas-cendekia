<?php
// includes/sidebar.php
if (!isset($current_page)) {
    $current_page = '';
}
$user = get_current_user_data();
$role = $user['peran'] ?? 'Admin';

// Define Menu Items strictly matching Stitch designs
$menus = [];
if ($role === 'Admin') {
    $menus = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'grid_view'],
        ['key' => 'siswa', 'label' => 'Data Siswa', 'url' => 'siswa.php', 'icon' => 'group'],
        ['key' => 'pengajar', 'label' => 'Data Pengajar', 'url' => 'pengajar.php', 'icon' => 'school'],
        ['key' => 'kelas', 'label' => 'Data Kelas', 'url' => 'kelas.php', 'icon' => 'domain'],
        ['key' => 'mapel', 'label' => 'Program Belajar', 'url' => 'mapel.php', 'icon' => 'menu_book'],
        ['key' => 'jadwal', 'label' => 'Jadwal Bimbingan', 'url' => 'jadwal.php', 'icon' => 'calendar_month'],
        ['key' => 'pendaftaran', 'label' => 'Pendaftaran Siswa', 'url' => 'pendaftaran.php', 'icon' => 'person_add'],
        ['key' => 'pembayaran', 'label' => 'Pembayaran', 'url' => 'pembayaran.php', 'icon' => 'account_balance_wallet'],
        ['key' => 'laporan', 'label' => 'Laporan', 'url' => 'laporan.php', 'icon' => 'assessment']
    ];
} else if ($role === 'Guru') {
    $menus = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'grid_view'],
        ['key' => 'jadwal', 'label' => 'Jadwal Mengajar', 'url' => 'jadwal.php', 'icon' => 'calendar_month'],
        ['key' => 'absensi', 'label' => 'Absensi Siswa', 'url' => 'absensi.php', 'icon' => 'fact_check'],
        ['key' => 'materi', 'label' => 'Materi Pembelajaran', 'url' => 'materi.php', 'icon' => 'auto_stories'],
        ['key' => 'latihan', 'label' => 'Latihan Soal', 'url' => 'latihan.php', 'icon' => 'quiz'],
        ['key' => 'nilai', 'label' => 'Hasil Latihan', 'url' => 'nilai.php', 'icon' => 'grade']
    ];
} else { // Siswa
    $menus = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'grid_view'],
        ['key' => 'jadwal', 'label' => 'Jadwal Belajar', 'url' => 'jadwal.php', 'icon' => 'calendar_month'],
        ['key' => 'kelas', 'label' => 'Kelas Saya', 'url' => 'kelas.php', 'icon' => 'school'],
        ['key' => 'latihan', 'label' => 'Latihan Soal', 'url' => 'latihan.php', 'icon' => 'quiz'],
        ['key' => 'nilai', 'label' => 'Riwayat Nilai', 'url' => 'nilai.php', 'icon' => 'grade'],
        ['key' => 'pembayaran', 'label' => 'Pembayaran', 'url' => 'pembayaran.php', 'icon' => 'payments']
    ];
}
?>

<!-- Mobile Sidebar Backdrop Overlay -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300 opacity-0"></div>

<!-- BEGIN: Sidebar Persis Stitch (bg-[#143d73] or bg-[#1b3e6b]) -->
<style>
    @media (min-width: 1024px) {
        #main-sidebar.sidebar-collapsed {
            width: 0 !important;
            min-width: 0 !important;
            flex-basis: 0 !important;
            overflow: hidden !important;
            border: 0 !important;
        }
    }
</style>

<aside id="main-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#143d73] text-white flex-shrink-0 flex flex-col justify-between shadow-2xl transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto select-none min-h-screen" data-purpose="app-sidebar">
    <div class="flex flex-col flex-1 overflow-y-auto">
        <!-- Brand Logo Header Persis Stitch Design -->
        <div class="px-6 py-5 flex items-center justify-between border-b border-white/10">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 flex-shrink-0 rounded-full bg-white p-1 flex items-center justify-center shadow-md overflow-hidden">
                    <img alt="Logo Bimbel Tunas Cendekia" class="w-8 h-8 object-contain" src="../assets/images/logo.png">
                </div>
                <div class="leading-tight">
                    <h1 class="font-bold text-base tracking-wide text-white">Bimbel</h1>
                    <p class="text-xs text-blue-200 font-medium tracking-tight">Tunas Cendekia</p>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button id="close-sidebar-btn" type="button" class="lg:hidden p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/10" aria-label="Tutup Menu">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Role Badge Info -->
        

        <!-- Navigation Menu -->
        <nav class="mt-3 px-3 space-y-1">
            <?php foreach ($menus as $m): 
                $isActive = ($current_page === $m['key']);
            ?>
                <a href="<?= $m['url'] ?>" class="flex items-center gap-3.5 px-4 py-2.5 rounded-lg font-medium transition-colors text-sm <?= $isActive ? 'bg-[#3b82f6] text-white shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' ?>">
                    <span class="material-symbols-outlined text-[20px] <?= $isActive ? 'text-white' : 'opacity-80' ?>"><?= $m['icon'] ?></span>
                    <span><?= htmlspecialchars($m['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Sidebar Bottom: Logout Persis Stitch Design -->
    <div class="px-3 pb-6 pt-4 border-t border-white/10">
        <a href="logout.php" class="btn-trigger-logout w-full flex items-center gap-3.5 px-4 py-2.5 rounded-lg text-blue-100 hover:text-white hover:bg-white/10 transition-colors text-sm font-medium text-left">
            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</aside>
<!-- END: Sidebar -->
