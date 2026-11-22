<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Pendaftaran Siswa";
$current_page = "pendaftaran";

$msg = '';
$err = '';

// Handle Status Verifikasi (Terima / Tolak)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id_pendaftaran = (int)($_POST['id_pendaftaran'] ?? 0);
        $status = trim($_POST['status'] ?? 'Menunggu');
        if ($id_pendaftaran) {
            $stmt = $pdo->prepare("UPDATE pendaftaran SET status_pendaftaran = ? WHERE id_pendaftaran = ?");
            $stmt->execute([$status, $id_pendaftaran]);
            $msg = "Status pendaftaran berhasil diperbarui menjadi: $status.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id_pendaftaran = (int)($_POST['id_pendaftaran'] ?? 0);
        if ($id_pendaftaran) {
            $stmt = $pdo->prepare("DELETE FROM pendaftaran WHERE id_pendaftaran = ?");
            $stmt->execute([$id_pendaftaran]);
            $msg = "Data pendaftaran berhasil dihapus.";
        }
    }
}

$search = trim($_GET['q'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

$sql = "
    SELECT p.*, s.nama_siswa, s.jenis_Kelamin, s.no_telepon, k.nama_kelas, k.tingkat, k.biaya 
    FROM pendaftaran p 
    JOIN siswa s ON p.id_siswa = s.id_siswa 
    JOIN kelas k ON p.id_kelas = k.id_kelas 
    WHERE 1=1
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (s.nama_siswa LIKE ? OR s.no_telepon LIKE ? OR k.nama_kelas LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filter_status) && $filter_status !== 'all') {
    $sql .= " AND p.status_pendaftaran = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY p.id_pendaftaran DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_pendaftaran = $stmt->fetchAll();

// Counts for KPI
$total_pendaftaran = (int)$pdo->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn();
$menunggu_verif = (int)$pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_pendaftaran = 'Menunggu'")->fetchColumn();
$diterima = (int)$pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_pendaftaran = 'Diterima'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Pendaftaran Siswa -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto" data-purpose="pendaftaran-main-content">
  <!-- Top Header & Action Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pendaftaran Siswa Baru</h2>
      <p class="text-slate-500 text-xs mt-0.5">Dashboard &gt; Pendaftaran Siswa</p>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>

  <!-- BEGIN: Metric Summary Cards Persis Stitch -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" data-purpose="metric-cards-container">
    <!-- Card 1: Total Pendaftaran -->
    <div class="bg-white p-4 rounded-xl border border-blue-50/60 shadow-sm flex items-center gap-4">
      <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path clip-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" fill-rule="evenodd"></path>
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-medium text-slate-500">Total Pendaftaran</p>
        <h3 class="text-base font-bold text-slate-900 mt-0.5"><?= $total_pendaftaran ?> Siswa</h3>
      </div>
    </div>
    <!-- Card 2: Menunggu Verifikasi -->
    <div class="bg-white p-4 rounded-xl border border-blue-50/60 shadow-sm flex items-center gap-4">
      <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-medium text-slate-500">Menunggu Verifikasi</p>
        <h3 class="text-base font-bold text-slate-900 mt-0.5"><?= $menunggu_verif ?> Siswa</h3>
      </div>
    </div>
    <!-- Card 3: Diterima -->
    <div class="bg-white p-4 rounded-xl border border-blue-50/60 shadow-sm flex items-center gap-4">
      <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-medium text-slate-500">Diterima</p>
        <h3 class="text-base font-bold text-slate-900 mt-0.5"><?= $diterima ?> Siswa</h3>
      </div>
    </div>
  </div>

  <!-- Filter & Search Bar Persis Stitch -->
  <div class="flex flex-col sm:flex-row items-center gap-3">
    <form method="GET" action="pendaftaran.php" class="relative flex-1 w-full">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </span>
      <input class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#133e7c] shadow-sm" name="q" placeholder="Cari nama pendaftar atau kelas..." type="text" value="<?= htmlspecialchars($search) ?>">
    </form>
    <form method="GET" action="pendaftaran.php" class="w-full sm:w-48">
      <div class="relative">
        <select class="w-full appearance-none bg-white border border-slate-200 rounded-lg px-3.5 py-2 pr-8 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-[#133e7c] shadow-sm cursor-pointer" name="status" onchange="this.form.submit()">
          <option value="all">Semua Status</option>
          <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
          <option value="Diterima" <?= $filter_status === 'Diterima' ? 'selected' : '' ?>>Diterima</option>
          <option value="Ditolak" <?= $filter_status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </span>
      </div>
    </form>
  </div>

  <!-- Table Card Persis Stitch -->
  <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
            <th class="py-3 px-4 w-12 text-center">NO</th>
            <th class="py-3 px-4">REGISTRASI</th>
            <th class="py-3 px-4">NAMA PENDAFTAR</th>
            <th class="py-3 px-4">PILIHAN KELAS</th>
            <th class="py-3 px-4">NO. TELEPON</th>
            <th class="py-3 px-4">TANGGAL DAFTAR</th>
            <th class="py-3 px-4 text-center">STATUS</th>
            <th class="py-3 px-4 text-center w-36">AKSI VERIFIKASI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($daftar_pendaftaran)): ?>
            <tr><td colspan="8" class="py-6 text-center text-slate-400">Belum ada data pendaftaran siswa.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar_pendaftaran as $i => $p): 
                $st = $p['status_pendaftaran'];
                $stBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                if ($st === 'Diterima') $stBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                elseif ($st === 'Ditolak') $stBadge = 'bg-rose-50 text-rose-700 border-rose-200';
            ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3.5 px-4 text-center font-medium text-slate-500"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900">REG-<?= str_pad($p['id_pendaftaran'], 4, '0', STR_PAD_LEFT) ?></td>
                <td class="py-3.5 px-4 font-semibold text-slate-900"><?= htmlspecialchars($p['nama_siswa']) ?></td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100"><?= htmlspecialchars($p['nama_kelas']) ?> (<?= htmlspecialchars($p['tingkat']) ?>)</span></td>
                <td class="py-3.5 px-4 text-slate-600"><?= htmlspecialchars($p['no_telepon']) ?></td>
                <td class="py-3.5 px-4 text-slate-500"><?= date('d M Y', strtotime($p['tanggal_daftar'])) ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium border <?= $stBadge ?>">
                    <?= htmlspecialchars($st) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center justify-center gap-1.5">
                    <?php if ($st !== 'Diterima'): ?>
                      <form method="POST" action="pendaftaran.php" class="inline">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id_pendaftaran" value="<?= $p['id_pendaftaran'] ?>">
                        <input type="hidden" name="status" value="Diterima">
                        <button type="submit" class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-[11px] font-semibold" title="Terima Pendaftaran">
                          Terima
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($st !== 'Ditolak'): ?>
                      <form method="POST" action="pendaftaran.php" class="inline">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id_pendaftaran" value="<?= $p['id_pendaftaran'] ?>">
                        <input type="hidden" name="status" value="Ditolak">
                        <button type="submit" class="px-2 py-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-[11px] font-semibold" title="Tolak Pendaftaran">
                          Tolak
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
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
