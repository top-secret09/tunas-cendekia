<?php
// admin/mapel.php - Kelola Data Mata Pelajaran
require_once __DIR__ . '/../config/database.php';
check_access(['Admin']);

$page_title = "Mata Pelajaran";
$current_page = "mapel";

$msg = '';
$err = '';

// Tambah Mapel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama = db_escape($_POST['nama_mapel'] ?? '');
    $deskripsi = db_escape($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        $err = "Nama mata pelajaran wajib diisi.";
    } else {
        $q = db_query("INSERT INTO mata_pelajaran (nama_mapel, deskripsi) VALUES ('$nama', '$deskripsi')");
        if ($q) {
            $msg = "Mata pelajaran baru berhasil ditambahkan.";
        } else {
            $err = "Gagal menambahkan mata pelajaran: " . $conn->error;
        }
    }
}

// Edit Mapel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_mapel = (int)($_POST['id_mapel'] ?? 0);
    $nama = db_escape($_POST['nama_mapel'] ?? '');
    $deskripsi = db_escape($_POST['deskripsi'] ?? '');

    $q = db_query("UPDATE mata_pelajaran SET nama_mapel = '$nama', deskripsi = '$deskripsi' WHERE id_mapel = $id_mapel");
    if ($q) {
        $msg = "Mata pelajaran berhasil diperbarui.";
    } else {
        $err = "Gagal memperbarui mata pelajaran: " . $conn->error;
    }
}

// Hapus Mapel
if (isset($_GET['hapus'])) {
    $id_mapel = (int)$_GET['hapus'];
    $q = db_query("DELETE FROM mata_pelajaran WHERE id_mapel = $id_mapel");
    if ($q) {
        $msg = "Mata pelajaran berhasil dihapus.";
    } else {
        $err = "Gagal menghapus mata pelajaran (kemungkinan masih dipakai di jadwal): " . $conn->error;
    }
}

$search = db_escape($_GET['q'] ?? '');
$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND (nama_mapel LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

$mapel_list = db_fetch_all("SELECT * FROM mata_pelajaran $where ORDER BY id_mapel ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 tracking-tight">Mata Pelajaran</h1>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span>&rsaquo;</span>
                    <span class="text-slate-700 font-semibold">Mata Pelajaran</span>
                </div>
            </div>
            <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#143d73] hover:bg-[#0f2e57] text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Tambah Mata Pelajaran</span>
            </button>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
                <span><?= htmlspecialchars($msg) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="p-3.5 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600 text-lg">error</span>
                <span><?= htmlspecialchars($err) ?></span>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <form method="GET" action="mapel.php" class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="relative flex-1">
                <span class="material-symbols-outlined text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-lg">search</span>
                <input class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:ring-1 focus:ring-blue-500 transition" name="q" placeholder="Cari nama mata pelajaran..." type="text" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <button class="px-5 py-2 rounded-lg bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer" type="submit">
                Cari
            </button>
            <?php if (!empty($search)): ?>
                <a href="mapel.php" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-medium text-center">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-600 font-semibold border-b border-slate-200/80 bg-slate-50/70">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Mata Pelajaran</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($mapel_list)): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Tidak ada data mata pelajaran.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mapel_list as $idx => $m): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $idx + 1 ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($m['nama_mapel']) ?></td>
                                    <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($m['deskripsi'] ?? '-') ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center justify-center gap-2">
                                            <button type="button" onclick='openEditModal(<?= json_encode($m) ?>)' class="p-1 rounded text-blue-600 hover:bg-blue-50 transition" title="Edit Mapel">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <a href="mapel.php?hapus=<?= $m['id_mapel'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?');" class="p-1 rounded text-rose-500 hover:bg-rose-50 transition" title="Hapus Mapel">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Mapel -->
    <div id="modal-tambah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-md w-full p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-display font-bold text-base text-slate-900">Tambah Mata Pelajaran</h3>
                <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="mapel.php" method="POST" class="space-y-3.5">
                <input type="hidden" name="action" value="tambah">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Mata Pelajaran *</label>
                    <input type="text" name="nama_mapel" placeholder="Contoh: Matematika" required class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi mata pelajaran..." class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none resize-none"></textarea>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 border rounded-lg text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Mapel -->
    <div id="modal-edit" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-md w-full p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-display font-bold text-base text-slate-900">Edit Mata Pelajaran</h3>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="mapel.php" method="POST" class="space-y-3.5">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_mapel" id="edit_id_mapel">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Mata Pelajaran *</label>
                    <input type="text" name="nama_mapel" id="edit_nama_mapel" required class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none resize-none"></textarea>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border rounded-lg text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold rounded-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

<script>
const modalAdd = document.getElementById('modal-tambah');
const modalEdit = document.getElementById('modal-edit');

function openAddModal() {
    modalAdd.classList.remove('hidden');
    requestAnimationFrame(() => modalAdd.classList.remove('opacity-0'));
}
function closeAddModal() {
    modalAdd.classList.add('opacity-0');
    setTimeout(() => modalAdd.classList.add('hidden'), 200);
}

function openEditModal(data) {
    document.getElementById('edit_id_mapel').value = data.id_mapel;
    document.getElementById('edit_nama_mapel').value = data.nama_mapel;
    document.getElementById('edit_deskripsi').value = data.deskripsi || '';

    modalEdit.classList.remove('hidden');
    requestAnimationFrame(() => modalEdit.classList.remove('opacity-0'));
}
function closeEditModal() {
    modalEdit.classList.add('opacity-0');
    setTimeout(() => modalEdit.classList.add('hidden'), 200);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
