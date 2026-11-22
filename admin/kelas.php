<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Data Kelas";
$current_page = "kelas";

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $nama_kelas = trim($_POST['nama_kelas'] ?? '');
        $tingkat = trim($_POST['tingkat'] ?? 'SD');
        $biaya = (float)($_POST['biaya'] ?? 150000);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status_kelas = trim($_POST['status_kelas'] ?? 'Aktif');

        if (!empty($nama_kelas)) {
            $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, tingkat, biaya, deskripsi, status_kelas) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama_kelas, $tingkat, $biaya, $deskripsi, $status_kelas]);
            $msg = "Kelas berhasil ditambahkan.";
        }
    } elseif ($_POST['action'] === 'edit') {
        $id_kelas = (int)($_POST['id_kelas'] ?? 0);
        $nama_kelas = trim($_POST['nama_kelas'] ?? '');
        $tingkat = trim($_POST['tingkat'] ?? 'SD');
        $biaya = (float)($_POST['biaya'] ?? 150000);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status_kelas = trim($_POST['status_kelas'] ?? 'Aktif');

        if ($id_kelas && !empty($nama_kelas)) {
            $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas = ?, tingkat = ?, biaya = ?, deskripsi = ?, status_kelas = ? WHERE id_kelas = ?");
            $stmt->execute([$nama_kelas, $tingkat, $biaya, $deskripsi, $status_kelas, $id_kelas]);
            $msg = "Data kelas berhasil diperbarui.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id_kelas = (int)($_POST['id_kelas'] ?? 0);
        if ($id_kelas) {
            $pdo->prepare("DELETE FROM pendaftaran WHERE id_kelas = ?")->execute([$id_kelas]);
            $pdo->prepare("DELETE FROM jadwal WHERE id_kelas = ?")->execute([$id_kelas]);
            $pdo->prepare("DELETE FROM kelas WHERE id_kelas = ?")->execute([$id_kelas]);
            $msg = "Data kelas berhasil dihapus.";
        }
    }
}

$search = trim($_GET['q'] ?? '');
$filter_tingkat = trim($_GET['tingkat'] ?? '');

$sql = "
    SELECT k.*, 
           (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_kelas = k.id_kelas) as total_siswa,
           (SELECT GROUP_CONCAT(DISTINCT m.nama_mapel SEPARATOR ', ') FROM jadwal j JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel WHERE j.id_kelas = k.id_kelas) as mapel_list,
           (SELECT GROUP_CONCAT(DISTINCT g.nama_guru SEPARATOR ', ') FROM jadwal j JOIN guru g ON j.id_guru = g.id_guru WHERE j.id_kelas = k.id_kelas) as guru_list
    FROM kelas k 
    WHERE 1=1
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (k.nama_kelas LIKE ? OR k.tingkat LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filter_tingkat) && $filter_tingkat !== 'all') {
    $sql .= " AND k.tingkat = ?";
    $params[] = $filter_tingkat;
}
$sql .= " ORDER BY k.id_kelas ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_kelas = $stmt->fetchAll();

$total_kelas = count($daftar_kelas);
$kelas_aktif = 0;
foreach ($daftar_kelas as $k) {
    if ($k['status_kelas'] === 'Aktif') $kelas_aktif++;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Data Kelas -->
<main class="flex-1 p-8 overflow-y-auto space-y-6" data-purpose="class-data-content">
  <!-- Top Title & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Kelas</h1>
      <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-500">
        <a class="hover:text-blue-600 transition-colors" href="index.php">Dashboard</a>
        <span>›</span>
        <span class="text-slate-700 font-medium">Data Kelas</span>
      </nav>
    </div>
    <div class="flex items-center gap-3">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#143d73] text-white font-semibold text-xs hover:bg-[#0f2e57] shadow-sm transition-all" onclick="openAddModal()" type="button">
        <span class="material-symbols-outlined text-base">add</span>
        <span>Tambah Kelas</span>
      </button>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-lg flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-rose-600">error</span>
      <span><?= htmlspecialchars($err) ?></span>
    </div>
  <?php endif; ?>

  <!-- KPI Cards Persis Stitch -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
    <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block mb-1">Total Kelas</span>
        <div class="flex items-baseline gap-2">
          <span class="text-2xl font-bold text-slate-900"><?= $total_kelas ?></span>
          <span class="text-xs text-slate-500 font-medium">Kelas</span>
        </div>
        <p class="text-xs text-slate-400 mt-1">Semua Jenjang Terdaftar</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
        <span class="material-symbols-outlined text-2xl">meeting_room</span>
      </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block mb-1">Kelas Aktif</span>
        <div class="flex items-baseline gap-2">
          <span class="text-2xl font-bold text-slate-900"><?= $kelas_aktif ?></span>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600">Berjalan</span>
        </div>
        <p class="text-xs text-slate-400 mt-1">Status Operasional Aktif</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
        <span class="material-symbols-outlined text-2xl">check_circle</span>
      </div>
    </div>
  </div>

  <!-- Search & Filters Persis Stitch -->
  <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3.5">
    <form method="GET" action="kelas.php" class="relative flex-1">
      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
        <span class="material-symbols-outlined text-lg">search</span>
      </div>
      <input class="w-full pl-10 pr-4 py-2 bg-slate-50 text-slate-800 placeholder:text-slate-400 text-xs rounded-lg outline-none border border-slate-200/80 focus:bg-white focus:ring-1 focus:ring-blue-500 transition-all" name="q" placeholder="Cari nama kelas atau jenjang..." type="text" value="<?= htmlspecialchars($search) ?>">
    </form>
    <form method="GET" action="kelas.php" class="flex flex-wrap items-center gap-2.5">
      <div class="relative min-w-[140px]">
        <select class="w-full appearance-none pl-3.5 pr-8 py-2 bg-slate-50 text-slate-700 text-xs rounded-lg border border-slate-200/80 outline-none focus:bg-white focus:ring-1 focus:ring-blue-500 cursor-pointer" name="tingkat" onchange="this.form.submit()">
          <option value="all">Semua Jenjang</option>
          <option value="PAUD/TK" <?= $filter_tingkat === 'PAUD/TK' ? 'selected' : '' ?>>PAUD / TK</option>
          <option value="SD" <?= $filter_tingkat === 'SD' ? 'selected' : '' ?>>SD</option>
          <option value="SMP" <?= $filter_tingkat === 'SMP' ? 'selected' : '' ?>>SMP</option>
          <option value="SMA" <?= $filter_tingkat === 'SMA' ? 'selected' : '' ?>>SMA</option>
        </select>
        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-base">expand_more</span>
      </div>
    </form>
  </div>

  <!-- Table Persis Stitch -->
  <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs" id="classTable">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
            <th class="py-3.5 px-4 text-center w-12">NO</th>
            <th class="py-3.5 px-4">NAMA KELAS</th>
            <th class="py-3.5 px-4">JENJANG</th>
            <th class="py-3.5 px-4">BIAYA (SPP)</th>
            <th class="py-3.5 px-4">DESKRIPSI</th>
            <th class="py-3.5 px-4 text-center">STATUS</th>
            <th class="py-3.5 px-4 text-center w-24">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($daftar_kelas)): ?>
            <tr><td colspan="7" class="py-6 text-center text-slate-400">Tidak ada data kelas.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar_kelas as $idx => $k): ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3.5 px-4 text-center font-medium text-slate-500"><?= $idx + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($k['nama_kelas']) ?></td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100"><?= htmlspecialchars($k['tingkat']) ?></span></td>
                <td class="py-3.5 px-4 font-semibold text-slate-800">Rp <?= number_format($k['biaya'], 0, ',', '.') ?></td>
                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate"><?= htmlspecialchars($k['deskripsi'] ?? '-') ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?= $k['status_kelas'] === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' ?>">
                    <?= htmlspecialchars($k['status_kelas']) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center justify-center gap-2.5">
                    <button class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit" onclick='openEditModal(<?= json_encode($k) ?>)'>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </button>
                    <form method="POST" action="kelas.php" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_kelas" value="<?= $k['id_kelas'] ?>">
                      <button class="text-rose-500 hover:text-rose-700 transition-colors" title="Hapus" type="submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"></path></svg>
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
  </div>
</main>

<!-- Modal Tambah Kelas -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="addClassModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Tambah Kelas Baru</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeAddModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="kelas.php">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Kelas</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_kelas" placeholder="Contoh: Kelas 10 SMA" required type="text">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jenjang Tingkat</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="tingkat">
            <option value="PAUD/TK">PAUD/TK</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Biaya SPP (Rp)</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="biaya" placeholder="250000" type="number" required>
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 resize-none" name="deskripsi" rows="2" placeholder="Deskripsi bimbingan belajar..."></textarea>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeAddModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Kelas</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Kelas -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="editClassModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Edit Data Kelas</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeEditModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="kelas.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_kelas" id="edit_id_kelas">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Kelas</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_kelas" id="edit_nama_kelas" required type="text">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jenjang Tingkat</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="tingkat" id="edit_tingkat">
            <option value="PAUD/TK">PAUD/TK</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Biaya SPP (Rp)</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="biaya" id="edit_biaya" type="number" required>
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 resize-none" name="deskripsi" id="edit_deskripsi" rows="2"></textarea>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeEditModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  const m = document.getElementById('addClassModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddModal() {
  const m = document.getElementById('addClassModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
function openEditModal(k) {
  document.getElementById('edit_id_kelas').value = k.id_kelas;
  document.getElementById('edit_nama_kelas').value = k.nama_kelas;
  document.getElementById('edit_tingkat').value = k.tingkat;
  document.getElementById('edit_biaya').value = k.biaya;
  document.getElementById('edit_deskripsi').value = k.deskripsi || '';
  const m = document.getElementById('editClassModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeEditModal() {
  const m = document.getElementById('editClassModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
