<?php
// guru/latihan.php - Kelola Latihan Soal Pengajar
require_once __DIR__ . '/../config/database.php';
check_access(['Guru']);

$page_title = "Latihan Soal";
$current_page = "latihan";

$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Tambah / Edit / Hapus Latihan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit') {
        $id_latihan = (int)($_POST['id_latihan'] ?? 0);
        $id_mapel = (int)($_POST['id_mapel'] ?? 0);
        $judul = db_escape(trim($_POST['judul_latihan'] ?? ''));
        $deskripsi = db_escape(trim($_POST['deskripsi'] ?? ''));
        $tautan = db_escape(trim($_POST['tautan_latihan'] ?? ''));
        $tanggal_dibuat = db_escape($_POST['tanggal_dibuat'] ?? date('Y-m-d H:i:s'));

        if (!empty($judul) && $id_mapel > 0) {
            if ($_POST['action'] === 'tambah') {
                $sql = "INSERT INTO latihan_soal (id_guru, id_mapel, judul_latihan, tautan_latihan, tanggal_dibuat) 
                        VALUES ($id_guru, $id_mapel, '$judul', '$tautan', '$tanggal_dibuat')";
                if (db_query($sql)) {
                    $alert_msg = "Latihan soal berhasil ditambahkan.";
                } else {
                    $alert_msg = "Gagal menambahkan latihan soal.";
                    $alert_type = "danger";
                }
            } else {
                $sql = "UPDATE latihan_soal SET id_mapel = $id_mapel, judul_latihan = '$judul', 
                               tautan_latihan = '$tautan' 
                        WHERE id_latihan = $id_latihan AND id_guru = $id_guru";
                if (db_query($sql)) {
                    $alert_msg = "Latihan soal berhasil diperbarui.";
                } else {
                    $alert_msg = "Gagal memperbarui latihan soal.";
                    $alert_type = "danger";
                }
            }
        } else {
            $alert_msg = "Judul latihan dan mata pelajaran wajib diisi.";
            $alert_type = "danger";
        }
    } elseif ($_POST['action'] === 'hapus') {
        $id_latihan = (int)($_POST['id_latihan'] ?? 0);
        db_query("DELETE FROM hasil_latihan WHERE id_latihan = $id_latihan");
        db_query("DELETE FROM latihan_soal WHERE id_latihan = $id_latihan AND id_guru = $id_guru");
        $alert_msg = "Latihan soal berhasil dihapus.";
    }
}

// Search & Filter
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_mapel = (int)($_GET['mapel'] ?? 0);

$where_clauses = ["l.id_guru = $id_guru"];
if (!empty($search)) {
    $where_clauses[] = "(l.judul_latihan LIKE '%$search%' OR m.nama_mapel LIKE '%$search%')";
}
if ($filter_mapel > 0) {
    $where_clauses[] = "l.id_mapel = $filter_mapel";
}
$where_sql = implode(' AND ', $where_clauses);

$latihan_list = db_fetch_all("SELECT l.*, m.nama_mapel, g.nama_guru,
                              (SELECT COUNT(*) FROM hasil_latihan h WHERE h.id_latihan = l.id_latihan) as total_submit 
                              FROM latihan_soal l 
                              JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
                              LEFT JOIN guru g ON l.id_guru = g.id_guru 
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
                    <span>Riwayat Nilai Siswa</span>
                </a>
                <button onclick="openModalTambah()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#19376D] hover:bg-blue-800 text-white text-xs font-semibold shadow-xs transition-all" type="button">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Buat Latihan Baru</span>
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
                            <th class="py-3.5 px-6" scope="col">NAMA PENGAJAR</th>
                            <th class="py-3.5 px-6 text-center" scope="col">TOTAL SUBMIT</th>
                            <th class="py-3.5 px-6 text-center" scope="col">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($latihan_list)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">quiz</span>
                                    Belum ada latihan soal yang dibuat. Klik tombol <strong>+ Buat Latihan Baru</strong> untuk menambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($latihan_list as $idx => $lat): ?>
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
                                        <?= htmlspecialchars($lat['nama_guru'] ?? $user['nama_lengkap']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <a href="nilai.php?id_latihan=<?= $lat['id_latihan'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                            <span><?= (int)$lat['total_submit'] ?> Siswa</span>
                                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <?php if (!empty($lat['tautan_latihan'])): ?>
                                                <a href="<?= htmlspecialchars($lat['tautan_latihan']) ?>" target="_blank" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition" title="Buka Tautan Latihan">
                                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                                </a>
                                            <?php endif; ?>
                                            <button onclick='openModalEdit(<?= json_encode($lat) ?>)' class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition" title="Edit Latihan">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                            <form method="POST" action="latihan.php" onsubmit="return confirm('Hapus latihan soal ini beserta seluruh nilai siswa yang terkait?');" class="inline">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_latihan" value="<?= $lat['id_latihan'] ?>">
                                                <button type="submit" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-red-600 hover:bg-slate-50 transition" title="Hapus Latihan">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </form>
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

<!-- Modal Tambah / Edit Latihan -->
<div id="modalLatihan" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 id="modalLatihanTitle" class="text-base font-bold text-slate-900">Buat Latihan Soal Baru</h3>
            <button type="button" onclick="closeModalLatihan()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="latihan.php" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="tambah">
            <input type="hidden" name="id_latihan" id="formIdLatihan" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                <select name="id_mapel" id="formIdMapel" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($mapel_options as $mp): ?>
                        <option value="<?= $mp['id_mapel'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Latihan <span class="text-red-500">*</span></label>
                <input type="text" name="judul_latihan" id="formJudul" required placeholder="Contoh: Kuis Evaluasi Limit & Turunan" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tautan Soal / Quizizz / Form</label>
                <input type="text" name="tautan_latihan" id="formTautan" placeholder="https://quizizz.com/... atau https://forms.google.com/..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalLatihan()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Simpan Latihan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalLatihanTitle').textContent = 'Buat Latihan Soal Baru';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('formIdLatihan').value = '';
    document.getElementById('formIdMapel').value = '';
    document.getElementById('formJudul').value = '';
    document.getElementById('formTautan').value = '';
    document.getElementById('modalLatihan').classList.remove('hidden');
}

function openModalEdit(lat) {
    document.getElementById('modalLatihanTitle').textContent = 'Edit Latihan Soal';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formIdLatihan').value = lat.id_latihan;
    document.getElementById('formIdMapel').value = lat.id_mapel;
    document.getElementById('formJudul').value = lat.judul_latihan;
    document.getElementById('formTautan').value = lat.tautan_latihan || '';
    document.getElementById('modalLatihan').classList.remove('hidden');
}

function closeModalLatihan() {
    document.getElementById('modalLatihan').classList.add('hidden');
}
</script>
