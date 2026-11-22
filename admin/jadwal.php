<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Jadwal Bimbingan";
$current_page = "jadwal";

$msg = '';
$err = '';

// Handle Tambah / Edit / Hapus Jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $id_kelas = (int)($_POST['id_kelas'] ?? 0);
        $id_mapel = (int)($_POST['id_mapel'] ?? 0);
        $id_guru = (int)($_POST['id_guru'] ?? 0);
        $hari = trim($_POST['hari'] ?? 'Senin');
        $jam_mulai = trim($_POST['jam_mulai'] ?? '08:00');
        $jam_selesai = trim($_POST['jam_selesai'] ?? '09:30');

        if ($id_kelas && $id_mapel && $id_guru) {
            $stmt = $pdo->prepare("INSERT INTO jadwal (id_kelas, id_mapel, id_guru, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_kelas, $id_mapel, $id_guru, $hari, $jam_mulai, $jam_selesai]);
            $msg = "Jadwal bimbingan berhasil ditambahkan.";
        } else {
            $err = "Mohon lengkapi Kelas, Mata Pelajaran, dan Pengajar.";
        }
    } elseif ($_POST['action'] === 'edit') {
        $id_jadwal = (int)($_POST['id_jadwal'] ?? 0);
        $id_kelas = (int)($_POST['id_kelas'] ?? 0);
        $id_mapel = (int)($_POST['id_mapel'] ?? 0);
        $id_guru = (int)($_POST['id_guru'] ?? 0);
        $hari = trim($_POST['hari'] ?? 'Senin');
        $jam_mulai = trim($_POST['jam_mulai'] ?? '08:00');
        $jam_selesai = trim($_POST['jam_selesai'] ?? '09:30');

        if ($id_jadwal && $id_kelas && $id_mapel && $id_guru) {
            $stmt = $pdo->prepare("UPDATE jadwal SET id_kelas = ?, id_mapel = ?, id_guru = ?, hari = ?, jam_mulai = ?, jam_selesai = ? WHERE id_jadwal = ?");
            $stmt->execute([$id_kelas, $id_mapel, $id_guru, $hari, $jam_mulai, $jam_selesai, $id_jadwal]);
            $msg = "Jadwal bimbingan berhasil diperbarui.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id_jadwal = (int)($_POST['id_jadwal'] ?? 0);
        if ($id_jadwal) {
            $pdo->prepare("DELETE FROM kehadiran WHERE id_jadwal = ?")->execute([$id_jadwal]);
            $pdo->prepare("DELETE FROM jadwal WHERE id_jadwal = ?")->execute([$id_jadwal]);
            $msg = "Jadwal bimbingan berhasil dihapus.";
        }
    }
}

// Filter Hari
$selected_hari = trim($_GET['hari'] ?? '');

$sql = "
    SELECT j.*, m.nama_mapel, k.nama_kelas, k.tingkat, g.nama_guru 
    FROM jadwal j 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    JOIN kelas k ON j.id_kelas = k.id_kelas 
    JOIN guru g ON j.id_guru = g.id_guru 
    WHERE 1=1
";
$params = [];
if (!empty($selected_hari) && $selected_hari !== 'all') {
    $sql .= " AND j.hari = ?";
    $params[] = $selected_hari;
}
$sql .= " ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_jadwal = $stmt->fetchAll();

// Dropdown options
$daftar_kelas = $pdo->query("SELECT * FROM kelas WHERE status_kelas = 'Aktif'")->fetchAll();
$daftar_mapel = $pdo->query("SELECT * FROM mata_pelajaran")->fetchAll();
$daftar_guru = $pdo->query("SELECT * FROM guru")->fetchAll();

$list_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Jadwal Bimbingan -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto" data-purpose="jadwal-main-content">
  <!-- Header Page: Title, Breadcrumb & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Jadwal Bimbingan</h2>
      <p class="text-slate-500 text-xs mt-0.5">Dashboard &gt; Jadwal Bimbingan</p>
    </div>
    <button class="inline-flex items-center justify-center gap-2 bg-[#143d73] hover:bg-[#0f2e57] text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-sm transition-all" onclick="openAddModal()" type="button">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      <span>Tambah Jadwal</span>
    </button>
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

  <!-- Selector Hari Bar Persis Stitch -->
  <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <h3 class="font-bold text-slate-900 text-sm">Pilih Hari Bimbingan</h3>
        <span class="text-xs text-slate-400 font-medium">(Tahun Ajaran Aktif)</span>
      </div>
      <a class="text-xs text-blue-600 hover:text-blue-800 font-semibold" href="jadwal.php">
        Tampilkan Semua Hari →
      </a>
    </div>

    <!-- 7 Days Grid Persis Stitch -->
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
      <?php foreach ($list_hari as $idx => $h): 
          $isActive = ($selected_hari === $h);
          $isMinggu = ($h === 'Minggu');
      ?>
        <a href="jadwal.php?hari=<?= urlencode($h) ?>" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl transition-all <?= $isActive ? 'bg-[#3b82f6] text-white shadow-md shadow-blue-500/20' : 'border border-slate-200/80 bg-slate-50/50 hover:bg-slate-100 text-slate-700' ?>">
          <span class="text-xs font-semibold uppercase tracking-wider <?= $isActive ? 'text-blue-100' : ($isMinggu ? 'text-red-500' : 'text-slate-500') ?>"><?= substr($h, 0, 3) ?></span>
          <span class="text-sm font-bold mt-1 <?= $isActive ? 'text-white' : ($isMinggu ? 'text-red-500' : 'text-slate-800') ?>"><?= $h ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Card Tabel Sesi Jadwal Persis Stitch -->
  <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h3 class="font-bold text-slate-900 text-sm">Sesi Jadwal Bimbingan <?= !empty($selected_hari) ? "Hari " . htmlspecialchars($selected_hari) : "Semua Hari" ?></h3>
        <p class="text-xs text-slate-500 mt-0.5">Daftar kelas bimbingan aktif dengan alokasi waktu dan pengajar</p>
      </div>
      <span class="text-xs text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200/60 font-medium">
        Total: <?= count($daftar_jadwal) ?> Sesi
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
            <th class="py-3 px-4 w-12 text-center">NO</th>
            <th class="py-3 px-4">HARI</th>
            <th class="py-3 px-4">TANGGAL</th>
            <th class="py-3 px-4">TAHUN</th>
            <th class="py-3 px-4">JAM SESI</th>
            <th class="py-3 px-4">MATA PELAJARAN</th>
            <th class="py-3 px-4">KELAS</th>
            <th class="py-3 px-4">PENGAJAR</th>
            <th class="py-3 px-4 text-center">STATUS</th>
            <th class="py-3 px-4 text-center w-24">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($daftar_jadwal)): ?>
            <tr><td colspan="10" class="py-6 text-center text-slate-400">Belum ada jadwal bimbingan.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar_jadwal as $i => $j): ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3.5 px-4 text-center font-medium text-slate-500"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($j['hari']) ?></td>
                <td class="py-3.5 px-4"><?= format_tanggal_indo($j['tanggal_belajar']) ?></td>
                <td class="py-3.5 px-4"><?= date('Y', strtotime($j['tanggal_belajar'])) ?></td>
                <td class="py-3.5 px-4 font-medium text-blue-700"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?> WIB</td>
                <td class="py-3.5 px-4 font-semibold text-slate-800"><?= htmlspecialchars($j['nama_mapel']) ?></td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100"><?= htmlspecialchars($j['nama_kelas']) ?> (<?= htmlspecialchars($j['tingkat']) ?>)</span></td>
                <td class="py-3.5 px-4 text-slate-800"><?= htmlspecialchars($j['nama_guru']) ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aktif
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center justify-center gap-2.5">
                    <button class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit" onclick='openEditModal(<?= json_encode($j) ?>)'>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </button>
                    <form method="POST" action="jadwal.php" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_jadwal" value="<?= $j['id_jadwal'] ?>">
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

<!-- Modal Tambah Jadwal -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="addJadwalModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Tambah Jadwal Bimbingan</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeAddModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="jadwal.php">
      <input type="hidden" name="action" value="add">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Kelas Bimbel</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_kelas" required>
            <?php foreach ($daftar_kelas as $k): ?>
              <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (<?= $k['tingkat'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Mata Pelajaran</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_mapel" required>
            <?php foreach ($daftar_mapel as $m): ?>
              <option value="<?= $m['id_mapel'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Pengajar / Tutor</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_guru" required>
          <?php foreach ($daftar_guru as $g): ?>
            <option value="<?= $g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?> (<?= htmlspecialchars($g['bidang_mapel']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Hari Bimbingan</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="hari">
          <?php foreach ($list_hari as $h): ?>
            <option value="<?= $h ?>"><?= $h ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jam Mulai</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jam_mulai" type="time" value="08:00" required>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jam Selesai</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jam_selesai" type="time" value="09:30" required>
        </div>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeAddModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Jadwal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Jadwal -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="editJadwalModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Edit Jadwal Bimbingan</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeEditModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="jadwal.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_jadwal" id="edit_id_jadwal">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Kelas Bimbel</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_kelas" id="edit_id_kelas" required>
            <?php foreach ($daftar_kelas as $k): ?>
              <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Mata Pelajaran</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_mapel" id="edit_id_mapel" required>
            <?php foreach ($daftar_mapel as $m): ?>
              <option value="<?= $m['id_mapel'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Pengajar / Tutor</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_guru" id="edit_id_guru" required>
          <?php foreach ($daftar_guru as $g): ?>
            <option value="<?= $g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Hari Bimbingan</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="hari" id="edit_hari">
          <?php foreach ($list_hari as $h): ?>
            <option value="<?= $h ?>"><?= $h ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jam Mulai</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jam_mulai" id="edit_jam_mulai" type="time" required>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Jam Selesai</label>
          <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jam_selesai" id="edit_jam_selesai" type="time" required>
        </div>
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
  const m = document.getElementById('addJadwalModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddModal() {
  const m = document.getElementById('addJadwalModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
function openEditModal(j) {
  document.getElementById('edit_id_jadwal').value = j.id_jadwal;
  document.getElementById('edit_id_kelas').value = j.id_kelas;
  document.getElementById('edit_id_mapel').value = j.id_mapel;
  document.getElementById('edit_id_guru').value = j.id_guru;
  document.getElementById('edit_hari').value = j.hari;
  document.getElementById('edit_jam_mulai').value = j.jam_mulai.substring(0, 5);
  document.getElementById('edit_jam_selesai').value = j.jam_selesai.substring(0, 5);
  const m = document.getElementById('editJadwalModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeEditModal() {
  const m = document.getElementById('editJadwalModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
