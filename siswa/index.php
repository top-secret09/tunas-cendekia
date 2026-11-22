<?php
require_once __DIR__ . '/../config/database.php';
require_role('siswa');

$page_title = "Dashboard Siswa";
$current_page = "dashboard";
$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);
$nama_siswa = $user['nama'] ?? 'Siswa';

// Ambil info pendaftaran & kelas siswa
$stmt_siswa_info = $pdo->prepare("
    SELECT s.*, p.id_kelas, p.status_pendaftaran, k.nama_kelas, k.tingkat, k.biaya 
    FROM siswa s 
    LEFT JOIN pendaftaran p ON s.id_siswa = p.id_siswa 
    LEFT JOIN kelas k ON p.id_kelas = k.id_kelas 
    WHERE s.id_siswa = ? 
    ORDER BY p.id_pendaftaran DESC LIMIT 1
");
$stmt_siswa_info->execute([$id_siswa]);
$siswa_info = $stmt_siswa_info->fetch();

$id_kelas = $siswa_info['id_kelas'] ?? 1;
$nama_kelas = $siswa_info['nama_kelas'] ?? 'Reguler';
$tingkat = $siswa_info['tingkat'] ?? 'SMA';

// Ambil jadwal belajar untuk kelas siswa
$stmt_jadwal = $pdo->prepare("
    SELECT j.*, m.nama_mapel, g.nama_guru, k.nama_kelas 
    FROM jadwal j 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    JOIN guru g ON j.id_guru = g.id_guru 
    JOIN kelas k ON j.id_kelas = k.id_kelas 
    WHERE j.id_kelas = ? 
    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
");
$stmt_jadwal->execute([$id_kelas]);
$jadwal_belajar = $stmt_jadwal->fetchAll();

// Hitung mapel unik
$mapel_diikuti = [];
foreach ($jadwal_belajar as $jb) {
    $mapel_diikuti[$jb['id_mapel']] = $jb['nama_mapel'];
}
$total_mapel = count($mapel_diikuti) ?: 4;

// Ambil info pembayaran terkini
$stmt_bayar = $pdo->prepare("
    SELECT p.* 
    FROM pembayaran p 
    JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran 
    WHERE pd.id_siswa = ? 
    ORDER BY p.id_pembayaran DESC LIMIT 1
");
$stmt_bayar->execute([$id_siswa]);
$pembayaran_terkini = $stmt_bayar->fetch();
$status_biaya = $pembayaran_terkini['status_pembayaran'] ?? 'Lunas';

// Ambil riwayat nilai siswa
$stmt_nilai = $pdo->prepare("
    SELECT h.*, l.judul_latihan as judul, m.nama_mapel 
    FROM hasil_latihan h 
    JOIN latihan_soal l ON h.id_latihan = l.id_latihan 
    JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
    WHERE h.id_siswa = ? 
    ORDER BY h.id_hasil DESC LIMIT 5
");
$stmt_nilai->execute([$id_siswa]);
$riwayat_nilai = $stmt_nilai->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- CONTENT BODY PERSIS STITCH DESIGN SISWA -->
<main class="flex-1 px-8 py-6 max-w-7xl w-full mx-auto flex flex-col gap-6 overflow-y-auto">
<!-- Top Header & Info Badges Persis Stitch -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
  <div>
    <div class="flex items-center gap-2">
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">Siswa Aktif</span>
    </div>
    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Selamat datang, <span class="font-semibold text-blue-700"><?= htmlspecialchars($nama_siswa) ?></span>!</p>
  </div>
  <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200/70 rounded-lg text-xs">
      <span class="text-slate-500">NIS:</span>
      <span class="font-semibold text-blue-700">S-<?= str_pad($id_siswa, 4, '0', STR_PAD_LEFT) ?></span>
    </div>
    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200/70 rounded-lg text-xs">
      <span class="text-slate-500">Kelas:</span>
      <span class="font-semibold text-blue-700"><?= htmlspecialchars($nama_kelas) ?></span>
    </div>
    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200/70 rounded-lg text-xs">
      <span class="text-slate-500">Program:</span>
      <span class="font-semibold text-slate-800"><?= htmlspecialchars($tingkat) ?> Reguler</span>
    </div>
  </div>
</div>

<!-- 4 Metric Cards Grid Persis Stitch -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <!-- Card 1: Mapel Diikuti -->
  <div class="bg-[#143d73] text-white rounded-2xl p-5 flex flex-col justify-between shadow-xs min-h-[120px]">
    <div class="flex items-center justify-between">
      <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center text-white">
        <span class="material-symbols-outlined text-xl">menu_book</span>
      </div>
    </div>
    <div class="mt-2">
      <p class="text-[11px] text-blue-200 uppercase tracking-wider font-semibold">Kelas Diikuti</p>
      <div class="text-2xl font-bold text-white mt-0.5"><?= $total_mapel ?> Mapel</div>
    </div>
  </div>

  <!-- Card 2: Jadwal Bimbingan -->
  <div class="bg-[#93c5fd] text-slate-900 rounded-2xl p-5 flex flex-col justify-between shadow-xs min-h-[120px]">
    <div class="flex items-center justify-between">
      <div class="w-9 h-9 rounded-lg bg-white/60 flex items-center justify-center text-blue-900">
        <span class="material-symbols-outlined text-xl">calendar_month</span>
      </div>
    </div>
    <div class="mt-2">
      <p class="text-[11px] text-blue-900/80 uppercase tracking-wider font-bold">Jadwal Sesi</p>
      <div class="text-2xl font-bold text-slate-900 mt-0.5"><?= count($jadwal_belajar) ?: 2 ?> Sesi/Minggu</div>
    </div>
  </div>

  <!-- Card 3: Latihan & Materi -->
  <div class="bg-[#fde047] text-slate-900 rounded-2xl p-5 flex flex-col justify-between shadow-xs min-h-[120px]">
    <div class="flex items-center justify-between">
      <div class="w-9 h-9 rounded-lg bg-white/60 flex items-center justify-center text-amber-900">
        <span class="material-symbols-outlined text-xl">auto_stories</span>
      </div>
    </div>
    <div class="mt-2">
      <p class="text-[11px] text-amber-900/90 uppercase tracking-wider font-bold">Materi & Kuis</p>
      <div class="text-2xl font-bold text-slate-900 mt-0.5"><?= count($riwayat_nilai) ?: 4 ?> Modul</div>
    </div>
  </div>

  <!-- Card 4: Status Pembayaran -->
  <div class="bg-emerald-100 text-emerald-900 rounded-2xl p-5 flex flex-col justify-between shadow-xs min-h-[120px]">
    <div class="flex items-center justify-between">
      <div class="w-9 h-9 rounded-lg bg-white/70 flex items-center justify-center text-emerald-800">
        <span class="material-symbols-outlined text-xl">verified</span>
      </div>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-emerald-800 uppercase tracking-wider"><?= date('M Y') ?></span>
    </div>
    <div class="mt-2">
      <p class="text-[11px] text-emerald-800/80 uppercase tracking-wider font-bold">Status Biaya</p>
      <div class="text-2xl font-bold text-emerald-800 mt-0.5"><?= htmlspecialchars($status_biaya) ?></div>
    </div>
  </div>
</div>

<!-- 2-Column Balanced Main Content Persis Stitch -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
  <!-- Kolom Kiri -->
  <div class="space-y-6">
    <!-- Jadwal Belajar -->
    <section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">Jadwal Belajar Bimbingan</h2>
        <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="jadwal.php">
          <span>Lihat Semua</span>
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
      <div class="space-y-3">
        <?php if (empty($jadwal_belajar)): ?>
          <p class="text-xs text-slate-400 py-3">Belum ada jadwal belajar bimbingan.</p>
        <?php else: ?>
          <?php foreach (array_slice($jadwal_belajar, 0, 3) as $i => $jb): ?>
            <div class="p-3.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors flex items-center justify-between gap-3">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-semibold text-slate-500"><?= htmlspecialchars($jb['hari']) ?>, <?= substr($jb['jam_mulai'], 0, 5) ?> – <?= substr($jb['jam_selesai'], 0, 5) ?></span>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $i === 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700' ?>">
                    <?= $i === 0 ? 'Terjadwal' : 'Akan Datang' ?>
                  </span>
                </div>
                <h3 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($jb['nama_mapel']) ?> <span class="font-normal text-slate-500">(<?= htmlspecialchars($jb['nama_guru']) ?>)</span></h3>
              </div>
              <span class="material-symbols-outlined text-slate-400 shrink-0">chevron_right</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Informasi Materi -->
    <section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">Materi & Bahan Pembelajaran</h2>
        <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="latihan.php">
          <span>Lihat Latihan</span>
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
      <div class="space-y-2">
        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-lg">description</span>
            </div>
            <div class="min-w-0">
              <span class="text-sm font-semibold text-slate-800 truncate block">Modul 01 - Pengantar Materi Terpadu</span>
              <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="text-blue-600 font-medium"><?= htmlspecialchars($nama_kelas) ?></span>
                <span>•</span>
                <span>Terbaru</span>
              </div>
            </div>
          </div>
          <span class="material-symbols-outlined text-slate-400 text-sm shrink-0">chevron_right</span>
        </div>
        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-lg">functions</span>
            </div>
            <div class="min-w-0">
              <span class="text-sm font-semibold text-slate-800 truncate block">Latihan Evaluasi Soal Mandiri</span>
              <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="text-blue-600 font-medium">Kuis Online</span>
                <span>•</span>
                <span>Tersedia</span>
              </div>
            </div>
          </div>
          <span class="material-symbols-outlined text-slate-400 text-sm shrink-0">chevron_right</span>
        </div>
      </div>
    </section>
  </div>

  <!-- Kolom Kanan -->
  <div class="space-y-6">
    <!-- Informasi Kelas Terdaftar -->
    <section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">Informasi Kelas Terdaftar</h2>
        <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="kelas.php">
          <span>Lihat Semua</span>
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
      <div class="space-y-2">
        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0 border border-blue-100">1</div>
            <div class="min-w-0">
              <span class="text-sm font-semibold text-slate-800 truncate block"><?= htmlspecialchars($nama_kelas) ?> (<?= htmlspecialchars($tingkat) ?>)</span>
              <span class="text-xs text-slate-500 truncate block">Status Pendaftaran: <?= htmlspecialchars($siswa_info['status_pendaftaran'] ?? 'Diterima') ?></span>
            </div>
          </div>
          <span class="material-symbols-outlined text-slate-400 text-sm shrink-0">chevron_right</span>
        </div>
      </div>
    </section>

    <!-- Ringkasan Pembayaran -->
    <section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">Ringkasan Pembayaran</h2>
        <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="pembayaran.php">
          <span>Lihat Detail</span>
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex flex-col gap-3">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-slate-500 block">Biaya Bimbingan Terkini</span>
            <span class="text-sm font-bold text-slate-800">SPP Bulan <?= date('F Y') ?></span>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $status_biaya === 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?> flex items-center gap-1">
            <span class="material-symbols-outlined text-sm"><?= $status_biaya === 'Lunas' ? 'check_circle' : 'pending' ?></span> <?= strtoupper($status_biaya) ?>
          </span>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-xs text-slate-500">
          <div>Nominal: <span class="font-bold text-slate-800">Rp <?= number_format($siswa_info['biaya'] ?? 500000, 0, ',', '.') ?></span></div>
          <div>Jatuh Tempo: 28 <?= date('M Y') ?></div>
        </div>
        <a class="mt-1 text-center py-2 px-3 rounded-lg bg-[#143d73] hover:bg-[#0f2e57] text-white text-xs font-semibold transition-colors" href="pembayaran.php">
          Lihat Riwayat &amp; Bukti Pembayaran
        </a>
      </div>
    </section>
  </div>
</div>

<!-- Bottom Section: Clean Table Riwayat Nilai Persis Stitch -->
<section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
  <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
    <div>
      <h2 class="text-base font-bold text-slate-900">Riwayat Nilai Latihan</h2>
      <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi hasil pengerjaan kuis dan evaluasi mandiri terkini</p>
    </div>
    <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="nilai.php">
      <span>Lihat Semua</span>
      <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs sm:text-sm">
      <thead>
        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
          <th class="py-2.5 px-3 w-12 font-semibold">No</th>
          <th class="py-2.5 px-3 font-semibold">Nama Latihan &amp; Materi</th>
          <th class="py-2.5 px-3 font-semibold">Mata Pelajaran</th>
          <th class="py-2.5 px-3 font-semibold">Tanggal</th>
          <th class="py-2.5 px-3 font-semibold text-right">Nilai / Evaluasi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($riwayat_nilai)): ?>
          <tr class="hover:bg-slate-50/50 transition-colors">
            <td class="py-3 px-3 text-slate-500">1</td>
            <td class="py-3 px-3 font-semibold text-slate-800">Evaluasi Pemahaman Dasar</td>
            <td class="py-3 px-3 text-slate-600">Umum</td>
            <td class="py-3 px-3 text-slate-500 text-xs"><?= date('d M Y') ?></td>
            <td class="py-3 px-3 text-right">
              <span class="font-bold text-blue-700 text-base">90 / 100</span>
              <span class="inline-block ml-2 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Sangat Baik</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($riwayat_nilai as $i => $rn): ?>
            <tr class="hover:bg-slate-50/50 transition-colors">
              <td class="py-3 px-3 text-slate-500 font-medium"><?= $i + 1 ?></td>
              <td class="py-3 px-3 font-semibold text-slate-800"><?= htmlspecialchars($rn['judul']) ?></td>
              <td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($rn['nama_mapel']) ?></td>
              <td class="py-3 px-3 text-slate-500 text-xs"><?= date('d M Y', strtotime($rn['tanggal_mengerjakan'] ?? 'now')) ?></td>
              <td class="py-3 px-3 text-right">
                <span class="font-bold text-blue-700 text-base"><?= (int)$rn['nilai'] ?> / 100</span>
                <span class="inline-block ml-2 px-2 py-0.5 rounded text-[11px] font-semibold <?= $rn['nilai'] >= 85 ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' ?>">
                  <?= $rn['nilai'] >= 85 ? 'Sangat Baik' : 'Baik' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
