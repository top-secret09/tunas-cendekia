<?php
// guru/materi.php - Materi Pembelajaran
require_once __DIR__ . '/../config/database.php';
check_access(['Guru']);

$page_title = "Materi Pembelajaran";
$current_page = "materi";

$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Tambah / Edit / Hapus Materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit') {
        $id_materi = (int)($_POST['id_materi'] ?? 0);
        $id_mapel = (int)($_POST['id_mapel'] ?? 0);
        $judul_materi = db_escape(trim($_POST['judul_materi'] ?? ''));
        $bab = db_escape(trim($_POST['bab'] ?? ''));
        $deskripsi = db_escape(trim($_POST['deskripsi'] ?? ''));
        $kategori = db_escape($_POST['kategori'] ?? 'PDF');
        $file_url = db_escape(trim($_POST['file_url'] ?? '#'));
        $tanggal_dibagikan = db_escape($_POST['tanggal_dibagikan'] ?? date('Y-m-d'));

        if (!empty($judul_materi) && $id_mapel > 0) {
            if ($_POST['action'] === 'tambah') {
                $sql = "INSERT INTO materi (id_mapel, id_guru, judul_materi, bab, deskripsi, kategori, file_url, tanggal_dibagikan) 
                        VALUES ($id_mapel, $id_guru, '$judul_materi', '$bab', '$deskripsi', '$kategori', '$file_url', '$tanggal_dibagikan')";
                if (db_query($sql)) {
                    $alert_msg = "Materi pembelajaran berhasil diunggah.";
                } else {
                    $alert_msg = "Gagal mengunggah materi.";
                    $alert_type = "danger";
                }
            } else {
                $sql = "UPDATE materi SET id_mapel = $id_mapel, judul_materi = '$judul_materi', bab = '$bab', 
                               deskripsi = '$deskripsi', kategori = '$kategori', file_url = '$file_url', 
                               tanggal_dibagikan = '$tanggal_dibagikan' 
                        WHERE id_materi = $id_materi";
                if (db_query($sql)) {
                    $alert_msg = "Materi pembelajaran berhasil diperbarui.";
                } else {
                    $alert_msg = "Gagal memperbarui materi.";
                    $alert_type = "danger";
                }
            }
        } else {
            $alert_msg = "Harap isi judul materi dan pilih mata pelajaran.";
            $alert_type = "danger";
        }
    } elseif ($_POST['action'] === 'hapus') {
        $id_materi = (int)($_POST['id_materi'] ?? 0);
        db_query("DELETE FROM materi WHERE id_materi = $id_materi");
        $alert_msg = "Materi pembelajaran berhasil dihapus.";
    }
}

// Filter & Search
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_mapel = (int)($_GET['mapel'] ?? 0);
$filter_kat = db_escape($_GET['kategori'] ?? 'all');

$where_clauses = ["1=1"];
if (!empty($search)) {
    $where_clauses[] = "(m.judul_materi LIKE '%$search%' OR m.bab LIKE '%$search%' OR mp.nama_mapel LIKE '%$search%')";
}
if ($filter_mapel > 0) {
    $where_clauses[] = "m.id_mapel = $filter_mapel";
}
if ($filter_kat !== 'all' && !empty($filter_kat)) {
    $where_clauses[] = "m.kategori = '$filter_kat'";
}
$where_sql = implode(' AND ', $where_clauses);

$materi_list = db_fetch_all("SELECT m.*, mp.nama_mapel 
                             FROM materi m 
                             JOIN mata_pelajaran mp ON m.id_mapel = mp.id_mapel 
                             WHERE $where_sql 
                             ORDER BY m.tanggal_dibagikan DESC, m.id_materi DESC");

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

        <!-- Top Page Header & Primary Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Materi Pembelajaran</h1>
                <div class="flex items-center gap-2 text-xs text-slate-500 mt-1 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span class="text-blue-700 font-semibold">Materi Pembelajaran</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openModalTambah()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#19376D] hover:bg-blue-800 text-white text-xs font-medium shadow-sm transition-all" type="button">
                    <span class="material-symbols-outlined text-[18px]">upload</span>
                    <span>Upload Materi Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar Container -->
        <form method="GET" action="materi.php" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                </span>
                <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-9 pr-4 py-2 bg-slate-50 text-slate-700 placeholder:text-slate-400 rounded-lg text-xs border border-slate-200 focus:outline-none focus:border-blue-600 focus:bg-white transition-all" placeholder="Cari materi, bab, atau mata pelajaran..." type="text">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <div class="relative flex-1 sm:flex-initial">
                    <select name="mapel" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs py-2 pl-3 pr-8 rounded-lg border border-slate-200 cursor-pointer focus:outline-none transition-all">
                        <option value="0">Semua Mata Pelajaran</option>
                        <?php foreach ($mapel_options as $mp): ?>
                            <option value="<?= $mp['id_mapel'] ?>" <?= ($filter_mapel == $mp['id_mapel']) ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="relative flex-1 sm:flex-initial">
                    <select name="kategori" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs py-2 pl-3 pr-8 rounded-lg border border-slate-200 cursor-pointer focus:outline-none transition-all">
                        <option value="all">Semua Format / Kategori</option>
                        <option value="PDF" <?= ($filter_kat === 'PDF') ? 'selected' : '' ?>>PDF</option>
                        <option value="PPTX" <?= ($filter_kat === 'PPTX') ? 'selected' : '' ?>>PPTX</option>
                        <option value="Video" <?= ($filter_kat === 'Video') ? 'selected' : '' ?>>Video</option>
                        <option value="Dokumen Rangkuman" <?= ($filter_kat === 'Dokumen Rangkuman') ? 'selected' : '' ?>>Dokumen Rangkuman</option>
                    </select>
                </div>
                <a href="materi.php" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <!-- Main Materials Data Card / Table Container -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/70">
                            <th class="py-3.5 px-6 font-medium w-14 text-center">NO</th>
                            <th class="py-3.5 px-6 font-medium">JUDUL MATERI &amp; BAB</th>
                            <th class="py-3.5 px-6 font-medium w-48">MATA PELAJARAN</th>
                            <th class="py-3.5 px-6 font-medium w-40">TANGGAL DIBAGIKAN</th>
                            <th class="py-3.5 px-6 font-medium w-44 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($materi_list)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">menu_book</span>
                                    Belum ada materi pembelajaran yang sesuai dengan pencarian Anda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materi_list as $idx => $m): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-medium text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 text-xs leading-snug"><?= htmlspecialchars($m['judul_materi']) ?></span>
                                            <span class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($m['bab'] ?? 'Modul Teori & Latihan') ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            <?= htmlspecialchars($m['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-xs text-slate-500"><?= format_tanggal_indo($m['tanggal_dibagikan']) ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button onclick='openModalEdit(<?= json_encode($m) ?>)' class="inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition" title="Edit Materi">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                            <a href="<?= htmlspecialchars($m['file_url'] ?: '#') ?>" target="_blank" class="inline-flex items-center justify-center p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition" title="Unduh Materi">
                                                <span class="material-symbols-outlined text-[16px]">download</span>
                                            </a>
                                            <form method="POST" action="materi.php" onsubmit="return confirm('Hapus materi pembelajaran ini?');" class="inline">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_materi" value="<?= $m['id_materi'] ?>">
                                                <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-red-600 hover:bg-slate-50 transition" title="Hapus Materi">
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
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($materi_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($materi_list) ?></span> materi</span>
                <div class="inline-flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold shadow-xs flex items-center justify-center" type="button">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<!-- Modal Tambah / Edit Materi -->
<div id="modalMateri" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 id="modalMateriTitle" class="text-base font-bold text-slate-900">Upload Materi Pembelajaran</h3>
            <button type="button" onclick="closeModalMateri()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="materi.php" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="tambah">
            <input type="hidden" name="id_materi" id="formIdMateri" value="">

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
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Materi <span class="text-red-500">*</span></label>
                <input type="text" name="judul_materi" id="formJudul" required placeholder="Contoh: Kalkulus: Integral Tentu & Turunan Parsial" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Bab / Topik</label>
                    <input type="text" name="bab" id="formBab" placeholder="Contoh: Bab 3 • Teori mendalam" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori / Format</label>
                    <select name="kategori" id="formKategori" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                        <option value="PDF">PDF</option>
                        <option value="PPTX">PPTX</option>
                        <option value="Video">Video</option>
                        <option value="Dokumen Rangkuman">Dokumen Rangkuman</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tautan / File URL</label>
                <input type="text" name="file_url" id="formFileUrl" placeholder="https://..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Dibagikan</label>
                <input type="date" name="tanggal_dibagikan" id="formTanggal" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalMateri()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Simpan Materi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalMateriTitle').textContent = 'Upload Materi Pembelajaran';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('formIdMateri').value = '';
    document.getElementById('formIdMapel').value = '';
    document.getElementById('formJudul').value = '';
    document.getElementById('formBab').value = '';
    document.getElementById('formKategori').value = 'PDF';
    document.getElementById('formFileUrl').value = '';
    document.getElementById('formTanggal').value = '<?= date('Y-m-d') ?>';
    document.getElementById('modalMateri').classList.remove('hidden');
}

function openModalEdit(m) {
    document.getElementById('modalMateriTitle').textContent = 'Edit Materi Pembelajaran';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formIdMateri').value = m.id_materi;
    document.getElementById('formIdMapel').value = m.id_mapel;
    document.getElementById('formJudul').value = m.judul_materi;
    document.getElementById('formBab').value = m.bab || '';
    document.getElementById('formKategori').value = m.kategori || 'PDF';
    document.getElementById('formFileUrl').value = m.file_url || '';
    document.getElementById('formTanggal').value = m.tanggal_dibagikan || '<?= date('Y-m-d') ?>';
    document.getElementById('modalMateri').classList.remove('hidden');
}

function closeModalMateri() {
    document.getElementById('modalMateri').classList.add('hidden');
}
</script>
