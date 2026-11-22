<?php
// siswa/kehadiran.php - Riwayat Kehadiran Siswa
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Kehadiran Saya";
$current_page = "kehadiran";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

// Hitung Statistik Presensi Siswa
$hadir = db_fetch_one("SELECT COUNT(*) as total FROM kehadiran WHERE id_siswa = $id_siswa AND status_kehadiran = 'Hadir'")['total'] ?? 0;
$izin = db_fetch_one("SELECT COUNT(*) as total FROM kehadiran WHERE id_siswa = $id_siswa AND status_kehadiran = 'Izin'")['total'] ?? 0;
$sakit = db_fetch_one("SELECT COUNT(*) as total FROM kehadiran WHERE id_siswa = $id_siswa AND status_kehadiran = 'Sakit'")['total'] ?? 0;
$alpa = db_fetch_one("SELECT COUNT(*) as total FROM kehadiran WHERE id_siswa = $id_siswa AND status_kehadiran = 'Alpa'")['total'] ?? 0;

$kehadiran_list = db_fetch_all("SELECT kh.*, m.nama_mapel, g.nama_guru, j.hari, j.jam_mulai, j.jam_selesai 
                                FROM kehadiran kh 
                                JOIN jadwal j ON kh.id_jadwal = j.id_jadwal 
                                JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
                                JOIN guru g ON j.id_guru = g.id_guru 
                                WHERE kh.id_siswa = $id_siswa 
                                ORDER BY kh.tanggal_kehadiran DESC, kh.id_kehadiran DESC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 px-4 sm:px-8 py-6 w-full flex flex-col gap-6 overflow-y-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Riwayat Presensi Kehadiran</h1>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span class="text-slate-700 font-semibold">Kehadiran</span>
                </div>
            </div>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition-all" type="button">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Cetak Rekap Presensi</span>
            </button>
        </div>

        <!-- 4 Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Hadir</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block"><?= $hadir ?> <span class="text-xs font-normal text-slate-400">Sesi</span></span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Izin</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block"><?= $izin ?> <span class="text-xs font-normal text-slate-400">Sesi</span></span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Sakit</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block"><?= $sakit ?> <span class="text-xs font-normal text-slate-400">Sesi</span></span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">medical_services</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Alpa</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block"><?= $alpa ?> <span class="text-xs font-normal text-slate-400">Sesi</span></span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                    <h2 class="text-sm font-bold text-slate-900">Log Presensi Bimbingan Belajar</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                        <?= count($kehadiran_list) ?> Catatan Presensi
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/70">
                            <th class="py-3.5 px-6 w-12 text-center">NO</th>
                            <th class="py-3.5 px-6">TANGGAL PRESENSI</th>
                            <th class="py-3.5 px-6">MATA PELAJARAN</th>
                            <th class="py-3.5 px-6">PENGAJAR / TUTOR</th>
                            <th class="py-3.5 px-6">WAKTU SESI</th>
                            <th class="py-3.5 px-6 text-center">STATUS KEHADIRAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($kehadiran_list)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">fact_check</span>
                                    Belum ada catatan presensi kehadiran yang tercatat untuk akun Anda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kehadiran_list as $idx => $k): 
                                $st = $k['status_kehadiran'];
                                if ($st === 'Hadir') {
                                    $badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $dot = 'bg-emerald-500';
                                } elseif ($st === 'Izin') {
                                    $badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                    $dot = 'bg-blue-500';
                                } elseif ($st === 'Sakit') {
                                    $badge = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $dot = 'bg-amber-500';
                                } else {
                                    $badge = 'bg-red-50 text-red-700 border-red-200';
                                    $dot = 'bg-red-500';
                                }
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-medium text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                                            <span class="font-bold text-slate-900"><?= format_tanggal_indo($k['tanggal_kehadiran']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            <?= htmlspecialchars($k['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-800">
                                        <?= htmlspecialchars($k['nama_guru']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600">
                                        <?= htmlspecialchars($k['hari']) ?>, <?= substr($k['jam_mulai'],0,5) ?> - <?= substr($k['jam_selesai'],0,5) ?> WIB
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border <?= $badge ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $dot ?>"></span>
                                            <span><?= htmlspecialchars($st) ?></span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-white flex items-center justify-between border-t border-slate-100 text-xs text-slate-500">
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($kehadiran_list) ?></span> riwayat presensi</span>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold flex items-center justify-center">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
