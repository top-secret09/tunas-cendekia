<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Laporan";
$current_page = "laporan";

$jenis = $_GET['jenis'] ?? 'siswa';
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$data_laporan = [];
if ($jenis === 'siswa') {
    $stmt = $pdo->query("
        SELECT s.id_siswa, s.nama_siswa, s.jenis_Kelamin, s.no_telepon, s.tanggal_daftar, k.nama_kelas, k.tingkat 
        FROM siswa s 
        LEFT JOIN pendaftaran p ON s.id_siswa = p.id_siswa 
        LEFT JOIN kelas k ON p.id_kelas = k.id_kelas 
        ORDER BY s.id_siswa ASC
    ");
    $data_laporan = $stmt->fetchAll();
} elseif ($jenis === 'pembayaran') {
    $stmt = $pdo->prepare("
        SELECT p.*, s.nama_siswa, k.nama_kelas 
        FROM pembayaran p 
        JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran 
        JOIN siswa s ON pd.id_siswa = s.id_siswa 
        JOIN kelas k ON pd.id_kelas = k.id_kelas 
        WHERE p.tanggal_bayar BETWEEN ? AND ? 
        ORDER BY p.id_pembayaran ASC
    ");
    $stmt->execute([$tgl_mulai, $tgl_selesai]);
    $data_laporan = $stmt->fetchAll();
} elseif ($jenis === 'jadwal') {
    $stmt = $pdo->query("
        SELECT j.*, m.nama_mapel, k.nama_kelas, g.nama_guru 
        FROM jadwal j 
        JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
        JOIN kelas k ON j.id_kelas = k.id_kelas 
        JOIN guru g ON j.id_guru = g.id_guru 
        ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    $data_laporan = $stmt->fetchAll();
} elseif ($jenis === 'pendaftaran') {
    $stmt = $pdo->prepare("
        SELECT p.*, s.nama_siswa, s.no_telepon, k.nama_kelas 
        FROM pendaftaran p 
        JOIN siswa s ON p.id_siswa = s.id_siswa 
        JOIN kelas k ON p.id_kelas = k.id_kelas 
        WHERE p.tanggal_daftar BETWEEN ? AND ? 
        ORDER BY p.id_pendaftaran ASC
    ");
    $stmt->execute([$tgl_mulai, $tgl_selesai]);
    $data_laporan = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Laporan -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto" data-purpose="laporan-main-content">
  <!-- Outer Container matching Stitch -->
  <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-8">
    <!-- Breadcrumb & Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan</h2>
      <p class="text-slate-400 text-xs mt-1">Dashboard &gt; Laporan</p>
    </div>

    <!-- Two-Column Configuration Box Persis Stitch -->
    <form method="GET" action="laporan.php" id="formLaporan">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start max-w-4xl pt-2">
        <!-- Bagian Kiri: Jenis Laporan (5 cols) -->
        <div class="md:col-span-5">
          <h3 class="text-sm font-bold text-slate-800 mb-4">Jenis Laporan</h3>
          <div class="space-y-3.5">
            <label class="flex items-center gap-3 cursor-pointer group">
              <input class="w-4 h-4 text-[#143d73] focus:ring-[#143d73] border-slate-300" name="jenis" type="radio" value="siswa" <?= $jenis === 'siswa' ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="text-xs text-slate-700 group-hover:text-slate-900">Laporan Data Siswa</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer group">
              <input class="w-4 h-4 text-[#143d73] focus:ring-[#143d73] border-slate-300" name="jenis" type="radio" value="pembayaran" <?= $jenis === 'pembayaran' ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="text-xs text-slate-700 group-hover:text-slate-900">Laporan Pembayaran</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer group">
              <input class="w-4 h-4 text-[#143d73] focus:ring-[#143d73] border-slate-300" name="jenis" type="radio" value="jadwal" <?= $jenis === 'jadwal' ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="text-xs text-slate-700 group-hover:text-slate-900">Laporan Jadwal Bimbingan</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer group">
              <input class="w-4 h-4 text-[#143d73] focus:ring-[#143d73] border-slate-300" name="jenis" type="radio" value="pendaftaran" <?= $jenis === 'pendaftaran' ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="text-xs text-slate-700 group-hover:text-slate-900">Laporan Pendaftaran</span>
            </label>
          </div>
        </div>

        <!-- Bagian Kanan: Periode & Action Buttons (7 cols) -->
        <div class="md:col-span-7">
          <div class="flex items-center gap-2 mb-3">
            <h3 class="text-sm font-bold text-slate-800">Periode Laporan</h3>
          </div>
          <!-- Date Picker Inputs -->
          <div class="grid grid-cols-2 gap-3 max-w-sm mb-6">
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Mulai</label>
              <input class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-blue-500 shadow-2xs" type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai) ?>" onchange="this.form.submit()">
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Sampai</label>
              <input class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-blue-500 shadow-2xs" type="date" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai) ?>" onchange="this.form.submit()">
            </div>
          </div>
          <!-- Action Buttons Persis Stitch -->
          <div class="flex items-center gap-3 pt-1">
            <button class="px-7 py-2.5 bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold rounded-lg shadow-xs transition-colors" onclick="window.print()" type="button">
              Cetak Laporan
            </button>
            <button class="px-6 py-2.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 text-xs font-medium rounded-lg shadow-2xs transition-colors" onclick="window.print()" type="button">
              Export PDF
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Preview Table Laporan -->
    <div class="mt-8 pt-6 border-t border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h4 class="font-bold text-slate-900 text-sm">Pratinjau Data: Laporan <?= ucfirst($jenis) ?></h4>
        <span class="text-xs text-slate-500">Total: <?= count($data_laporan) ?> Baris Data</span>
      </div>
      <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase">
              <th class="py-3 px-4 w-12 text-center">NO</th>
              <?php if ($jenis === 'siswa'): ?>
                <th class="py-3 px-4">NAMA SISWA</th>
                <th class="py-3 px-4">JENIS KELAMIN</th>
                <th class="py-3 px-4">KELAS</th>
                <th class="py-3 px-4">NO. TELEPON</th>
                <th class="py-3 px-4">TANGGAL DAFTAR</th>
              <?php elseif ($jenis === 'pembayaran'): ?>
                <th class="py-3 px-4">INVOICE</th>
                <th class="py-3 px-4">NAMA SISWA</th>
                <th class="py-3 px-4">KELAS</th>
                <th class="py-3 px-4">NOMINAL</th>
                <th class="py-3 px-4">METODE</th>
                <th class="py-3 px-4">STATUS</th>
              <?php elseif ($jenis === 'jadwal'): ?>
                <th class="py-3 px-4">HARI</th>
                <th class="py-3 px-4">JAM</th>
                <th class="py-3 px-4">MAPEL</th>
                <th class="py-3 px-4">KELAS</th>
                <th class="py-3 px-4">PENGAJAR</th>
              <?php elseif ($jenis === 'pendaftaran'): ?>
                <th class="py-3 px-4">REGISTRASI</th>
                <th class="py-3 px-4">NAMA SISWA</th>
                <th class="py-3 px-4">KELAS</th>
                <th class="py-3 px-4">TANGGAL</th>
                <th class="py-3 px-4">STATUS</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($data_laporan)): ?>
              <tr><td colspan="7" class="py-6 text-center text-slate-400">Tidak ada data untuk periode ini.</td></tr>
            <?php else: ?>
              <?php foreach ($data_laporan as $idx => $row): ?>
                <tr class="hover:bg-slate-50/70">
                  <td class="py-3 px-4 text-center text-slate-500 font-medium"><?= $idx + 1 ?></td>
                  <?php if ($jenis === 'siswa'): ?>
                    <td class="py-3 px-4 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td class="py-3 px-4 capitalize"><?= htmlspecialchars($row['jenis_Kelamin']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_kelas'] ?? 'Reguler') ?> (<?= htmlspecialchars($row['tingkat'] ?? '-') ?>)</td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['no_telepon']) ?></td>
                    <td class="py-3 px-4"><?= date('d/m/Y', strtotime($row['tanggal_daftar'])) ?></td>
                  <?php elseif ($jenis === 'pembayaran'): ?>
                    <td class="py-3 px-4 font-bold text-slate-900">INV-<?= str_pad($row['id_pembayaran'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-3 px-4 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                    <td class="py-3 px-4 font-bold text-blue-700">Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.') ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['metode_bayar']) ?></td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800"><?= htmlspecialchars($row['status_pembayaran']) ?></span></td>
                  <?php elseif ($jenis === 'jadwal'): ?>
                    <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($row['hari']) ?></td>
                    <td class="py-3 px-4 font-medium text-blue-700"><?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></td>
                    <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($row['nama_mapel']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_kelas']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_guru']) ?></td>
                  <?php elseif ($jenis === 'pendaftaran'): ?>
                    <td class="py-3 px-4 font-bold text-slate-900">REG-<?= str_pad($row['id_pendaftaran'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-3 px-4 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_kelas']) ?></td>
                    <td class="py-3 px-4"><?= date('d/m/Y', strtotime($row['tanggal_daftar'])) ?></td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800"><?= htmlspecialchars($row['status_pendaftaran']) ?></span></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
