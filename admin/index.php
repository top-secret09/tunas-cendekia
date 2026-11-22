<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$page_title = "Dashboard Admin";
$current_page = "dashboard";

// Hitung metrik riil dari database
$total_siswa = (int)$pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$total_guru = (int)$pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
$total_kelas = (int)$pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$total_pendaftaran = (int)$pdo->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn();

// Ambil 5 siswa terbaru
$stmt_siswa = $pdo->query("
    SELECT s.id_siswa, s.nama_siswa, s.tanggal_daftar, k.nama_kelas, k.tingkat 
    FROM siswa s 
    LEFT JOIN pendaftaran p ON s.id_siswa = p.id_siswa 
    LEFT JOIN kelas k ON p.id_kelas = k.id_kelas 
    ORDER BY s.id_siswa DESC LIMIT 5
");
$siswa_terbaru = $stmt_siswa->fetchAll();

// Ambil jadwal terdekat
$stmt_jadwal = $pdo->query("
    SELECT j.hari, j.jam_mulai, j.jam_selesai, m.nama_mapel, k.nama_kelas, g.nama_guru 
    FROM jadwal j 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    LEFT JOIN kelas k ON j.id_kelas = k.id_kelas 
    JOIN guru g ON j.id_guru = g.id_guru 
    ORDER BY j.id_jadwal ASC LIMIT 4
");
$jadwal_terdekat = $stmt_jadwal->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: DashboardBody Persis Stitch -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto" data-purpose="dashboard-main-content">
<!-- Page Header & Date Badge -->
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
<div>
  <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h2>
  <p class="text-slate-500 text-sm mt-0.5">Selamat datang, Administrator!</p>
</div>
</section>

<!-- BEGIN: 4 Stat Cards Row Persis Stitch Design -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" data-purpose="summary-cards">
<!-- Card 1: Total Siswa (Dark Blue) -->
<div class="bg-[#2962be] rounded-xl p-5 text-white shadow-sm flex flex-col justify-between">
<div>
  <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3">
    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
      <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path>
    </svg>
  </div>
  <div class="text-xs font-medium text-blue-100">Total Siswa</div>
  <div class="text-3xl font-bold mt-1 text-white tracking-tight"><?= number_format($total_siswa, 0, ',', '.') ?></div>
</div>
</div>

<!-- Card 2: Total Pengajar (Sky Blue) -->
<div class="bg-[#99c4ff] rounded-xl p-5 text-slate-800 shadow-sm flex flex-col justify-between">
<div>
  <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3">
    <svg class="w-8 h-8 text-[#1e3a8a]" fill="currentColor" viewBox="0 0 24 24">
      <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"></path>
    </svg>
  </div>
  <div class="text-xs font-semibold text-slate-800">Total Pengajar</div>
  <div class="text-3xl font-bold mt-1 text-slate-900 tracking-tight"><?= number_format($total_guru, 0, ',', '.') ?></div>
</div>
</div>

<!-- Card 3: Total Kelas (Soft Yellow) -->
<div class="bg-[#ffd778] rounded-xl p-5 text-slate-800 shadow-sm flex flex-col justify-between">
<div>
  <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3">
    <svg class="w-8 h-8 text-[#854d0e]" fill="currentColor" viewBox="0 0 24 24">
      <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.65 1 5.75v14.5c1.45-1.1 3.55-1.5 5.5-1.5 1.95 0 4.05.4 5.5 1.5 1.45-1.1 3.55-1.5 5.5-1.5 1.17 0 2.39.15 3.5.5V5zm-2 12c-.88-.23-1.84-.35-2.8-.35-1.4 0-2.82.3-4.2.9V7.1c1.13-.57 2.45-.85 3.7-.85 1.15 0 2.27.2 3.3.65v10.1z"></path>
    </svg>
  </div>
  <div class="text-xs font-semibold text-slate-800">Total Kelas</div>
  <div class="text-3xl font-bold mt-1 text-slate-900 tracking-tight"><?= number_format($total_kelas, 0, ',', '.') ?></div>
</div>
</div>

<!-- Card 4: Total Pendaftaran (Light Blue/Gray) -->
<div class="bg-[#e4effb] rounded-xl p-5 text-slate-800 shadow-sm flex flex-col justify-between border border-blue-100/50">
<div>
  <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3">
    <svg class="w-8 h-8 text-[#1e3a8a]" fill="currentColor" viewBox="0 0 24 24">
      <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
    </svg>
  </div>
  <div class="text-xs font-semibold text-slate-800">Total Pendaftaran</div>
  <div class="text-3xl font-bold mt-1 text-slate-900 tracking-tight"><?= number_format($total_pendaftaran, 0, ',', '.') ?></div>
</div>
</div>
</section>
<!-- END: 4 Stat Cards Row -->

<!-- BEGIN: Charts Section Persis Stitch Design -->
<section class="grid grid-cols-1 lg:grid-cols-2 gap-5" data-purpose="charts-section">
<!-- Left: Statistik Jumlah Siswa (Line Chart) -->
<div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
<h3 class="font-bold text-slate-900 text-sm mb-4">Statistik Jumlah Siswa</h3>
<div class="w-full flex items-center">
  <!-- Y-Axis Labels -->
  <div class="flex flex-col justify-between text-[11px] text-slate-500 font-medium h-44 pr-2 text-right w-10 select-none">
    <span>100</span>
    <span>75</span>
    <span>50</span>
    <span>25</span>
    <span>0</span>
  </div>
  <!-- Chart Area Container -->
  <div class="flex-1 flex flex-col">
    <!-- SVG Grid and Line Plot -->
    <div class="relative w-full h-44">
      <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 500 176">
        <!-- Horizontal Grid Lines -->
        <line stroke="#e2e8f0" stroke-width="1" x1="0" x2="500" y1="8" y2="8"></line>
        <line stroke="#e2e8f0" stroke-width="1" x1="0" x2="500" y1="48" y2="48"></line>
        <line stroke="#e2e8f0" stroke-width="1" x1="0" x2="500" y1="88" y2="88"></line>
        <line stroke="#e2e8f0" stroke-width="1" x1="0" x2="500" y1="128" y2="128"></line>
        <line stroke="#cbd5e1" stroke-width="1.2" x1="0" x2="500" y1="168" y2="168"></line>
        <!-- Trend Line (Upward Curve) -->
        <polyline fill="none" points="35,120 115,105 200,85 285,68 375,45 460,25" stroke="#3b82f6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></polyline>
        <!-- Data Point Dots -->
        <circle cx="35" cy="120" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
        <circle cx="115" cy="105" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
        <circle cx="200" cy="85" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
        <circle cx="285" cy="68" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
        <circle cx="375" cy="45" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
        <circle cx="460" cy="25" fill="#3b82f6" r="4.5" stroke="#ffffff" stroke-width="1.5"></circle>
      </svg>
    </div>
    <!-- X-Axis Labels -->
    <div class="flex justify-between text-[11px] text-slate-500 font-medium px-4 pt-2 select-none">
      <span>Jan</span>
      <span>Feb</span>
      <span>Mar</span>
      <span>Apr</span>
      <span>Mei</span>
      <span>Jun</span>
    </div>
  </div>
</div>
</div>

<!-- Right: Grafik Pembayaran (Bar Chart) -->
<div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
<h3 class="font-bold text-slate-900 text-sm mb-4">Grafik Pembayaran</h3>
<div class="w-full flex items-center">
  <!-- Y-Axis Labels -->
  <div class="flex flex-col justify-between text-[11px] text-slate-500 font-medium h-44 pr-2 text-right w-10 select-none">
    <span>15jt</span>
    <span>10jt</span>
    <span>5jt</span>
    <span>0</span>
  </div>
  <!-- Bar Chart Area Container -->
  <div class="flex-1 flex flex-col">
    <div class="relative w-full h-44 flex items-end">
      <!-- Horizontal Grid Lines inside Bar Chart -->
      <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
        <div class="border-b border-slate-200/70 w-full h-0"></div>
        <div class="border-b border-slate-200/70 w-full h-0"></div>
        <div class="border-b border-slate-200/70 w-full h-0"></div>
        <div class="border-b border-slate-300 w-full h-0"></div>
      </div>
      <!-- 6 Monthly Bars -->
      <div class="w-full flex justify-between items-end px-4 relative z-10 h-full pb-[1px]">
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[40%] transition-all hover:bg-blue-600" title="Jan: Rp 6.000.000"></div>
        </div>
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[58%] transition-all hover:bg-blue-600" title="Feb: Rp 8.700.000"></div>
        </div>
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[68%] transition-all hover:bg-blue-600" title="Mar: Rp 10.200.000"></div>
        </div>
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[62%] transition-all hover:bg-blue-600" title="Apr: Rp 9.300.000"></div>
        </div>
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[88%] transition-all hover:bg-blue-600" title="Mei: Rp 13.200.000"></div>
        </div>
        <div class="flex flex-col items-center w-8">
          <div class="w-5 bg-[#3177d6] rounded-t-sm h-[75%] transition-all hover:bg-blue-600" title="Jun: Rp 11.250.000"></div>
        </div>
      </div>
    </div>
    <!-- X-Axis Labels -->
    <div class="flex justify-between text-[11px] text-slate-500 font-medium px-4 pt-2 select-none">
      <span>Jan</span>
      <span>Feb</span>
      <span>Mar</span>
      <span>Apr</span>
      <span>Mei</span>
      <span>Jun</span>
    </div>
  </div>
</div>
</div>
</section>
<!-- END: Charts Section -->

<!-- BEGIN: Bottom Three Panels Row Persis Stitch Design -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" data-purpose="recent-activities">
<!-- Panel 1: Siswa Terbaru -->
<div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
<div>
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-900 text-sm">Siswa Terbaru</h3>
    <a class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-0.5" href="siswa.php">
      Lihat Semua →
    </a>
  </div>
  <!-- Table of recent students -->
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="text-slate-500 font-semibold border-b border-slate-100">
          <th class="pb-2.5 font-medium">No</th>
          <th class="pb-2.5 font-medium">Nama Siswa</th>
          <th class="pb-2.5 font-medium">Kelas</th>
          <th class="pb-2.5 font-medium text-right">Tanggal Daftar</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($siswa_terbaru)): ?>
          <tr><td colspan="4" class="py-3 text-center text-slate-400">Belum ada siswa terdaftar.</td></tr>
        <?php else: ?>
          <?php foreach ($siswa_terbaru as $i => $s): ?>
            <tr class="hover:bg-slate-50/60">
              <td class="py-2.5 font-medium text-slate-500"><?= $i + 1 ?></td>
              <td class="py-2.5 font-medium text-slate-900"><?= htmlspecialchars($s['nama_siswa']) ?></td>
              <td class="py-2.5 text-slate-600"><?= htmlspecialchars($s['tingkat'] ?? $s['nama_kelas'] ?? 'Reguler') ?></td>
              <td class="py-2.5 text-right text-slate-500 font-medium"><?= date('d/m/Y', strtotime($s['tanggal_daftar'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<!-- Panel 2: Jadwal Terdekat -->
<div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
<div>
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-900 text-sm">Jadwal Terdekat</h3>
    <a class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-0.5" href="jadwal.php">
      Lihat Semua →
    </a>
  </div>
  <!-- List of schedule items -->
  <div class="space-y-3.5">
    <?php if (empty($jadwal_terdekat)): ?>
      <p class="text-xs text-slate-400 py-2">Belum ada jadwal bimbingan aktif.</p>
    <?php else: ?>
      <?php foreach ($jadwal_terdekat as $idx => $jd): ?>
        <div class="<?= $idx < count($jadwal_terdekat) - 1 ? 'border-b border-slate-100 pb-3' : '' ?>">
          <div class="text-xs font-bold text-slate-900"><?= htmlspecialchars($jd['hari']) ?>, <?= substr($jd['jam_mulai'], 0, 5) ?> - <?= substr($jd['jam_selesai'], 0, 5) ?> WIB</div>
          <div class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($jd['nama_mapel']) ?> - <?= htmlspecialchars($jd['nama_kelas']) ?> (<?= htmlspecialchars($jd['nama_guru']) ?>)</div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</div>

<!-- Panel 3: Notifikasi Ringkas -->
<div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
<div>
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-900 text-sm">Notifikasi</h3>
    <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-0.5" onclick="document.getElementById('notification-btn').click();">
      Buka Panel →
    </button>
  </div>
  <!-- Notifications list -->
  <div class="space-y-3.5">
    <div class="flex items-start gap-3">
      <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white flex-shrink-0 mt-0.5 shadow-xs">
        <svg class="w-3.5 h-3.5 stroke-current stroke-2" fill="none" viewBox="0 0 24 24">
          <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-900">Pembayaran SPP Masuk</p>
        <p class="text-[11px] text-slate-400 mt-0.5">5 menit yang lalu</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center text-white flex-shrink-0 mt-0.5 shadow-xs">
        <svg class="w-3.5 h-3.5 stroke-current stroke-2" fill="none" viewBox="0 0 24 24">
          <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-900">Pendaftaran Siswa Baru</p>
        <p class="text-[11px] text-slate-400 mt-0.5">30 menit yang lalu</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white flex-shrink-0 mt-0.5 shadow-xs">
        <svg class="w-3.5 h-3.5 stroke-current stroke-2" fill="none" viewBox="0 0 24 24">
          <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-900">Verifikasi Presensi Kelas</p>
        <p class="text-[11px] text-slate-400 mt-0.5">2 jam yang lalu</p>
      </div>
    </div>
  </div>
</div>
</div>
</section>
<!-- END: Bottom Three Panels Row -->
</main>
<!-- END: DashboardBody -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
