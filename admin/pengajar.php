<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Data Pengajar";
$current_page = "pengajar";

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $nama = trim($_POST['nama_guru'] ?? '');
        $mapel = trim($_POST['bidang_mapel'] ?? '');
        $pendidikan = trim($_POST['pendidikan_terakhir'] ?? '');
        $hp = trim($_POST['no_telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $username = 'guru_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($nama, 0, 8))) . rand(10, 99);
        $password = 'guru123';
        if (!empty($nama)) {
            try {
                $pdo->beginTransaction();
                $stmtA = $pdo->prepare("INSERT INTO akun (username, password, peran) VALUES (?, ?, 'Guru')");
                $stmtA->execute([$username, $password]);
                $id_akun = $pdo->lastInsertId();

                $stmtG = $pdo->prepare("INSERT INTO guru (id_akun, nama_guru, alamat, pendidikan_terakhir, bidang_mapel, no_telepon) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtG->execute([$id_akun, $nama, $alamat, $pendidikan, $mapel, $hp]);

                $pdo->commit();
                $msg = "Pengajar berhasil ditambahkan. Username: $username, Password: $password";
            } catch (Exception $e) {
                $pdo->rollBack();
                $err = "Gagal menambah pengajar: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id_guru = (int)($_POST['id_guru'] ?? 0);
        $nama = trim($_POST['nama_guru'] ?? '');
        $mapel = trim($_POST['bidang_mapel'] ?? '');
        $pendidikan = trim($_POST['pendidikan_terakhir'] ?? '');
        $hp = trim($_POST['no_telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        if ($id_guru && !empty($nama)) {
            $stmt = $pdo->prepare("UPDATE guru SET nama_guru = ?, bidang_mapel = ?, pendidikan_terakhir = ?, no_telepon = ?, alamat = ? WHERE id_guru = ?");
            $stmt->execute([$nama, $mapel, $pendidikan, $hp, $alamat, $id_guru]);
            $msg = "Data pengajar berhasil diperbarui.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id_guru = (int)($_POST['id_guru'] ?? 0);
        if ($id_guru) {
            $stmt = $pdo->prepare("SELECT id_akun FROM guru WHERE id_guru = ?");
            $stmt->execute([$id_guru]);
            $akun_id = $stmt->fetchColumn();

            $pdo->prepare("DELETE FROM jadwal WHERE id_guru = ?")->execute([$id_guru]);
            $pdo->prepare("DELETE FROM latihan_soal WHERE id_guru = ?")->execute([$id_guru]);
            $pdo->prepare("DELETE FROM guru WHERE id_guru = ?")->execute([$id_guru]);
            if ($akun_id) {
                $pdo->prepare("DELETE FROM akun WHERE id_akun = ?")->execute([$akun_id]);
            }
            $msg = "Data pengajar berhasil dihapus.";
        }
    }
}

$search = trim($_GET['q'] ?? '');
$sql = "
    SELECT g.*, a.username 
    FROM guru g 
    LEFT JOIN akun a ON g.id_akun = a.id_akun 
    WHERE 1=1
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (g.nama_guru LIKE ? OR g.bidang_mapel LIKE ? OR g.no_telepon LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY g.id_guru ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_guru = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Data Pengajar -->
<main class="flex-1 p-8 overflow-y-auto space-y-6" data-purpose="teacher-data-content">
  <!-- Top Title & Action Button -->
  <section class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Data Pengajar</h2>
      <p class="text-slate-500 text-xs mt-1">Dashboard &gt; Data Pengajar</p>
    </div>
    <button class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-[#143d73] hover:bg-[#0f2e56] text-white text-xs font-semibold rounded-lg shadow-sm transition-all" onclick="openAddModal()" type="button">
      <span>+</span>
      <span>Tambah Pengajar</span>
    </button>
  </section>

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

  <!-- Content Card Persis Stitch -->
  <section class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-5">
    <!-- Search Toolbar -->
    <div class="flex items-center justify-between gap-4">
      <form method="GET" action="pengajar.php" class="relative w-full max-w-sm">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </span>
        <input class="block w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-xs placeholder-slate-400 text-slate-800 bg-[#f8fafc] focus:outline-none focus:bg-white focus:ring-1 focus:ring-blue-500 transition-colors" name="q" placeholder="Cari nama pengajar..." type="text" value="<?= htmlspecialchars($search) ?>">
      </form>
      <div class="text-xs text-slate-500">
        Total: <span class="font-bold text-slate-800"><?= count($daftar_guru) ?> Pengajar</span>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="text-slate-600 font-semibold border-b border-slate-100">
            <th class="py-3 px-3 w-12 font-semibold">No</th>
            <th class="py-3 px-3 font-semibold">ID Pengajar</th>
            <th class="py-3 px-3 font-semibold">Nama Pengajar</th>
            <th class="py-3 px-3 font-semibold">Mata Pelajaran</th>
            <th class="py-3 px-3 font-semibold">Pendidikan</th>
            <th class="py-3 px-3 font-semibold">No. HP</th>
            <th class="py-3 px-3 text-center font-semibold">Status</th>
            <th class="py-3 px-3 text-center w-24 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($daftar_guru)): ?>
            <tr><td colspan="8" class="py-6 text-center text-slate-400">Tidak ada data pengajar.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar_guru as $idx => $g): ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-3 text-slate-500"><?= $idx + 1 ?></td>
                <td class="py-3.5 px-3 font-medium text-slate-700">G<?= str_pad($g['id_guru'], 3, '0', STR_PAD_LEFT) ?></td>
                <td class="py-3.5 px-3 font-semibold text-slate-900"><?= htmlspecialchars($g['nama_guru']) ?></td>
                <td class="py-3.5 px-3 text-slate-600"><?= htmlspecialchars($g['bidang_mapel'] ?? 'Umum') ?></td>
                <td class="py-3.5 px-3 text-slate-600"><?= htmlspecialchars($g['pendidikan_terakhir'] ?? '-') ?></td>
                <td class="py-3.5 px-3 text-slate-600"><?= htmlspecialchars($g['no_telepon'] ?? '-') ?></td>
                <td class="py-3.5 px-3 text-center">
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#059669] border border-[#a7f3d0]">
                    Aktif
                  </span>
                </td>
                <td class="py-3.5 px-3 text-center">
                  <div class="flex items-center justify-center gap-2.5">
                    <button class="text-blue-500 hover:text-blue-700 transition-colors" title="Edit" onclick='openEditModal(<?= json_encode($g) ?>)'>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </button>
                    <form method="POST" action="pengajar.php" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengajar ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_guru" value="<?= $g['id_guru'] ?>">
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
  </section>
</main>

<!-- Modal Tambah Pengajar -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="addTeacherModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Tambah Pengajar Baru</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeAddModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="pengajar.php">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Lengkap Pengajar</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_guru" placeholder="Contoh: Dr. Rina Astuti" required type="text">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Bidang Mata Pelajaran</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="bidang_mapel" placeholder="Contoh: Matematika" required type="text">
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Pendidikan Terakhir</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="pendidikan_terakhir" placeholder="S1 Pendidikan..." type="text">
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">No. HP / WhatsApp</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="no_telepon" placeholder="08123456789" type="tel">
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Alamat Domisili</label>
        <textarea class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 resize-none" name="alamat" rows="2" placeholder="Alamat pengajar..."></textarea>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeAddModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Data</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Pengajar -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="editTeacherModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Edit Data Pengajar</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeEditModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="pengajar.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_guru" id="edit_id_guru">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Lengkap Pengajar</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_guru" id="edit_nama_guru" required type="text">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Bidang Mata Pelajaran</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="bidang_mapel" id="edit_bidang_mapel" required type="text">
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Pendidikan Terakhir</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="pendidikan_terakhir" id="edit_pendidikan_terakhir" type="text">
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">No. HP / WhatsApp</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="no_telepon" id="edit_no_telepon" type="tel">
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Alamat Domisili</label>
        <textarea class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 resize-none" name="alamat" id="edit_alamat" rows="2"></textarea>
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
  const m = document.getElementById('addTeacherModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddModal() {
  const m = document.getElementById('addTeacherModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
function openEditModal(g) {
  document.getElementById('edit_id_guru').value = g.id_guru;
  document.getElementById('edit_nama_guru').value = g.nama_guru;
  document.getElementById('edit_bidang_mapel').value = g.bidang_mapel || '';
  document.getElementById('edit_pendidikan_terakhir').value = g.pendidikan_terakhir || '';
  document.getElementById('edit_no_telepon').value = g.no_telepon || '';
  document.getElementById('edit_alamat').value = g.alamat || '';
  const m = document.getElementById('editTeacherModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeEditModal() {
  const m = document.getElementById('editTeacherModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
