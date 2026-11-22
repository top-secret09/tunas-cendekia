<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Pembayaran";
$current_page = "pembayaran";

$msg = '';
$err = '';

// Handle Tambah / Update Pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $id_pendaftaran = (int)($_POST['id_pendaftaran'] ?? 0);
        $jumlah = (float)($_POST['jumlah_bayar'] ?? 0);
        $metode = trim($_POST['metode_bayar'] ?? 'Transfer');
        $status = trim($_POST['status_pembayaran'] ?? 'Lunas');

        if ($id_pendaftaran && $jumlah > 0) {
            $stmt = $pdo->prepare("INSERT INTO pembayaran (id_pendaftaran, tanggal_bayar, jumlah_bayar, metode_bayar, status_pembayaran) VALUES (?, CURDATE(), ?, ?, ?)");
            $stmt->execute([$id_pendaftaran, $jumlah, $metode, $status]);
            // Insert notification for the admin (or relevant user)
            $adminId = $_SESSION['user']['id_akun'] ?? 1;
            $notifMsg = "Pembayaran baru (ID " . $pdo->lastInsertId() . ") berhasil dicatat.";
            $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
            $stmtNotif->execute([$adminId, 'payment', $notifMsg]);
            $msg = "Transaksi pembayaran berhasil dicatat.";
        } else {
            $err = "Mohon pilih pendaftaran siswa dan masukkan nominal pembayaran valid.";
        }
    } elseif ($_POST['action'] === 'update_status') {
        $id_pembayaran = (int)($_POST['id_pembayaran'] ?? 0);
        $status = trim($_POST['status'] ?? 'Lunas');
        if ($id_pembayaran) {
            $stmt = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = ? WHERE id_pembayaran = ?");
            $stmt->execute([$status, $id_pembayaran]);
            $msg = "Status pembayaran berhasil diperbarui menjadi: $status.";
        }
    }
}

$search = trim($_GET['q'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

$sql = "
    SELECT p.*, s.nama_siswa, s.no_telepon, k.nama_kelas, k.tingkat 
    FROM pembayaran p 
    JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran 
    JOIN siswa s ON pd.id_siswa = s.id_siswa 
    JOIN kelas k ON pd.id_kelas = k.id_kelas 
    WHERE 1=1
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (s.nama_siswa LIKE ? OR p.metode_bayar LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filter_status) && $filter_status !== 'all') {
    $sql .= " AND p.status_pembayaran = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY p.id_pembayaran DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_pembayaran = $stmt->fetchAll();

$daftar_pendaftaran = $pdo->query("
    SELECT pd.id_pendaftaran, s.nama_siswa, k.nama_kelas, k.biaya 
    FROM pendaftaran pd 
    JOIN siswa s ON pd.id_siswa = s.id_siswa 
    JOIN kelas k ON pd.id_kelas = k.id_kelas 
    ORDER BY s.nama_siswa ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Pembayaran -->
<main class="flex-1 p-8 overflow-y-auto space-y-6" data-purpose="payment-data-content">
  <!-- Top Title & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pembayaran</h2>
      <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
        <a class="hover:text-blue-600 transition-colors" href="index.php">Dashboard</a>
        <span class="text-slate-400">&gt;</span>
        <span class="text-slate-600 font-medium">Pembayaran</span>
      </div>
    </div>
    <div class="flex items-center gap-2.5">
      <button class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-lg shadow-xs transition-colors" onclick="window.print()" type="button">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        <span>Ekspor Data</span>
      </button>
      <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" onclick="openAddModal()" type="button">
        <span>+ Konfirmasi Pembayaran</span>
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

  <!-- Table Container Persis Stitch -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
    <form method="GET" action="pembayaran.php" class="relative flex-1 max-w-md">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </span>
      <input class="w-full pl-9 pr-4 py-2 bg-slate-50/50 border border-slate-200 rounded-lg text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white transition-colors" name="q" placeholder="Cari nama siswa atau metode pembayaran..." type="text" value="<?= htmlspecialchars($search) ?>">
    </form>
    <form method="GET" action="pembayaran.php" class="flex flex-wrap items-center gap-2.5">
      <select class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:outline-none focus:border-blue-600" name="status" onchange="this.form.submit()">
        <option value="all">Semua Status</option>
        <option value="Lunas" <?= $filter_status === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
        <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
        <option value="Ditolak" <?= $filter_status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
      </select>
    </form>
  </div>

  <div class="overflow-x-auto border border-slate-200/80 rounded-xl bg-white shadow-xs">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="text-slate-600 font-semibold border-b border-slate-200/80 bg-slate-50/70">
          <th class="py-3 px-4 font-semibold w-12 text-center">No</th>
          <th class="py-3 px-4 font-semibold">ID Pembayaran</th>
          <th class="py-3 px-4 font-semibold">Nama Siswa</th>
          <th class="py-3 px-4 font-semibold">Program / Kelas</th>
          <th class="py-3 px-4 font-semibold">Jumlah</th>
          <th class="py-3 px-4 font-semibold">Metode</th>
          <th class="py-3 px-4 font-semibold">Tanggal</th>
          <th class="py-3 px-4 font-semibold text-center">Status</th>
          <th class="py-3 px-4 font-semibold text-center w-24">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($daftar_pembayaran)): ?>
          <tr><td colspan="9" class="py-6 text-center text-slate-400">Belum ada riwayat pembayaran.</td></tr>
        <?php else: ?>
          <?php foreach ($daftar_pembayaran as $i => $pb): 
              $isLunas = ($pb['status_pembayaran'] === 'Lunas');
          ?>
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-medium text-slate-500 text-center"><?= $i + 1 ?></td>
              <td class="py-3.5 px-4 font-bold text-slate-900">INV-<?= str_pad($pb['id_pembayaran'], 4, '0', STR_PAD_LEFT) ?></td>
              <td class="py-3.5 px-4 font-semibold text-slate-800"><?= htmlspecialchars($pb['nama_siswa']) ?></td>
              <td class="py-3.5 px-4 text-slate-600"><?= htmlspecialchars($pb['nama_kelas'] ?? 'Reguler') ?> (<?= htmlspecialchars($pb['tingkat'] ?? '-') ?>)</td>
              <td class="py-3.5 px-4 font-bold text-blue-700">Rp <?= number_format($pb['jumlah_bayar'], 0, ',', '.') ?></td>
              <td class="py-3.5 px-4 text-slate-600"><?= htmlspecialchars($pb['metode_bayar']) ?></td>
              <td class="py-3.5 px-4 text-slate-500"><?= !empty($pb['tanggal_bayar']) ? date('d/m/Y', strtotime($pb['tanggal_bayar'])) : '-' ?></td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold <?= $isLunas ? 'bg-[#dcfce7] text-[#16a34a] border border-[#bbf7d0]' : 'bg-[#fef3c7] text-[#d97706] border border-[#fde68a]' ?>">
                  <?= htmlspecialchars($pb['status_pembayaran']) ?>
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5 text-blue-600">
                  <?php if (!$isLunas): ?>
                    <form method="POST" action="pembayaran.php" class="inline">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="id_pembayaran" value="<?= $pb['id_pembayaran'] ?>">
                      <input type="hidden" name="status" value="Lunas">
                      <button type="submit" class="p-1 hover:text-emerald-700 hover:bg-emerald-50 rounded transition-colors" title="Verifikasi Lunas">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                      </button>
                    </form>
                  <?php endif; ?>
                  <?php if (!empty($pb['bukti_pembayaran'])): ?>
                    <a href="../uploads/<?= htmlspecialchars($pb['bukti_pembayaran']) ?>" target="_blank" class="p-1 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" title="Lihat Bukti Bayar">
                      <span class="material-symbols-outlined text-base">image</span>
                    </a>
                  <?php endif; ?>
                  <button type="button" onclick="alert('Kuitansi INV-<?= str_pad($pb['id_pembayaran'], 4, '0', STR_PAD_LEFT) ?> telah dicetak untuk <?= htmlspecialchars($pb['nama_siswa']) ?>.')" class="p-1 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" title="Cetak Kuitansi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal Konfirmasi Pembayaran Baru -->
<div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4" id="addPaymentModal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <h3 class="font-bold text-slate-900 text-sm">Konfirmasi Pembayaran SPP</h3>
      <button class="text-slate-400 hover:text-slate-600" onclick="closeAddModal()" type="button">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path></svg>
      </button>
    </div>
    <form class="p-5 space-y-4 text-xs" method="POST" action="pembayaran.php">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block font-medium text-slate-700 mb-1">Pendaftaran Siswa</label>
        <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="id_pendaftaran" required>
          <?php foreach ($daftar_pendaftaran as $s): ?>
            <option value="<?= $s['id_pendaftaran'] ?>"><?= htmlspecialchars($s['nama_siswa']) ?> - <?= htmlspecialchars($s['nama_kelas']) ?> (Tagihan: Rp <?= number_format($s['biaya'], 0, ',', '.') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium text-slate-700 mb-1">Nominal Pembayaran (Rp)</label>
        <input class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="jumlah_bayar" placeholder="Contoh: 150000" type="number" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-medium text-slate-700 mb-1">Metode Pembayaran</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="metode_bayar">
            <option value="Transfer">Transfer Bank</option>
            <option value="Tunai">Tunai</option>
            <option value="E-Wallet">E-Wallet</option>
          </select>
        </div>
        <div>
          <label class="block font-medium text-slate-700 mb-1">Status</label>
          <select class="w-full px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" name="status_pembayaran">
            <option value="Lunas">Lunas</option>
            <option value="Menunggu">Menunggu</option>
            <option value="Ditolak">Ditolak</option>
          </select>
        </div>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors" onclick="closeAddModal()" type="button">Batal</button>
        <button class="px-4 py-2 rounded-lg bg-[#143d73] text-white font-medium hover:bg-[#0f2e57] transition-colors shadow-xs" type="submit">Simpan Transaksi</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  const m = document.getElementById('addPaymentModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddModal() {
  const m = document.getElementById('addPaymentModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
