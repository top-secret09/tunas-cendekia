<?php
// siswa/latihan.php - Latihan Soal Siswa
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Latihan Soal";
$current_page = "latihan";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Submit Nilai / Selesai Mengerjakan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kerjakan') {
    $id_latihan = (int)($_POST['id_latihan'] ?? 0);
    $nilai = (float)($_POST['nilai'] ?? 85.0);
    $tgl = date('Y-m-d');

    if ($id_latihan > 0) {
        $cek = db_fetch_one("SELECT id_hasil FROM hasil_latihan WHERE id_siswa = $id_siswa AND id_latihan = $id_latihan");
        if ($cek) {
            db_query("UPDATE hasil_latihan SET nilai = $nilai, tanggal_pengerjaan = '$tgl' WHERE id_hasil = " . $cek['id_hasil']);
        } else {
            db_query("INSERT INTO hasil_latihan (id_siswa, id_latihan, nilai, tanggal_pengerjaan) VALUES ($id_siswa, $id_latihan, $nilai, '$tgl')");
        }
        $alert_msg = "Latihan soal berhasil dikerjakan! Nilai Anda telah tercatat di riwayat nilai.";
    }
}

// Search & Filter
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_mapel = (int)($_GET['mapel'] ?? 0);

$where_clauses = ["1=1"];
if (!empty($search)) {
    $where_clauses[] = "(l.judul_latihan LIKE '%$search%' OR m.nama_mapel LIKE '%$search%' OR g.nama_guru LIKE '%$search%')";
}
if ($filter_mapel > 0) {
    $where_clauses[] = "l.id_mapel = $filter_mapel";
}
$where_sql = implode(' AND ', $where_clauses);

$latihan_list = db_fetch_all("SELECT l.*, m.nama_mapel, g.nama_guru, h.nilai, h.tanggal_pengerjaan 
                             FROM latihan_soal l 
                             JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
                             JOIN guru g ON l.id_guru = g.id_guru 
                             LEFT JOIN hasil_latihan h ON l.id_latihan = h.id_latihan AND h.id_siswa = $id_siswa 
                             WHERE $where_sql 
                             ORDER BY l.id_latihan DESC");

$mapel_options = db_fetch_all("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 px-4 sm:px-8 py-6 w-full flex flex-col gap-6 overflow-y-auto">
        <?php if (!empty($alert_msg)): ?>
            <div class="p-4 rounded-xl text-sm font-medium <?= $alert_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> flex items-center justify-between">
                <span><?= htmlspecialchars($alert_msg) ?></span>
                <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold px-2 py-1">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Latihan Soal</h1>
                <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span class="text-slate-700 font-semibold">Latihan Soal</span>
                </nav>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="nilai.php" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-blue-700 text-xs font-semibold shadow-xs hover:bg-slate-50 transition-all border border-slate-200">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    <span>Riwayat Pengerjaan</span>
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#19376D] hover:bg-blue-800 text-white text-xs font-semibold shadow-xs transition-all" type="button">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Unduh Panduan (PDF)</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar Card -->
        <form method="GET" action="latihan.php" class="bg-white rounded-xl p-4 shadow-xs border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="relative w-full md:w-96">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px] pointer-events-none">search</span>
                <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-2 bg-slate-50 text-slate-700 placeholder:text-slate-400 text-xs rounded-lg outline-none focus:bg-white focus:border-blue-600 transition-all border border-slate-200" placeholder="Cari judul latihan, mata pelajaran, atau topik..." type="text">
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial md:w-56">
                    <select name="mapel" onchange="this.form.submit()" class="w-full appearance-none pl-3 pr-8 py-2 bg-slate-50 text-slate-700 text-xs rounded-lg outline-none cursor-pointer border border-slate-200 focus:bg-white transition-all">
                        <option value="0">Semua Mata Pelajaran</option>
                        <?php foreach ($mapel_options as $mp): ?>
                            <option value="<?= $mp['id_mapel'] ?>" <?= ($filter_mapel == $mp['id_mapel']) ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a href="latihan.php" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <!-- Table Card Container -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-white">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-bold text-slate-900">Daftar Latihan Soal Tersedia</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                        <?= count($latihan_list) ?> Latihan Aktif
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/70">
                            <th class="py-3.5 px-6 w-12 text-center" scope="col">NO</th>
                            <th class="py-3.5 px-6" scope="col">JUDUL LATIHAN</th>
                            <th class="py-3.5 px-6" scope="col">MATA PELAJARAN</th>
                            <th class="py-3.5 px-6" scope="col">PENGAJAR</th>
                            <th class="py-3.5 px-6 text-center" scope="col">STATUS PENGERJAAN</th>
                            <th class="py-3.5 px-6 text-center" scope="col">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($latihan_list)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">quiz</span>
                                    Belum ada latihan soal yang tersedia untuk filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($latihan_list as $idx => $lat): 
                                $isDone = !is_null($lat['nilai']);
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-medium text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 text-xs leading-snug"><?= htmlspecialchars($lat['judul_latihan']) ?></span>
                                            <span class="text-[11px] text-slate-400 mt-0.5">Dibuat: <?= format_tanggal_indo($lat['tanggal_dibuat']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            <?= htmlspecialchars($lat['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-700">
                                        <?= htmlspecialchars($lat['nama_guru']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($isDone): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Nilai: <?= (float)$lat['nilai'] ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Belum Dikerjakan</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <?php if (!empty($lat['tautan_latihan'])): ?>
                                                <a href="<?= htmlspecialchars($lat['tautan_latihan']) ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition shadow-xs">
                                                    <span><?= $isDone ? 'Buka Soal' : 'Mulai' ?></span>
                                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                                </a>
                                            <?php else: ?>
                                                <form method="POST" action="latihan.php" class="inline">
                                                    <input type="hidden" name="action" value="kerjakan">
                                                    <input type="hidden" name="id_latihan" value="<?= $lat['id_latihan'] ?>">
                                                    <input type="hidden" name="nilai" value="85.0">
                                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-xs">
                                                        <span>Kerjakan</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 bg-white gap-3 border-t border-slate-100 text-xs text-slate-500">
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($latihan_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($latihan_list) ?></span> latihan</span>
                <div class="inline-flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold shadow-xs flex items-center justify-center" type="button">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
