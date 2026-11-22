<?php
// siswa/jadwal.php - Jadwal Belajar Siswa
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Jadwal Belajar";
$current_page = "jadwal";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

// Ambil kelas aktif siswa dari pendaftaran
$kelas_siswa = db_fetch_all("SELECT id_kelas FROM pendaftaran WHERE id_siswa = $id_siswa AND status_pendaftaran = 'Diterima'");
$id_kelas_list = array_column($kelas_siswa, 'id_kelas');
if (empty($id_kelas_list)) $id_kelas_list = [1, 2];
$id_kelas_str = implode(',', array_map('intval', $id_kelas_list));

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$filter_hari = db_escape($_GET['hari'] ?? 'all');
$search = db_escape(trim($_GET['q'] ?? ''));

$where_clauses = ["(j.id_kelas IN ($id_kelas_str) OR j.id_kelas IS NULL OR j.id_kelas = 0)"];
if ($filter_hari !== 'all' && in_array($filter_hari, $hari_list)) {
    $where_clauses[] = "j.hari = '$filter_hari'";
}
if (!empty($search)) {
    $where_clauses[] = "(m.nama_mapel LIKE '%$search%' OR g.nama_guru LIKE '%$search%' OR k.nama_kelas LIKE '%$search%')";
}
$where_sql = implode(' AND ', $where_clauses);

$jadwal_list = db_fetch_all("SELECT j.*, m.nama_mapel, g.nama_guru, g.no_telepon as kontak_guru, k.nama_kelas, k.tingkat 
                             FROM jadwal j 
                             JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
                             JOIN guru g ON j.id_guru = g.id_guru 
                             LEFT JOIN kelas k ON j.id_kelas = k.id_kelas 
                             WHERE $where_sql 
                             ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 px-4 sm:px-8 py-6 w-full flex flex-col gap-6 overflow-y-auto">
        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Jadwal Belajar</h1>
                <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span class="text-slate-700 font-semibold">Jadwal Belajar</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition-all" type="button">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    <span>Cetak</span>
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#19376D] hover:bg-blue-800 text-white text-xs font-semibold shadow-xs transition-all" type="button">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Unduh Jadwal (PDF)</span>
                </button>
            </div>
        </div>

        <!-- Date Selector / 7-Day Pill Navigation -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 text-[20px]">calendar_month</span>
                    <span class="text-sm font-bold text-slate-900">Pilih Hari Belajar (Mingguan)</span>
                </div>
                <a href="jadwal.php?hari=all" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1.5 transition-colors">
                    <span>Lihat Semua Hari</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2.5 pt-2">
                <a href="jadwal.php?hari=all" class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl border transition-all text-center <?= ($filter_hari === 'all') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 border-blue-600' : 'border-slate-200/80 bg-slate-50/50 hover:bg-slate-100 text-slate-600' ?>">
                    <span class="text-[11px] font-medium uppercase tracking-wider <?= ($filter_hari === 'all') ? 'text-blue-100' : 'text-slate-500' ?>">Semua</span>
                    <span class="text-sm font-bold mt-0.5">Hari</span>
                </a>
                <?php 
                $abbr = ['Senin'=>'Sen', 'Selasa'=>'Sel', 'Rabu'=>'Rab', 'Kamis'=>'Kam', 'Jumat'=>'Jum', 'Sabtu'=>'Sab', 'Minggu'=>'Min'];
                foreach ($hari_list as $h): 
                    $isActive = ($filter_hari === $h);
                    $isSun = ($h === 'Minggu');
                ?>
                    <a href="jadwal.php?hari=<?= $h ?>" class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl border transition-all text-center <?= $isActive ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 border-blue-600' : 'border-slate-200/80 bg-slate-50/50 hover:bg-slate-100 text-slate-600' ?>">
                        <span class="text-[11px] font-medium uppercase tracking-wider <?= $isActive ? 'text-blue-100' : ($isSun ? 'text-red-500' : 'text-slate-500') ?>"><?= $abbr[$h] ?></span>
                        <span class="text-sm font-bold mt-0.5 <?= $isActive ? 'text-white' : ($isSun ? 'text-red-500' : 'text-slate-800') ?>"><?= substr($h, 0, 3) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Table Sesi Jadwal -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                    <h2 class="text-sm font-bold text-slate-900">
                        <?= ($filter_hari === 'all') ? 'Semua Sesi Belajar Anda' : 'Sesi Belajar Hari ' . htmlspecialchars($filter_hari) ?>
                    </h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                        <?= count($jadwal_list) ?> Sesi Terdaftar
                    </span>
                </div>
                <form method="GET" action="jadwal.php" class="flex items-center gap-3">
                    <?php if ($filter_hari !== 'all'): ?>
                        <input type="hidden" name="hari" value="<?= htmlspecialchars($filter_hari) ?>">
                    <?php endif; ?>
                    <div class="relative min-w-[240px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                        <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-9 pr-3 py-2 bg-slate-50 rounded-lg text-xs text-slate-700 placeholder:text-slate-400 border border-slate-200 focus:outline-none focus:border-blue-600 focus:bg-white transition" placeholder="Cari mapel / pengajar..." type="text">
                    </div>
                </form>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/70 uppercase tracking-wider">
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">HARI &amp; WAKTU</th>
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">MATA PELAJARAN</th>
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">TENTOR / PENGAJAR</th>
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">KELAS</th>
                            <th class="py-4 px-6 sm:px-8 font-medium text-center" scope="col">STATUS</th>
                            <th class="py-4 px-6 sm:px-8 font-medium text-center" scope="col">HUBUNGI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($jadwal_list)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">event_busy</span>
                                    Belum ada sesi bimbingan belajar untuk filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $curr_day = date('l');
                            $day_map = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
                            $today_indo = $day_map[$curr_day] ?? '';
                            $curr_time = date('H:i:s');

                            foreach ($jadwal_list as $j): 
                                $isToday = ($j['hari'] === $today_indo);
                                $isOngoing = $isToday && ($curr_time >= $j['jam_mulai'] && $curr_time <= $j['jam_selesai']);
                                $isPast = ($isToday && $curr_time > $j['jam_selesai']);
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-5 px-6 sm:px-8">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 text-xs">
                                                <?= htmlspecialchars($j['hari']) ?>, <?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?> WIB
                                            </span>
                                            <span class="text-[11px] text-slate-400 mt-0.5">
                                                <?= htmlspecialchars(format_tanggal_indo($j['tanggal_belajar'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            <?= htmlspecialchars($j['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-[11px] flex items-center justify-center shrink-0">
                                                <?= strtoupper(substr($j['nama_guru'], 0, 1)) ?>
                                            </div>
                                            <span class="font-semibold text-slate-800 text-xs"><?= htmlspecialchars($j['nama_guru']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8">
                                        <span class="font-medium text-slate-800"><?= htmlspecialchars($j['nama_kelas'] ?? 'Semua Kelas') ?></span>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8 text-center">
                                        <?php if ($isOngoing): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Berlangsung</span>
                                            </span>
                                        <?php elseif ($isPast): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                <span>Selesai</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <span>Akan Datang</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8 text-center">
                                        <?php if (!empty($j['kontak_guru'])): ?>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $j['kontak_guru']) ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs transition">
                                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                                <span>WhatsApp</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 bg-white text-xs text-slate-500">
                <p>
                    Menampilkan <span class="font-semibold text-slate-700">1–<?= count($jadwal_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($jadwal_list) ?></span> sesi
                </p>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-600 text-white text-xs font-bold shadow-xs" type="button">1</button>
                </div>
            </div>
        </div>

        <!-- Bottom Note Akademik -->
        <div class="rounded-2xl bg-white border border-slate-200/70 p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">school</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-800">Tata Tertib Bimbingan Belajar</span>
                    <span class="text-xs text-slate-500">Pastikan hadir 10 menit sebelum kelas dimulai, membawa modul pelajaran, dan melakukan presensi kehadiran.</span>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="kehadiran.php" class="px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold rounded-xl transition">
                    Cek Presensi Kehadiran
                </a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
