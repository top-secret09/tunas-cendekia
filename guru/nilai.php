<?php
// guru/nilai.php - Rekap Hasil Latihan & Nilai Siswa
require_once __DIR__ . '/../config/database.php';
check_access(['Guru']);

$page_title = "Hasil Latihan";
$current_page = "nilai";

$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Input / Edit / Hapus Nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit') {
        $id_hasil = (int)($_POST['id_hasil'] ?? 0);
        $id_siswa = (int)($_POST['id_siswa'] ?? 0);
        $id_latihan = (int)($_POST['id_latihan'] ?? 0);
        $nilai = (float)($_POST['nilai'] ?? 0);
        $tanggal_pengerjaan = db_escape($_POST['tanggal_pengerjaan'] ?? date('Y-m-d'));

        if ($id_siswa > 0 && $id_latihan > 0) {
            if ($_POST['action'] === 'tambah') {
                $sql = "INSERT INTO hasil_latihan (id_siswa, id_latihan, nilai, tanggal_pengerjaan) 
                        VALUES ($id_siswa, $id_latihan, $nilai, '$tanggal_pengerjaan')";
                if (db_query($sql)) {
                    $alert_msg = "Nilai siswa berhasil disimpan.";
                } else {
                    $alert_msg = "Gagal menyimpan nilai.";
                    $alert_type = "danger";
                }
            } else {
                $sql = "UPDATE hasil_latihan SET id_siswa = $id_siswa, id_latihan = $id_latihan, 
                               nilai = $nilai, tanggal_pengerjaan = '$tanggal_pengerjaan' 
                        WHERE id_hasil = $id_hasil";
                if (db_query($sql)) {
                    $alert_msg = "Nilai siswa berhasil diperbarui.";
                } else {
                    $alert_msg = "Gagal memperbarui nilai.";
                    $alert_type = "danger";
                }
            }
        } else {
            $alert_msg = "Pilih siswa dan latihan soal terlebih dahulu.";
            $alert_type = "danger";
        }
    } elseif ($_POST['action'] === 'hapus') {
        $id_hasil = (int)($_POST['id_hasil'] ?? 0);
        db_query("DELETE FROM hasil_latihan WHERE id_hasil = $id_hasil");
        $alert_msg = "Data nilai latihan berhasil dihapus.";
    }
}

// Search & Filter
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_latihan = (int)($_GET['id_latihan'] ?? 0);
$filter_mapel = (int)($_GET['mapel'] ?? 0);

$where_clauses = ["l.id_guru = $id_guru"];
if (!empty($search)) {
    $where_clauses[] = "(s.nama_siswa LIKE '%$search%' OR s.nis LIKE '%$search%' OR l.judul_latihan LIKE '%$search%')";
}
if ($filter_latihan > 0) {
    $where_clauses[] = "h.id_latihan = $filter_latihan";
}
if ($filter_mapel > 0) {
    $where_clauses[] = "l.id_mapel = $filter_mapel";
}
$where_sql = implode(' AND ', $where_clauses);

$hasil_list = db_fetch_all("SELECT h.*, s.nama_siswa, s.nis, s.id_kelas, k.nama_kelas, k.tingkat, 
                                   l.judul_latihan, m.nama_mapel 
                            FROM hasil_latihan h 
                            JOIN siswa s ON h.id_siswa = s.id_siswa 
                            LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
                            JOIN latihan_soal l ON h.id_latihan = l.id_latihan 
                            JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
                            WHERE $where_sql 
                            ORDER BY h.id_hasil DESC");

// Stats calculation
$total_nilai = count($hasil_list);
$sum_nilai = 0;
$max_nilai = 0;
$remedial_count = 0;
foreach ($hasil_list as $row) {
    $val = (float)$row['nilai'];
    $sum_nilai += $val;
    if ($val > $max_nilai) $max_nilai = $val;
    if ($val < 75) $remedial_count++;
}
$avg_nilai = $total_nilai > 0 ? round($sum_nilai / $total_nilai, 1) : 0;

$daftar_siswa = db_fetch_all("SELECT s.id_siswa, s.nama_siswa, s.nis, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas ORDER BY s.nama_siswa ASC");
$daftar_latihan = db_fetch_all("SELECT l.id_latihan, l.judul_latihan, m.nama_mapel FROM latihan_soal l JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel WHERE l.id_guru = $id_guru ORDER BY l.id_latihan DESC");
$daftar_mapel = db_fetch_all("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");

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

        <!-- Title & Action Buttons -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-1">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight leading-tight">Hasil Latihan</h1>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span class="material-symbols-outlined text-[13px] text-slate-400">chevron_right</span>
                    <span class="text-slate-700 font-semibold">Hasil Latihan</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-xs font-medium flex items-center gap-1.5 shadow-xs transition">
                    <span class="material-symbols-outlined text-[18px] text-slate-500">download</span>
                    <span>Export Nilai</span>
                </button>
                <button type="button" onclick="openModalTambah()" class="bg-[#19376D] hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-xs font-medium flex items-center gap-1.5 shadow-xs transition">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Input Nilai Baru</span>
                </button>
            </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Total Nilai Masuk</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1.5"><?= $total_nilai ?> <span class="text-xs font-normal text-slate-500">Data</span></span>
                    <span class="text-[11px] text-emerald-700 font-medium mt-1 inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">trending_up</span>Tercatat di sistem
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">assignment_turned_in</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Rata-rata Nilai</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1.5"><?= $avg_nilai ?></span>
                    <span class="text-[11px] text-emerald-700 font-medium mt-1 inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">trending_up</span>Target kelulusan 75
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">analytics</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Nilai Tertinggi</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1.5"><?= $max_nilai ?></span>
                    <span class="text-[11px] text-blue-600 font-medium mt-1 inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">star</span>Peringkat teratas
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Butuh Remedial</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1.5"><?= $remedial_count ?> <span class="text-xs font-normal text-slate-500">Siswa</span></span>
                    <span class="text-[11px] text-amber-600 font-medium mt-1 inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">warning</span>Nilai &lt; 75
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">rule</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar Container -->
        <form method="GET" action="nilai.php" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                </span>
                <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-9 pr-4 py-2 bg-slate-50 text-slate-700 placeholder:text-slate-400 rounded-lg text-xs border border-slate-200 focus:outline-none focus:border-blue-600 focus:bg-white transition-all" placeholder="Cari nama siswa, NIS, atau judul latihan..." type="text">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <div class="relative flex-1 sm:flex-initial">
                    <select name="mapel" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs py-2 pl-3 pr-8 rounded-lg border border-slate-200 cursor-pointer focus:outline-none transition-all">
                        <option value="0">Semua Mata Pelajaran</option>
                        <?php foreach ($daftar_mapel as $mp): ?>
                            <option value="<?= $mp['id_mapel'] ?>" <?= ($filter_mapel == $mp['id_mapel']) ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="relative flex-1 sm:flex-initial">
                    <select name="id_latihan" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs py-2 pl-3 pr-8 rounded-lg border border-slate-200 cursor-pointer focus:outline-none transition-all">
                        <option value="0">Semua Latihan</option>
                        <?php foreach ($daftar_latihan as $dl): ?>
                            <option value="<?= $dl['id_latihan'] ?>" <?= ($filter_latihan == $dl['id_latihan']) ? 'selected' : '' ?>><?= htmlspecialchars($dl['judul_latihan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a href="nilai.php" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <!-- Main Score Table Card -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/70">
                            <th class="py-3.5 px-6 w-12 text-center">NO</th>
                            <th class="py-3.5 px-6">SISWA</th>
                            <th class="py-3.5 px-6">KELAS</th>
                            <th class="py-3.5 px-6">JUDUL LATIHAN &amp; MAPEL</th>
                            <th class="py-3.5 px-6 text-center">NILAI</th>
                            <th class="py-3.5 px-6">TANGGAL SUBMIT</th>
                            <th class="py-3.5 px-6 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($hasil_list)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">assignment_turned_in</span>
                                    Belum ada data nilai latihan siswa yang tercatat pada filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hasil_list as $idx => $h): 
                                $isPass = (float)$h['nilai'] >= 75;
                                $initial = strtoupper(substr($h['nama_siswa'], 0, 1));
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-medium text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-[#19376D] font-bold text-xs flex items-center justify-center shrink-0">
                                                <?= $initial ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($h['nama_siswa']) ?></span>
                                                <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($h['nis'] ?? '-') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-medium text-slate-800"><?= htmlspecialchars($h['nama_kelas'] ?? 'Reguler') ?></span>
                                        <?php if (!empty($h['tingkat'])): ?>
                                            <span class="block text-[11px] text-slate-400"><?= htmlspecialchars($h['tingkat']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-slate-900 text-xs"><?= htmlspecialchars($h['judul_latihan']) ?></span>
                                            <span class="text-[11px] text-blue-600 font-medium"><?= htmlspecialchars($h['nama_mapel']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-sm font-extrabold <?= $isPass ? 'text-emerald-600' : 'text-red-600' ?>">
                                                <?= number_format((float)$h['nilai'], 0) ?>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $isPass ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                                                <?= $isPass ? 'Lulus' : 'Remedial' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-xs text-slate-500"><?= format_tanggal_indo($h['tanggal_pengerjaan']) ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button onclick='openModalEdit(<?= json_encode($h) ?>)' class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition" title="Edit Nilai">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                            <form method="POST" action="nilai.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data nilai ini?');" class="inline">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_hasil" value="<?= $h['id_hasil'] ?>">
                                                <button type="submit" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-red-600 hover:bg-slate-50 transition" title="Hapus Nilai">
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
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($hasil_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($hasil_list) ?></span> data nilai</span>
                <div class="inline-flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold shadow-xs flex items-center justify-center" type="button">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<!-- Modal Input / Edit Nilai -->
<div id="modalNilai" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 id="modalNilaiTitle" class="text-base font-bold text-slate-900">Input Nilai Latihan Siswa</h3>
            <button type="button" onclick="closeModalNilai()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="nilai.php" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="tambah">
            <input type="hidden" name="id_hasil" id="formIdHasil" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Siswa <span class="text-red-500">*</span></label>
                <select name="id_siswa" id="formIdSiswa" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($daftar_siswa as $s): ?>
                        <option value="<?= $s['id_siswa'] ?>"><?= htmlspecialchars($s['nama_siswa']) ?> (<?= htmlspecialchars($s['nama_kelas'] ?? 'Reguler') ?> - NIS: <?= htmlspecialchars($s['nis'] ?? '-') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Latihan Soal <span class="text-red-500">*</span></label>
                <select name="id_latihan" id="formIdLatihan" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Latihan Soal --</option>
                    <?php foreach ($daftar_latihan as $l): ?>
                        <option value="<?= $l['id_latihan'] ?>"><?= htmlspecialchars($l['judul_latihan']) ?> (<?= htmlspecialchars($l['nama_mapel']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nilai (0 - 100) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="nilai" id="formNilai" required placeholder="85.5" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Submit <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pengerjaan" id="formTanggal" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalNilai()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Simpan Nilai</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalNilaiTitle').textContent = 'Input Nilai Latihan Siswa';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('formIdHasil').value = '';
    document.getElementById('formIdSiswa').value = '';
    document.getElementById('formIdLatihan').value = '<?= $filter_latihan > 0 ? $filter_latihan : '' ?>';
    document.getElementById('formNilai').value = '';
    document.getElementById('formTanggal').value = '<?= date('Y-m-d') ?>';
    document.getElementById('modalNilai').classList.remove('hidden');
}

function openModalEdit(h) {
    document.getElementById('modalNilaiTitle').textContent = 'Edit Nilai Latihan';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formIdHasil').value = h.id_hasil;
    document.getElementById('formIdSiswa').value = h.id_siswa;
    document.getElementById('formIdLatihan').value = h.id_latihan;
    document.getElementById('formNilai').value = h.nilai;
    document.getElementById('formTanggal').value = h.tanggal_pengerjaan || '<?= date('Y-m-d') ?>';
    document.getElementById('modalNilai').classList.remove('hidden');
}

function closeModalNilai() {
    document.getElementById('modalNilai').classList.add('hidden');
}
</script>
