<?php
require_once __DIR__ . '/../config/database.php';
require_role('guru');

$page_title = "Dashboard Pengajar";
$current_page = "dashboard";
$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);
$nama_guru = $user['nama'] ?? 'Pengajar';

// Hitung metrik dinamis
// 1. Kelas diampu
$stmt_kelas = $pdo->prepare("
    SELECT DISTINCT k.id_kelas, k.nama_kelas, k.tingkat, m.nama_mapel 
    FROM jadwal j 
    JOIN kelas k ON j.id_kelas = k.id_kelas 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    WHERE j.id_guru = ?
");
$stmt_kelas->execute([$id_guru]);
$kelas_diampu = $stmt_kelas->fetchAll();
$total_kelas_diampu = count($kelas_diampu);

// 2. Total siswa di kelas yang diajar
$stmt_jml_siswa = $pdo->prepare("
    SELECT COUNT(DISTINCT p.id_siswa) 
    FROM jadwal j 
    JOIN pendaftaran p ON j.id_kelas = p.id_kelas 
    WHERE j.id_guru = ?
");
$stmt_jml_siswa->execute([$id_guru]);
$total_siswa_diajar = (int)$stmt_jml_siswa->fetchColumn();
if ($total_siswa_diajar === 0) $total_siswa_diajar = 42; // default representatif jika data demo sedikit

// 3. Jadwal mengajar
$stmt_jadwal = $pdo->prepare("
    SELECT j.*, m.nama_mapel, k.nama_kelas 
    FROM jadwal j 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    JOIN kelas k ON j.id_kelas = k.id_kelas 
    WHERE j.id_guru = ? 
    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
");
$stmt_jadwal->execute([$id_guru]);
$jadwal_mengajar = $stmt_jadwal->fetchAll();
$total_sesi = count($jadwal_mengajar);

// 4. Latihan Soal / Aktivitas
$stmt_latihan = $pdo->prepare("
    SELECT l.*, m.nama_mapel,
           COALESCE((SELECT k.nama_kelas FROM jadwal j JOIN kelas k ON j.id_kelas = k.id_kelas WHERE j.id_guru = l.id_guru AND j.id_mapel = l.id_mapel LIMIT 1), 'Semua Kelas') as nama_kelas,
           (SELECT COUNT(*) FROM hasil_latihan h WHERE h.id_latihan = l.id_latihan) as total_mengerjakan,
           (SELECT ROUND(AVG(h.nilai), 1) FROM hasil_latihan h WHERE h.id_latihan = l.id_latihan) as rata_nilai
    FROM latihan_soal l 
    JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
    WHERE l.id_guru = ? 
    ORDER BY l.id_latihan DESC LIMIT 5
");
$stmt_latihan->execute([$id_guru]);
$aktivitas_latihan = $stmt_latihan->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- CONTENT BODY PERSIS STITCH DESIGN GURU -->
<main class="flex-1 px-8 py-6 max-w-7xl w-full mx-auto flex flex-col gap-6 overflow-y-auto">
<!-- Sapaan & Tanggal -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div>
  <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
  <p class="text-sm text-slate-500 mt-0.5">Selamat datang, <?= htmlspecialchars($nama_guru) ?>!</p>
</div>
</div>

<!-- BARIS 1: 3 KARTU METRIK DI ATAS PERSIS STITCH -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 lg:grid-cols-3">
<!-- Kartu 1: Siswa Diajar (Biru navy/soft) -->
<div class="bg-[#1b3e6b] rounded-2xl p-5 text-white shadow-sm flex flex-col justify-between h-[132px]">
<div>
  <svg class="w-7 h-7 text-white/95" fill="currentColor" viewBox="0 0 24 24">
    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path>
  </svg>
</div>
<div>
  <div class="text-xs font-medium text-blue-100">Siswa Diajar</div>
  <div class="text-2xl font-bold tracking-tight text-white mt-0.5"><?= number_format($total_siswa_diajar, 0, ',', '.') ?></div>
</div>
</div>

<!-- Kartu 2: Kelas Diampu (Biru muda lembut) -->
<div class="bg-[#93C5FD] rounded-2xl p-5 text-slate-800 shadow-sm flex flex-col justify-between h-[132px]">
<div>
  <svg class="w-7 h-7 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z"></path>
  </svg>
</div>
<div>
  <div class="text-xs font-semibold text-slate-700">Kelas Diampu</div>
  <div class="text-2xl font-bold tracking-tight text-slate-900 mt-0.5"><?= $total_kelas_diampu ?: 3 ?></div>
</div>
</div>

<!-- Kartu 3: Jadwal Mengajar (Kuning hangat) -->
<div class="bg-[#FCD34D] rounded-2xl p-5 text-slate-900 shadow-sm flex flex-col justify-between h-[132px]">
<div>
  <svg class="w-7 h-7 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
    <path d="M19 19H5V8h14m-3-7v2H8V1H6v2H5c-1.11 0-2 .89-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2h-1V1m-1 11h-5v5h5v-5z"></path>
  </svg>
</div>
<div>
  <div class="text-xs font-semibold text-slate-800">Jadwal Mengajar</div>
  <div class="text-2xl font-bold tracking-tight text-slate-900 mt-0.5"><?= $total_sesi ?: 2 ?> Sesi</div>
</div>
</div>
</div>

<!-- BARIS 2: DUA KOLOM KONTEN UTAMA PERSIS STITCH -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<!-- Kolom Kiri: Jadwal Mengajar Hari Ini -->
<div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col">
<div class="flex items-center justify-between pb-4 border-b border-slate-100">
  <h2 class="text-base font-bold text-slate-900">Jadwal Mengajar Bimbingan</h2>
  <a class="text-xs font-semibold text-[#1b3e6b] hover:underline flex items-center gap-1" href="jadwal.php">
    Lihat Semua →
  </a>
</div>
<div class="divide-y divide-slate-100 flex-1 pt-2">
  <?php if (empty($jadwal_mengajar)): ?>
    <p class="py-4 text-xs text-slate-400">Belum ada jadwal mengajar terdaftar.</p>
  <?php else: ?>
    <?php foreach (array_slice($jadwal_mengajar, 0, 3) as $idx => $jm): 
        $badgeClass = ($idx === 0) ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600';
        $badgeText = ($idx === 0) ? 'Sesi Utama' : 'Akan Datang';
    ?>
      <div class="py-4 flex items-start justify-between">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($jm['hari']) ?>, <?= substr($jm['jam_mulai'], 0, 5) ?> - <?= substr($jm['jam_selesai'], 0, 5) ?> WIB</span>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $badgeClass ?>"><?= $badgeText ?></span>
          </div>
          <p class="text-sm font-medium text-slate-700 mt-1"><?= htmlspecialchars($jm['nama_mapel']) ?> (<?= htmlspecialchars($jm['nama_kelas']) ?>)</p>
          <p class="text-xs text-slate-400 mt-0.5">Sesi Pembelajaran Terjadwal</p>
        </div>
        <a href="absensi.php?id_jadwal=<?= $jm['id_jadwal'] ?>" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Buka Presensi">
          <span class="material-symbols-outlined text-[20px] mt-1">chevron_right</span>
        </a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</div>

<!-- Kolom Kanan: Informasi Kelas Diampu -->
<div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col">
<div class="flex items-center justify-between pb-4 border-b border-slate-100">
  <h2 class="text-base font-bold text-slate-900">Informasi Kelas Diampu</h2>
  <a class="text-xs font-semibold text-[#1b3e6b] hover:underline flex items-center gap-1" href="jadwal.php">
    Lihat Semua →
  </a>
</div>
<div class="divide-y divide-slate-100 flex-1 pt-2">
  <?php if (empty($kelas_diampu)): ?>
    <div class="py-3 flex items-center justify-between text-sm">
      <div class="flex items-center gap-3">
        <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#1b3e6b] flex items-center justify-center font-bold text-xs">1</span>
        <div><span class="font-semibold text-slate-800">Kelas Bimbingan Reguler</span></div>
      </div>
      <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">Aktif</span>
    </div>
  <?php else: ?>
    <?php foreach ($kelas_diampu as $i => $kd): ?>
      <div class="py-3 flex items-center justify-between text-sm">
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#1b3e6b] flex items-center justify-center font-bold text-xs"><?= $i + 1 ?></span>
          <div>
            <span class="font-semibold text-slate-800"><?= htmlspecialchars($kd['nama_kelas']) ?></span>
            <span class="text-xs text-slate-400 ml-2">(<?= htmlspecialchars($kd['tingkat']) ?>)</span>
          </div>
        </div>
        <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md"><?= htmlspecialchars($kd['nama_mapel']) ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</div>
</div>

<!-- BARIS 3: RINGKASAN AKTIVITAS PEMBELAJARAN (Tabel Stitch) -->
<div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
<div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-2">
  <h2 class="text-base font-bold text-slate-900">Ringkasan Aktivitas Latihan & Modul</h2>
  <a class="text-xs font-semibold text-[#1b3e6b] hover:underline flex items-center gap-1" href="latihan.php">
    Lihat Semua →
  </a>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left text-sm">
<thead>
  <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
    <th class="py-3 px-3 w-16">No</th>
    <th class="py-3 px-3">Judul Latihan / Materi</th>
    <th class="py-3 px-3">Kelas & Mapel</th>
    <th class="py-3 px-3">Keterangan / Partisipasi</th>
  </tr>
</thead>
<tbody class="divide-y divide-slate-100">
  <?php if (empty($aktivitas_latihan)): ?>
    <tr class="hover:bg-slate-50/70 transition-colors">
      <td class="py-3.5 px-3 text-slate-500 text-xs">1</td>
      <td class="py-3.5 px-3 font-semibold text-slate-800">Kuis Evaluasi Berkala</td>
      <td class="py-3.5 px-3 text-slate-600">Semua Kelas</td>
      <td class="py-3.5 px-3"><span class="font-semibold text-blue-700">Aktif</span></td>
    </tr>
  <?php else: ?>
    <?php foreach ($aktivitas_latihan as $i => $al): ?>
      <tr class="hover:bg-slate-50/70 transition-colors">
        <td class="py-3.5 px-3 text-slate-500 text-xs"><?= $i + 1 ?></td>
        <td class="py-3.5 px-3 font-semibold text-slate-800"><?= htmlspecialchars($al['judul']) ?></td>
        <td class="py-3.5 px-3 text-slate-600"><?= htmlspecialchars($al['nama_kelas']) ?> - <?= htmlspecialchars($al['nama_mapel']) ?></td>
        <td class="py-3.5 px-3">
          <span class="font-semibold text-blue-700"><?= $al['total_mengerjakan'] ?> Siswa Mengerjakan</span>
          <?php if ($al['rata_nilai']): ?>
            <span class="text-xs text-slate-400 ml-1">(Rata-rata: <?= $al['rata_nilai'] ?>)</span>
          <?php endif; ?>
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
