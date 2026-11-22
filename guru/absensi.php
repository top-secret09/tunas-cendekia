<?php
require_once __DIR__ . '/../config/database.php';
require_role('guru');

$page_title = "Absensi Siswa";
$current_page = "absensi";

$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);
$msg = '';
$err = '';

// Ambil daftar kelas yang diajar guru ini
$stmt_kelas = $pdo->prepare("
    SELECT DISTINCT k.id_kelas, k.nama_kelas, k.tingkat, m.nama_mapel, j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai
    FROM jadwal j 
    JOIN kelas k ON j.id_kelas = k.id_kelas 
    JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
    WHERE j.id_guru = ?
");
$stmt_kelas->execute([$id_guru]);
$daftar_kelas = $stmt_kelas->fetchAll();

$id_jadwal_selected = (int)($_GET['id_jadwal'] ?? ($daftar_kelas[0]['id_jadwal'] ?? 0));
$tanggal_selected = $_GET['tanggal'] ?? date('Y-m-d');

// Cari detail jadwal terpilih
$jadwal_info = null;
foreach ($daftar_kelas as $k) {
    if ($k['id_jadwal'] == $id_jadwal_selected) {
        $jadwal_info = $k;
        break;
    }
}
if (!$jadwal_info && !empty($daftar_kelas)) {
    $jadwal_info = $daftar_kelas[0];
    $id_jadwal_selected = $jadwal_info['id_jadwal'];
}

// Handle Simpan Presensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_absensi') {
    $presensi_data = $_POST['status'] ?? [];
    $tgl = $_POST['tanggal'] ?? date('Y-m-d');
    $id_jdw = (int)($_POST['id_jadwal'] ?? $id_jadwal_selected);

    try {
        $pdo->beginTransaction();
        foreach ($presensi_data as $id_siswa => $st) {
            $id_siswa = (int)$id_siswa;
            // Cek apakah sudah ada record presensi untuk siswa & jadwal & tanggal ini
            $cek = $pdo->prepare("SELECT id_kehadiran FROM kehadiran WHERE id_jadwal = ? AND id_siswa = ? AND tanggal_kehadiran = ?");
            $cek->execute([$id_jdw, $id_siswa, $tgl]);
            $existing = $cek->fetchColumn();

            if ($existing) {
                $up = $pdo->prepare("UPDATE kehadiran SET status_kehadiran = ? WHERE id_kehadiran = ?");
                $up->execute([$st, $existing]);
            } else {
                $ins = $pdo->prepare("INSERT INTO kehadiran (id_jadwal, id_siswa, tanggal_kehadiran, jam_masuk, status_kehadiran) VALUES (?, ?, ?, CURTIME(), ?)");
                $ins->execute([$id_jdw, $id_siswa, $tgl, $st]);
            }
        }
        $pdo->commit();
        $msg = "Presensi siswa berhasil disimpan!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $err = "Gagal menyimpan presensi: " . $e->getMessage();
    }
}

// Ambil daftar siswa yang terdaftar di kelas jadwal ini
$siswa_list = [];
if ($jadwal_info) {
    $stmt_siswa = $pdo->prepare("
        SELECT s.id_siswa, s.nama_siswa, s.jenis_Kelamin, 
               COALESCE((SELECT kh.status_kehadiran FROM kehadiran kh WHERE kh.id_siswa = s.id_siswa AND kh.id_jadwal = ? AND kh.tanggal_kehadiran = ? LIMIT 1), 'Hadir') as status_kehadiran
        FROM siswa s 
        JOIN pendaftaran p ON s.id_siswa = p.id_siswa 
        WHERE p.id_kelas = ?
        ORDER BY s.nama_siswa ASC
    ");
    $stmt_siswa->execute([$id_jadwal_selected, $tanggal_selected, $jadwal_info['id_kelas']]);
    $siswa_list = $stmt_siswa->fetchAll();
}

// Hitung rekap statistik hadir, izin, sakit, alpa
$total_siswa = count($siswa_list);
$cnt_hadir = 0; $cnt_izin = 0; $cnt_sakit = 0; $cnt_alpa = 0;
foreach ($siswa_list as $sw) {
    if ($sw['status_kehadiran'] === 'Hadir') $cnt_hadir++;
    elseif ($sw['status_kehadiran'] === 'Izin') $cnt_izin++;
    elseif ($sw['status_kehadiran'] === 'Sakit') $cnt_sakit++;
    elseif ($sw['status_kehadiran'] === 'Alpa') $cnt_alpa++;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
<?php include __DIR__ . '/../includes/topbar.php'; ?>

<!-- BEGIN: MainContent Persis Stitch Absensi Siswa -->
<main class="flex-1 p-8 space-y-6 overflow-y-auto" data-purpose="absensi-main-content">
  <!-- Top Header & Action Buttons -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Absensi Siswa</h1>
      <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
        <a class="hover:text-blue-600 transition-colors" href="index.php">Dashboard</a>
        <span class="text-slate-400">&gt;</span>
        <span class="text-slate-700 font-medium">Absensi</span>
      </p>
    </div>
    <div class="flex items-center gap-2.5">
      <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold shadow-xs hover:bg-slate-50 transition-colors" onclick="window.print()" type="button">
        <span class="material-symbols-outlined text-base text-slate-500">file_download</span>
        <span>Export Presensi</span>
      </button>
      <button class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#143d73] text-white rounded-lg text-xs font-semibold shadow-sm hover:bg-[#0f2e57] transition-colors" onclick="document.getElementById('formPresensi').submit()" type="button">
        <span class="material-symbols-outlined text-base">save</span>
        <span>Simpan Presensi</span>
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

  <!-- Select Class & Schedule Bar Persis Stitch -->
  <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <form method="GET" action="absensi.php" class="flex flex-col sm:flex-row items-center gap-3 flex-1" id="filterAbsensi">
      <div class="w-full sm:w-64 relative">
        <label class="text-xs font-medium text-slate-500 block mb-1">Pilih Sesi Kelas</label>
        <div class="relative">
          <select class="w-full appearance-none bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 pr-8 text-xs text-slate-800 focus:outline-none focus:border-blue-600 font-semibold cursor-pointer" name="id_jadwal" onchange="this.form.submit()">
            <?php foreach ($daftar_kelas as $dk): ?>
              <option value="<?= $dk['id_jadwal'] ?>" <?= $dk['id_jadwal'] == $id_jadwal_selected ? 'selected' : '' ?>>
                <?= htmlspecialchars($dk['nama_kelas']) ?> (<?= htmlspecialchars($dk['nama_mapel']) ?>) - <?= $dk['hari'] ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span class="material-symbols-outlined text-base text-slate-400 absolute right-2.5 top-2.5 pointer-events-none">expand_more</span>
        </div>
      </div>
      <div class="w-full sm:w-56 relative">
        <label class="text-xs font-medium text-slate-500 block mb-1">Tanggal Presensi</label>
        <input class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 font-semibold cursor-pointer" type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_selected) ?>" onchange="this.form.submit()">
      </div>
    </form>
    <?php if ($jadwal_info): ?>
      <div class="flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 shrink-0">
        <span class="material-symbols-outlined text-base text-blue-600">event_available</span>
        <span>Mata Pelajaran: <strong class="text-slate-900 font-bold"><?= htmlspecialchars($jadwal_info['nama_mapel']) ?></strong> (<?= substr($jadwal_info['jam_mulai'], 0, 5) ?> - <?= substr($jadwal_info['jam_selesai'], 0, 5) ?> WIB)</span>
      </div>
    <?php endif; ?>
  </div>

  <!-- 5 Metric Counters Persis Stitch -->
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    <!-- Total Siswa -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 shadow-sm flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
        <span class="material-symbols-outlined text-xl">groups</span>
      </div>
      <div class="flex flex-col">
        <span class="text-xs font-medium text-slate-500">Total Siswa</span>
        <span class="text-lg font-bold text-slate-900 leading-tight mt-0.5"><?= $total_siswa ?> Siswa</span>
      </div>
    </div>
    <!-- Hadir -->
    <div class="bg-emerald-50/40 border border-emerald-300 rounded-xl p-3.5 shadow-sm flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 border border-emerald-200">
        <span class="material-symbols-outlined text-xl">check_circle</span>
      </div>
      <div class="flex flex-col">
        <span class="text-xs font-medium text-emerald-700">Hadir</span>
        <span class="text-lg font-bold text-emerald-700 leading-tight mt-0.5" id="countHadir"><?= $cnt_hadir ?> Siswa</span>
      </div>
    </div>
    <!-- Izin -->
    <div class="bg-blue-50/40 border border-blue-200 rounded-xl p-3.5 shadow-sm flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 shrink-0">
        <span class="material-symbols-outlined text-xl">assignment</span>
      </div>
      <div class="flex flex-col">
        <span class="text-xs font-medium text-blue-700">Izin</span>
        <span class="text-lg font-bold text-blue-700 leading-tight mt-0.5" id="countIzin"><?= $cnt_izin ?> Siswa</span>
      </div>
    </div>
    <!-- Sakit -->
    <div class="bg-amber-50/40 border border-amber-300 rounded-xl p-3.5 shadow-sm flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shrink-0 border border-amber-200">
        <span class="material-symbols-outlined text-xl">medical_services</span>
      </div>
      <div class="flex flex-col">
        <span class="text-xs font-medium text-amber-700">Sakit</span>
        <span class="text-lg font-bold text-amber-700 leading-tight mt-0.5" id="countSakit"><?= $cnt_sakit ?> Siswa</span>
      </div>
    </div>
    <!-- Alpa -->
    <div class="col-span-2 md:col-span-1 bg-rose-50/40 border border-rose-300 rounded-xl p-3.5 shadow-sm flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 shrink-0 border border-rose-200">
        <span class="material-symbols-outlined text-xl">cancel</span>
      </div>
      <div class="flex flex-col">
        <span class="text-xs font-medium text-rose-700">Alpa</span>
        <span class="text-lg font-bold text-rose-700 leading-tight mt-0.5" id="countAlpa"><?= $cnt_alpa ?> Siswa</span>
      </div>
    </div>
  </div>

  <!-- Table Absensi Persis Stitch -->
  <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-800">Daftar Kehadiran Siswa</span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" onclick="markAll('Hadir')" class="px-3 py-1.5 bg-emerald-50 border border-emerald-300 hover:bg-emerald-100 text-xs font-semibold text-emerald-700 rounded-lg flex items-center gap-1.5 transition-colors shadow-xs">
          <span class="material-symbols-outlined text-sm text-emerald-600">done_all</span>
          <span>Tandai Hadir Semua</span>
        </button>
      </div>
    </div>

    <form method="POST" action="absensi.php" id="formPresensi">
      <input type="hidden" name="action" value="save_absensi">
      <input type="hidden" name="id_jadwal" value="<?= $id_jadwal_selected ?>">
      <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal_selected) ?>">

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">NO</th>
              <th class="py-3 px-4 w-24">NIS / ID</th>
              <th class="py-3 px-4">NAMA SISWA</th>
              <th class="py-3 px-4">JENIS KELAMIN</th>
              <th class="py-3 px-4 text-center">STATUS KEHADIRAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($siswa_list)): ?>
              <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada siswa terdaftar di kelas ini.</td></tr>
            <?php else: ?>
              <?php foreach ($siswa_list as $i => $sw): 
                  $currentSt = $sw['status_kehadiran'];
              ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3.5 px-4 text-center text-slate-500 font-medium"><?= $i + 1 ?></td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">S<?= str_pad($sw['id_siswa'], 3, '0', STR_PAD_LEFT) ?></td>
                  <td class="py-3.5 px-4 font-semibold text-slate-900"><?= htmlspecialchars($sw['nama_siswa']) ?></td>
                  <td class="py-3.5 px-4 capitalize text-slate-600"><?= htmlspecialchars($sw['jenis_Kelamin']) ?></td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-lg border border-slate-200 gap-1">
                      <label class="cursor-pointer">
                        <input type="radio" name="status[<?= $sw['id_siswa'] ?>]" value="Hadir" class="sr-only status-radio" <?= $currentSt === 'Hadir' ? 'checked' : '' ?> onchange="updateCounts()">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all <?= $currentSt === 'Hadir' ? 'bg-emerald-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">Hadir</span>
                      </label>
                      <label class="cursor-pointer">
                        <input type="radio" name="status[<?= $sw['id_siswa'] ?>]" value="Izin" class="sr-only status-radio" <?= $currentSt === 'Izin' ? 'checked' : '' ?> onchange="updateCounts()">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all <?= $currentSt === 'Izin' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">Izin</span>
                      </label>
                      <label class="cursor-pointer">
                        <input type="radio" name="status[<?= $sw['id_siswa'] ?>]" value="Sakit" class="sr-only status-radio" <?= $currentSt === 'Sakit' ? 'checked' : '' ?> onchange="updateCounts()">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all <?= $currentSt === 'Sakit' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">Sakit</span>
                      </label>
                      <label class="cursor-pointer">
                        <input type="radio" name="status[<?= $sw['id_siswa'] ?>]" value="Alpa" class="sr-only status-radio" <?= $currentSt === 'Alpa' ? 'checked' : '' ?> onchange="updateCounts()">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all <?= $currentSt === 'Alpa' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">Alpa</span>
                      </label>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</main>

<script>
function markAll(status) {
  document.querySelectorAll('.status-radio').forEach(r => {
    if (r.value === status) {
      r.checked = true;
      r.dispatchEvent(new Event('change'));
    }
  });
}

function updateCounts() {
  let hadir = 0, izin = 0, sakit = 0, alpa = 0;
  document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
    if (r.value === 'Hadir') hadir++;
    else if (r.value === 'Izin') izin++;
    else if (r.value === 'Sakit') sakit++;
    else if (r.value === 'Alpa') alpa++;
  });
  document.getElementById('countHadir').innerText = hadir + ' Siswa';
  document.getElementById('countIzin').innerText = izin + ' Siswa';
  document.getElementById('countSakit').innerText = sakit + ' Siswa';
  document.getElementById('countAlpa').innerText = alpa + ' Siswa';

  // Update button highlights
  document.querySelectorAll('label').forEach(lbl => {
    const radio = lbl.querySelector('input[type="radio"]');
    const span = lbl.querySelector('span');
    if (radio && span) {
      if (radio.checked) {
        if (radio.value === 'Hadir') span.className = 'px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all bg-emerald-500 text-white shadow-xs';
        else if (radio.value === 'Izin') span.className = 'px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all bg-blue-600 text-white shadow-xs';
        else if (radio.value === 'Sakit') span.className = 'px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all bg-amber-500 text-white shadow-xs';
        else if (radio.value === 'Alpa') span.className = 'px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all bg-rose-600 text-white shadow-xs';
      } else {
        span.className = 'px-2.5 py-1 rounded text-xs font-semibold inline-block transition-all text-slate-600 hover:text-slate-900';
      }
    }
  });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
