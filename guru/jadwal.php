<?php
// guru/jadwal.php - Jadwal Mengajar Guru
require_once __DIR__ . '/../config/database.php';
check_access(['Guru']);

$page_title = "Jadwal Mengajar";
$current_page = "jadwal";

$user = get_current_user_data();
$id_guru = (int)($user['id_guru'] ?? $_SESSION['guru']['id_guru'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Tambah / Edit Jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit') {
        $id_jadwal = (int)($_POST['id_jadwal'] ?? 0);
        $id_mapel = (int)($_POST['id_mapel'] ?? 0);
        $id_kelas = (int)($_POST['id_kelas'] ?? 0);
        $hari = db_escape($_POST['hari'] ?? 'Senin');
        $jam_mulai = db_escape($_POST['jam_mulai'] ?? '08:00');
        $jam_selesai = db_escape($_POST['jam_selesai'] ?? '09:30');
        $tanggal_belajar = db_escape($_POST['tanggal_belajar'] ?? date('Y-m-d'));

        if ($id_mapel && $id_kelas) {
            if ($_POST['action'] === 'tambah') {
                $sql = "INSERT INTO jadwal (id_guru, id_mapel, id_kelas, hari, jam_mulai, jam_selesai, tanggal_belajar) 
                        VALUES ($id_guru, $id_mapel, $id_kelas, '$hari', '$jam_mulai', '$jam_selesai', '$tanggal_belajar')";
                if (db_query($sql)) {
                    $alert_msg = "Jadwal mengajar berhasil ditambahkan.";
                } else {
                    $alert_msg = "Gagal menambahkan jadwal.";
                    $alert_type = "danger";
                }
            } else {
                $sql = "UPDATE jadwal SET id_mapel = $id_mapel, id_kelas = $id_kelas, hari = '$hari', 
                               jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai', tanggal_belajar = '$tanggal_belajar'
                        WHERE id_jadwal = $id_jadwal AND id_guru = $id_guru";
                if (db_query($sql)) {
                    $alert_msg = "Jadwal mengajar berhasil diperbarui.";
                } else {
                    $alert_msg = "Gagal memperbarui jadwal.";
                    $alert_type = "danger";
                }
            }
        }
    } elseif ($_POST['action'] === 'hapus') {
        $id_jadwal = (int)($_POST['id_jadwal'] ?? 0);
        db_query("DELETE FROM jadwal WHERE id_jadwal = $id_jadwal AND id_guru = $id_guru");
        $alert_msg = "Jadwal mengajar telah dihapus.";
    }
}

// Available filter day
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$filter_hari = db_escape($_GET['hari'] ?? 'all');
$search = db_escape(trim($_GET['q'] ?? ''));

$where = "WHERE j.id_guru = $id_guru";
if ($filter_hari !== 'all' && in_array($filter_hari, $hari_list)) {
    $where .= " AND j.hari = '$filter_hari'";
}
if (!empty($search)) {
    $where .= " AND (m.nama_mapel LIKE '%$search%' OR k.nama_kelas LIKE '%$search%' OR j.hari LIKE '%$search%')";
}

$jadwal_list = db_fetch_all("SELECT j.*, m.nama_mapel, k.nama_kelas, k.tingkat 
                             FROM jadwal j 
                             JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
                             LEFT JOIN kelas k ON j.id_kelas = k.id_kelas 
                             $where 
                             ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC");

$mapel_options = db_fetch_all("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");
$kelas_options = db_fetch_all("SELECT * FROM kelas ORDER BY tingkat ASC, nama_kelas ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="flex-1 px-4 sm:px-8 py-6 w-full flex flex-col gap-6 overflow-y-auto">
        <?php if (!empty($alert_msg)): ?>
            <div class="p-4 rounded-xl text-sm font-medium <?= $alert_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> flex items-center justify-between">
                <span><?= htmlspecialchars($alert_msg) ?></span>
                <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold px-2 py-1">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Judul & Breadcrumb + Quick Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-gray-800">Jadwal Mengajar</h1>
                <p class="text-sm text-gray-500 mt-1">
                    <a href="index.php" class="hover:text-blue-600 transition">Dashboard</a> &gt; 
                    <span class="text-slate-700 font-medium">Jadwal Mengajar</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openModalTambah()" class="inline-flex items-center gap-1.5 bg-[#19376D] hover:bg-blue-800 text-white rounded-lg px-4 py-2 text-sm font-medium shadow-sm transition-all" type="button">
                    <span class="text-base font-bold leading-none">+</span>
                    <span>Tambah Jadwal</span>
                </button>
            </div>
        </div>

        <!-- Kartu Pilih Hari Mengajar (7 Hari Navigation) -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-900 text-sm">Pilih Hari Mengajar</h3>
                    <span class="text-xs text-slate-400 font-medium">(Mingguan)</span>
                </div>
                <a href="jadwal.php?hari=all" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1.5 transition-colors">
                    <span>Lihat Semua Hari</span>
                    <span>&rarr;</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2.5 flex-1">
                    <a href="jadwal.php?hari=all" class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl border transition-all text-center <?= ($filter_hari === 'all') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 border-blue-600' : 'border-slate-200/80 bg-slate-50/50 hover:bg-slate-100 text-slate-600' ?>">
                        <span class="text-[11px] font-medium uppercase tracking-wider <?= ($filter_hari === 'all') ? 'text-blue-100' : 'text-slate-500' ?>">Semua</span>
                        <span class="text-sm font-bold mt-0.5">Hari</span>
                    </a>
                    <?php 
                    $abbr = ['Senin'=>'Sen', 'Selasa'=>'Sel', 'Rabu'=>'Rab', 'Kamis'=>'Kam', 'Jumat'=>'Jum', 'Sabtu'=>'Sab', 'Minggu'=>'Min'];
                    foreach ($hari_list as $h): 
                        $isActive = ($filter_hari === $h);
                        $isSun = ($h === 'Minggu');
                    ?>
                        <a href="jadwal.php?hari=<?= $h ?>" class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl border transition-all text-center <?= $isActive ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 border-blue-600' : 'border-slate-200/80 bg-slate-50/50 hover:bg-slate-100 text-slate-600' ?>">
                            <span class="text-[11px] font-medium uppercase tracking-wider <?= $isActive ? 'text-blue-100' : ($isSun ? 'text-red-500' : 'text-slate-500') ?>"><?= $abbr[$h] ?></span>
                            <span class="text-sm font-bold mt-0.5 <?= $isActive ? 'text-white' : ($isSun ? 'text-red-500' : 'text-slate-800') ?>"><?= substr($h, 0, 3) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Kartu Utama "Sesi Jadwal Hari Ini" & Tabel Sesuai Design -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                    <h2 class="text-sm font-bold text-slate-900">
                        <?= ($filter_hari === 'all') ? 'Semua Sesi Mengajar' : 'Sesi Jadwal Hari ' . htmlspecialchars($filter_hari) ?>
                    </h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                        <?= count($jadwal_list) ?> Sesi Terdaftar
                    </span>
                </div>
                <form method="GET" action="jadwal.php" class="flex items-center gap-3">
                    <?php if ($filter_hari !== 'all'): ?>
                        <input type="hidden" name="hari" value="<?= htmlspecialchars($filter_hari) ?>">
                    <?php endif; ?>
                    <div class="relative min-w-[240px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                        <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-9 pr-3 py-2 bg-slate-50 rounded-lg text-xs text-slate-700 placeholder:text-slate-400 border border-slate-200 focus:outline-none focus:border-blue-600 focus:bg-white transition" placeholder="Cari sesi / kelas..." type="text">
                    </div>
                </form>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/70">
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">HARI &amp; JAM</th>
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">KELAS</th>
                            <th class="py-4 px-6 sm:px-8 font-medium" scope="col">MATA PELAJARAN</th>
                            <th class="py-4 px-6 sm:px-8 font-medium text-center" scope="col">STATUS</th>
                            <th class="py-4 px-6 sm:px-8 font-medium text-center" scope="col">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($jadwal_list)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">calendar_today</span>
                                    Belum ada sesi jadwal mengajar yang terdaftar pada filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $curr_day = date('l');
                            $day_map = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
                            $today_indo = $day_map[$curr_day] ?? '';
                            $curr_time = date('H:i:s');

                            foreach ($jadwal_list as $j): 
                                $isToday = ($j['hari'] === $today_indo);
                                $isOngoing = $isToday && ($curr_time >= $j['jam_mulai'] && $curr_time <= $j['jam_selesai']);
                                $isPast = ($isToday && $curr_time > $j['jam_selesai']);
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-5 px-6 sm:px-8">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 text-xs">
                                                <?= htmlspecialchars($j['hari']) ?>, <?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?>
                                            </span>
                                            <span class="text-[11px] text-slate-400 mt-0.5">
                                                <?= htmlspecialchars(format_tanggal_indo($j['tanggal_belajar'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8">
                                        <span class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($j['nama_kelas'] ?? 'Semua Kelas') ?></span>
                                        <?php if (!empty($j['tingkat'])): ?>
                                            <span class="block text-[11px] text-slate-400"><?= htmlspecialchars($j['tingkat']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8">
                                        <span class="font-medium text-slate-800 text-xs"><?= htmlspecialchars($j['nama_mapel']) ?></span>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8 text-center">
                                        <?php if ($isOngoing): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Berlangsung</span>
                                            </span>
                                        <?php elseif ($isPast): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                <span>Selesai</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <span>Akan Datang</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-5 px-6 sm:px-8 text-center">
                                        <div class="inline-flex items-center justify-center gap-2">
                                            <a href="absensi.php?id_jadwal=<?= $j['id_jadwal'] ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-xs">
                                                <span class="material-symbols-outlined text-xs">fact_check</span>
                                                <span>Presensi</span>
                                            </a>
                                            <button onclick='openModalEdit(<?= json_encode($j) ?>)' class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition" title="Edit Jadwal" type="button">
                                                <span class="material-symbols-outlined text-base leading-none">edit</span>
                                            </button>
                                            <form method="POST" action="jadwal.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');" class="inline">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_jadwal" value="<?= $j['id_jadwal'] ?>">
                                                <button type="submit" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-red-600 hover:bg-slate-50 transition" title="Hapus Jadwal">
                                                    <span class="material-symbols-outlined text-base leading-none">delete</span>
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
            <div class="px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 bg-white text-xs text-slate-500">
                <p>
                    Menampilkan <span class="font-semibold text-slate-700">1–<?= count($jadwal_list) ?></span> dari <span class="font-semibold text-slate-700"><?= count($jadwal_list) ?></span> sesi
                </p>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-600 text-white text-xs font-bold shadow-xs" type="button">1</button>
                </div>
            </div>
        </div>

        <!-- Bottom Note / Sinkronisasi Jadwal -->
        <div class="rounded-2xl bg-white border border-slate-200/70 p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">info</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-800">Informasi Sinkronisasi Jadwal</span>
                    <span class="text-xs text-slate-500">Perubahan ruangan atau jam mengajar memerlukan persetujuan cabang pusat minimal 24 jam sebelum sesi dimulai.</span>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button onclick="window.print()" class="px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold rounded-xl transition" type="button">
                    Cetak Jadwal (PDF)
                </button>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<!-- Modal Tambah / Edit Jadwal -->
<div id="modalJadwal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 id="modalTitle" class="text-base font-bold text-slate-900">Tambah Jadwal Mengajar</h3>
            <button type="button" onclick="closeModalJadwal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="jadwal.php" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="tambah">
            <input type="hidden" name="id_jadwal" id="formIdJadwal" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                <select name="id_mapel" id="formIdMapel" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($mapel_options as $m): ?>
                        <option value="<?= $m['id_mapel'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                <select name="id_kelas" id="formIdKelas" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_options as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['tingkat']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Hari <span class="text-red-500">*</span></label>
                    <select name="hari" id="formHari" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                        <?php foreach ($hari_list as $h): ?>
                            <option value="<?= $h ?>"><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Belajar</label>
                    <input type="date" name="tanggal_belajar" id="formTanggal" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" id="formJamMulai" value="08:00" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" id="formJamSelesai" value="09:30" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalJadwal()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalTitle').textContent = 'Tambah Jadwal Mengajar';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('formIdJadwal').value = '';
    document.getElementById('formIdMapel').value = '';
    document.getElementById('formIdKelas').value = '';
    document.getElementById('formHari').value = 'Senin';
    document.getElementById('formJamMulai').value = '08:00';
    document.getElementById('formJamSelesai').value = '09:30';
    document.getElementById('modalJadwal').classList.remove('hidden');
}

function openModalEdit(j) {
    document.getElementById('modalTitle').textContent = 'Edit Jadwal Mengajar';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formIdJadwal').value = j.id_jadwal;
    document.getElementById('formIdMapel').value = j.id_mapel;
    document.getElementById('formIdKelas').value = j.id_kelas;
    document.getElementById('formHari').value = j.hari;
    document.getElementById('formTanggal').value = j.tanggal_belajar;
    document.getElementById('formJamMulai').value = j.jam_mulai.substring(0, 5);
    document.getElementById('formJamSelesai').value = j.jam_selesai.substring(0, 5);
    document.getElementById('modalJadwal').classList.remove('hidden');
}

function closeModalJadwal() {
    document.getElementById('modalJadwal').classList.add('hidden');
}
</script>
