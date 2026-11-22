<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Data Siswa";
$current_page = "siswa";

// Handle Tambah / Edit Siswa
$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $nama = trim($_POST['nama_siswa'] ?? '');
        $jk = trim($_POST['jenis_kelamin'] ?? 'laki-laki');
        $hp = trim($_POST['no_telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $id_kelas = (int)($_POST['id_kelas'] ?? 1);
        $username = 'siswa_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($nama, 0, 8))) . rand(10, 99);
        $password = 'siswa123';
        if (!empty($nama)) {
            try {
                $pdo->beginTransaction();
                $stmtA = $pdo->prepare("INSERT INTO akun (username, password, peran) VALUES (?, ?, 'Siswa')");
                $stmtA->execute([$username, $password]);
                $id_akun = $pdo->lastInsertId();

                $stmtS = $pdo->prepare("INSERT INTO siswa (id_akun, nama_siswa, jenis_Kelamin, tanggal_lahir, alamat, no_telepon, tanggal_daftar) VALUES (?, ?, ?, CURDATE(), ?, ?, CURDATE())");
                $stmtS->execute([$id_akun, $nama, $jk, $alamat, $hp]);
                $id_siswa = $pdo->lastInsertId();

                $stmtP = $pdo->prepare("INSERT INTO pendaftaran (id_siswa, id_kelas, tanggal_daftar, status_pendaftaran) VALUES (?, ?, CURDATE(), 'Diterima')");
                $stmtP->execute([$id_siswa, $id_kelas]);

                $pdo->commit();
                $msg = "Siswa berhasil ditambahkan (Username: $username, Pass: $password)";
            } catch (Exception $e) {
                $pdo->rollBack();
                $err = "Gagal menambah siswa: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id_siswa = (int)($_POST['id_siswa'] ?? 0);
        $nama = trim($_POST['nama_siswa'] ?? '');
        $jk = trim($_POST['jenis_kelamin'] ?? 'laki-laki');
        $hp = trim($_POST['no_telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        if ($id_siswa && !empty($nama)) {
            $stmt = $pdo->prepare("UPDATE siswa SET nama_siswa = ?, jenis_Kelamin = ?, no_telepon = ?, alamat = ? WHERE id_siswa = ?");
            $stmt->execute([$nama, $jk, $hp, $alamat, $id_siswa]);
            $msg = "Data siswa berhasil diperbarui.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id_siswa = (int)($_POST['id_siswa'] ?? 0);
        if ($id_siswa) {
            $stmt = $pdo->prepare("SELECT id_akun FROM siswa WHERE id_siswa = ?");
            $stmt->execute([$id_siswa]);
            $akun_id = $stmt->fetchColumn();

            $pdo->prepare("DELETE FROM pembayaran WHERE id_pendaftaran IN (SELECT id_pendaftaran FROM pendaftaran WHERE id_siswa = ?)")->execute([$id_siswa]);
            $pdo->prepare("DELETE FROM pendaftaran WHERE id_siswa = ?")->execute([$id_siswa]);
            $pdo->prepare("DELETE FROM kehadiran WHERE id_siswa = ?")->execute([$id_siswa]);
            $pdo->prepare("DELETE FROM hasil_latihan WHERE id_siswa = ?")->execute([$id_siswa]);
            $pdo->prepare("DELETE FROM siswa WHERE id_siswa = ?")->execute([$id_siswa]);
            if ($akun_id) {
                $pdo->prepare("DELETE FROM akun WHERE id_akun = ?")->execute([$akun_id]);
            }
            $msg = "Data siswa berhasil dihapus.";
        }
    }
}

// Search & Filter
$search = trim($_GET['q'] ?? '');
$filter_tingkat = trim($_GET['tingkat'] ?? '');

$sql = "
    SELECT s.*, a.username, k.nama_kelas, k.tingkat, p.status_pendaftaran 
    FROM siswa s 
    LEFT JOIN akun a ON s.id_akun = a.id_akun 
    LEFT JOIN pendaftaran p ON s.id_siswa = p.id_siswa 
    LEFT JOIN kelas k ON p.id_kelas = k.id_kelas 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.nama_siswa LIKE ? OR s.no_telepon LIKE ? OR a.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_tingkat) && $filter_tingkat !== 'all') {
    $sql .= " AND k.tingkat = ?";
    $params[] = $filter_tingkat;
}

$sql .= " ORDER BY s.id_siswa DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_siswa = $stmt->fetchAll();

// List kelas untuk form tambah
$daftar_kelas = $pdo->query("SELECT * FROM kelas WHERE status_kelas = 'Aktif'")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: Main Content Area Persis Stitch Data Siswa -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto">
  <!-- Top Header: Title, Breadcrumb & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Data Siswa</h2>
      <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-medium">
        <a class="hover:text-blue-600 transition-colors" href="index.php">Dashboard</a>
        <span>›</span>
        <span class="text-slate-700 font-semibold">Data Siswa</span>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-medium text-xs shadow-xs transition-all active:scale-95" id="btnExportExcel" onclick="window.print()" type="button">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 9h1m0 3h1m-1 3h1" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        <span>Export / Cetak</span>
      </button>
      <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#143d73] hover:bg-[#0f2e57] text-white font-medium text-xs shadow-sm transition-all active:scale-95" id="btnTambahSiswa" onclick="openAddModal()" type="button">
        <span class="text-base font-bold leading-none">+</span>
        <span>Tambah Siswa</span>
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

  <!-- Stats KPI: Total Siswa -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs flex items-center gap-3.5">
      <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
      </div>
      <div>
        <p class="text-xs text-slate-500 font-medium leading-none mb-1">Total Siswa Terdaftar</p>
        <h3 class="text-lg font-bold text-slate-900 leading-tight"><?= count($daftar_siswa) ?> Siswa</h3>
      </div>
    </div>
  </div>

  <!-- Filter & Search Controls Persis Stitch -->
  <form method="GET" action="siswa.php" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
    <!-- Search Input -->
    <div class="relative flex-1">
      <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      <input class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 placeholder-slate-400 outline-none focus:ring-1 focus:ring-blue-500 shadow-xs" name="q" placeholder="Cari nama siswa atau no. telepon..." type="text" value="<?= htmlspecialchars($search) ?>">
    </div>
    <!-- Filter: Jenjang Kelas -->
    <div class="relative md:w-44">
      <select class="w-full appearance-none pl-3.5 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 outline-none focus:ring-1 focus:ring-blue-500 shadow-xs cursor-pointer" name="tingkat">
        <option value="all">Semua Jenjang</option>
        <option value="SD" <?= $filter_tingkat === 'SD' ? 'selected' : '' ?>>SD</option>
        <option value="SMP" <?= $filter_tingkat === 'SMP' ? 'selected' : '' ?>>SMP</option>
        <option value="SMA" <?= $filter_tingkat === 'SMA' ? 'selected' : '' ?>>SMA</option>
      </select>
      <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
    </div>
    <!-- Button: Cari -->
    <button class="px-5 py-2 rounded-lg bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold shadow-xs transition-colors" type="submit">
      Cari
    </button>
  </form>

  <!-- Data Table Card Persis Stitch -->
  <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs" id="studentsTable">
        <thead>
          <tr class="text-slate-700 font-semibold border-b border-slate-200/80 bg-slate-50/70">
            <th class="py-3 px-4 font-semibold text-slate-600">No</th>
            <th class="py-3 px-4 font-semibold text-slate-600">ID Siswa</th>
            <th class="py-3 px-4 font-semibold text-slate-600">Nama Siswa</th>
            <th class="py-3 px-4 font-semibold text-slate-600">Jenis Kelamin</th>
            <th class="py-3 px-4 font-semibold text-slate-600">Kelas Bimbel</th>
            <th class="py-3 px-4 font-semibold text-slate-600">Jenjang</th>
            <th class="py-3 px-4 font-semibold text-slate-600">No. HP</th>
            <th class="py-3 px-4 font-semibold text-slate-600">Status</th>
            <th class="py-3 px-4 font-semibold text-slate-600 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($daftar_siswa)): ?>
            <tr>
              <td colspan="9" class="py-6 text-center text-slate-400">Tidak ada data siswa ditemukan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($daftar_siswa as $idx => $s): ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4 text-slate-500"><?= $idx + 1 ?></td>
                <td class="py-3 px-4 font-medium text-slate-800">S<?= str_pad($s['id_siswa'], 3, '0', STR_PAD_LEFT) ?></td>
                <td class="py-3 px-4 font-medium text-slate-900"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                <td class="py-3 px-4 text-slate-600 capitalize"><?= htmlspecialchars($s['jenis_Kelamin']) ?></td>
                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($s['nama_kelas'] ?? 'Reguler') ?></td>
                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($s['tingkat'] ?? '-') ?></td>
                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($s['no_telepon']) ?></td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                    Aktif
                  </span>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="inline-flex items-center justify-center gap-2.5">
                    <button class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit" onclick='openEditModal(<?= json_encode($s) ?>)'>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </button>
                    <form method="POST" action="siswa.php" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_siswa" value="<?= $s['id_siswa'] ?>">
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

    <!-- Pagination Footer Info -->
    <div class="px-5 py-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
      <div>
        Menampilkan <span class="font-medium text-slate-700">1–<?= count($daftar_siswa) ?></span> dari <span class="font-medium text-slate-700"><?= count($daftar_siswa) ?></span> data
      </div>
      <div class="flex items-center gap-1.5">
        <button class="w-7 h-7 rounded bg-[#3b82f6] text-white font-medium flex items-center justify-center text-xs shadow-xs">1</button>
      </div>
    </div>
  </div>
</main>

<!-- Modal Tambah Siswa Persis Stitch -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="addStudentModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150 border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Tambah Siswa Baru</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeAddModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="siswa.php">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Lengkap Siswa</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_siswa" placeholder="Contoh: Andi Pratama" required type="text">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jenis Kelamin</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jenis_kelamin">
            <option value="laki-laki">Laki-laki</option>
            <option value="perempuan">Perempuan</option>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Kelas Bimbel</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_kelas">
            <?php foreach ($daftar_kelas as $k): ?>
              <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (<?= $k['tingkat'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">No. HP / WhatsApp</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="no_telepon" placeholder="08123456789" type="tel">
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Alamat Domisili</label>
        <textarea class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 resize-none" name="alamat" rows="2" placeholder="Alamat lengkap..."></textarea>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeAddModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Data</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Siswa -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="editStudentModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150 border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Edit Data Siswa</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeEditModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="siswa.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_siswa" id="edit_id_siswa">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nama Lengkap Siswa</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="nama_siswa" id="edit_nama_siswa" required type="text">
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Jenis Kelamin</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jenis_kelamin" id="edit_jenis_kelamin">
          <option value="laki-laki">Laki-laki</option>
          <option value="perempuan">Perempuan</option>
        </select>
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
  const m = document.getElementById('addStudentModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddModal() {
  const m = document.getElementById('addStudentModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
function openEditModal(data) {
  document.getElementById('edit_id_siswa').value = data.id_siswa;
  document.getElementById('edit_nama_siswa').value = data.nama_siswa;
  document.getElementById('edit_jenis_kelamin').value = data.jenis_Kelamin;
  document.getElementById('edit_no_telepon').value = data.no_telepon || '';
  document.getElementById('edit_alamat').value = data.alamat || '';
  const m = document.getElementById('editStudentModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeEditModal() {
  const m = document.getElementById('editStudentModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
