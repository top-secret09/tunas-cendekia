<?php
// siswa/nilai.php - Riwayat Nilai Latihan Siswa
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Riwayat Nilai";
$current_page = "nilai";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

// Search & Filter
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_mapel = (int)($_GET['mapel'] ?? 0);

$where_clauses = ["h.id_siswa = $id_siswa"];
if (!empty($search)) {
    $where_clauses[] = "(l.judul_latihan LIKE '%$search%' OR m.nama_mapel LIKE '%$search%')";
}
if ($filter_mapel > 0) {
    $where_clauses[] = "l.id_mapel = $filter_mapel";
}
$where_sql = implode(' AND ', $where_clauses);

$nilai_list = db_fetch_all("SELECT h.*, l.judul_latihan, m.nama_mapel, g.nama_guru 
                            FROM hasil_latihan h 
                            JOIN latihan_soal l ON h.id_latihan = l.id_latihan 
                            JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
                            LEFT JOIN guru g ON l.id_guru = g.id_guru 
                            WHERE $where_sql 
                            ORDER BY h.id_hasil DESC");

// Stats calculation
$total_latihan = count($nilai_list);
$sum_nilai = 0;
$max_nilai = 0;
foreach ($nilai_list as $n) {
    $val = (float)$n['nilai'];
    $sum_nilai += $val;
    if ($val > $max_nilai) $max_nilai = $val;
}
$avg_nilai = $total_latihan > 0 ? round($sum_nilai / $total_latihan, 1) : 0;

$mapel_options = db_fetch_all("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 px-4 sm:px-8 py-6 w-full flex flex-col gap-6 overflow-y-auto">
        <!-- Page Title & Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-1">Riwayat Nilai</h1>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span class="text-slate-700 font-semibold">Riwayat Nilai</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-all shadow-xs" type="button">
                    <span class="material-symbols-outlined text-[18px] text-slate-600">download</span>
                    <span>Unduh Transkrip (PDF)</span>
                </button>
            </div>
        </div>

        <!-- Metric Stats Cards (3 Columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- Card 1: Rata-Rata Nilai -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between transition-all">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">RATA-RATA NILAI</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-1"><?= $avg_nilai ?></span>
                    <span class="text-xs font-medium text-emerald-600 mt-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">trending_up</span>
                        <span>Target kelulusan 75</span>
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[26px]">analytics</span>
                </div>
            </div>

            <!-- Card 2: Total Latihan -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between transition-all">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">TOTAL LATIHAN</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-1"><?= $total_latihan ?> <span class="text-base font-semibold text-slate-400">Latihan</span></span>
                    <span class="text-xs font-medium text-slate-400 mt-2">Dikerjakan &amp; dinilai</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[26px]">quiz</span>
                </div>
            </div>

            <!-- Card 3: Nilai Tertinggi -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between transition-all">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NILAI TERTINGGI</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-1"><?= $max_nilai ?> <span class="text-base font-semibold text-slate-400">/ 100</span></span>
                    <span class="text-xs font-medium text-teal-600 mt-2">Skor terbaik Anda</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[26px]">military_tech</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <form method="GET" action="nilai.php" class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="relative w-full md:w-96 flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px] pointer-events-none">search</span>
                <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-2 bg-slate-50 hover:bg-slate-100/70 focus:bg-white rounded-lg text-xs text-slate-800 placeholder:text-slate-400 border border-slate-200 focus:outline-none focus:border-blue-600 transition-all" placeholder="Cari riwayat latihan atau mata pelajaran..." type="text">
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <select name="mapel" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 pl-3.5 pr-8 py-2 rounded-lg text-xs font-medium text-slate-700 border border-slate-200 focus:outline-none cursor-pointer">
                        <option value="0">Semua Mata Pelajaran</option>
                        <?php foreach ($mapel_options as $mp): ?>
                            <option value="<?= $mp['id_mapel'] ?>" <?= ($filter_mapel == $mp['id_mapel']) ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a href="nilai.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer shrink-0">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <!-- Table Data Riwayat Nilai -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/70 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-3.5 px-6 text-center w-12">NO</th>
                            <th class="py-3.5 px-6 min-w-[220px]">JUDUL LATIHAN</th>
                            <th class="py-3.5 px-6 min-w-[160px]">MATA PELAJARAN</th>
                            <th class="py-3.5 px-6 min-w-[140px] text-center">NILAI</th>
                            <th class="py-3.5 px-6 min-w-[160px]">TANGGAL PENGERJAAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($nilai_list)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">military_tech</span>
                                    Belum ada catatan riwayat nilai latihan untuk akun Anda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($nilai_list as $idx => $n): 
                                $val = (float)$n['nilai'];
                                if ($val >= 90) {
                                    $label = "$val • Sangat Baik";
                                    $badgeClass = "bg-emerald-50 text-emerald-700";
                                    $dotClass = "bg-emerald-600";
                                } elseif ($val >= 80) {
                                    $label = "$val • Baik";
                                    $badgeClass = "bg-emerald-50 text-emerald-700";
                                    $dotClass = "bg-emerald-600";
                                } elseif ($val >= 75) {
                                    $label = "$val • Cukup";
                                    $badgeClass = "bg-amber-50 text-amber-700";
                                    $dotClass = "bg-amber-600";
                                } else {
                                    $label = "$val • Remedial";
                                    $badgeClass = "bg-red-50 text-red-700";
                                    $dotClass = "bg-red-600";
                                }
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-semibold text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                            <span><?= htmlspecialchars($n['judul_latihan']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 font-medium">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            <?= htmlspecialchars($n['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $badgeClass ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span>
                                            <?= $label ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 text-xs font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400">schedule</span>
                                            <span><?= format_tanggal_indo($n['tanggal_pengerjaan']) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-white flex items-center justify-between border-t border-slate-100 text-xs text-slate-500">
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($nilai_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($nilai_list) ?></span> riwayat nilai</span>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold flex items-center justify-center">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
