<?php
// siswa/kelas.php - Kelas Saya
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Kelas Saya";
$current_page = "kelas";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Pendaftaran Kelas Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'daftar_kelas') {
    $id_kelas = (int)($_POST['id_kelas'] ?? 0);
    if ($id_kelas > 0) {
        $cek = db_fetch_one("SELECT id_pendaftaran FROM pendaftaran WHERE id_siswa = $id_siswa AND id_kelas = $id_kelas");
        if ($cek) {
            $alert_msg = "Anda sudah pernah mendaftar di kelas ini.";
            $alert_type = "danger";
        } else {
            $tgl = date('Y-m-d');
            $q = db_query("INSERT INTO pendaftaran (id_siswa, id_kelas, tanggal_daftar, status_pendaftaran) 
                           VALUES ($id_siswa, $id_kelas, '$tgl', 'Menunggu')");
            if ($q) {
                $alert_msg = "Pendaftaran kelas baru berhasil dikirim. Menunggu verifikasi admin.";
            } else {
                $alert_msg = "Gagal mendaftar kelas.";
                $alert_type = "danger";
            }
        }
    } else {
        $alert_msg = "Silakan pilih kelas terlebih dahulu.";
        $alert_type = "danger";
    }
}

// Search & Filter
$search = db_escape(trim($_GET['q'] ?? ''));
$filter_tingkat = db_escape(trim($_GET['tingkat'] ?? 'all'));

$where_clauses = ["pd.id_siswa = $id_siswa"];
if (!empty($search)) {
    $where_clauses[] = "(k.nama_kelas LIKE '%$search%' OR k.tingkat LIKE '%$search%' OR g.nama_guru LIKE '%$search%')";
}
if ($filter_tingkat !== 'all' && !empty($filter_tingkat)) {
    $where_clauses[] = "k.tingkat = '$filter_tingkat'";
}
$where_sql = implode(' AND ', $where_clauses);

$kelas_list = db_fetch_all("SELECT pd.*, k.nama_kelas, k.tingkat, k.biaya, k.deskripsi, 
                                   p.status_pembayaran, p.id_pembayaran,
                                   (SELECT g.nama_guru FROM jadwal j JOIN guru g ON j.id_guru = g.id_guru WHERE j.id_kelas = k.id_kelas LIMIT 1) as nama_guru,
                                   (SELECT m.nama_mapel FROM jadwal j JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel WHERE j.id_kelas = k.id_kelas LIMIT 1) as nama_mapel
                            FROM pendaftaran pd 
                            JOIN kelas k ON pd.id_kelas = k.id_kelas 
                            LEFT JOIN pembayaran p ON pd.id_pendaftaran = p.id_pendaftaran 
                            LEFT JOIN jadwal j ON k.id_kelas = j.id_kelas
                            LEFT JOIN guru g ON j.id_guru = g.id_guru
                            WHERE $where_sql 
                            GROUP BY pd.id_pendaftaran 
                            ORDER BY pd.id_pendaftaran DESC");

$kelas_tersedia = db_fetch_all("SELECT * FROM kelas 
                                WHERE status_kelas = 'Aktif' 
                                AND id_kelas NOT IN (SELECT id_kelas FROM pendaftaran WHERE id_siswa = $id_siswa) 
                                ORDER BY biaya ASC");

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

        <!-- Header Atas Minimalis -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Kelas Saya</h1>
                <div class="flex items-center gap-2 text-xs text-slate-500 mt-1 font-medium">
                    <a class="hover:text-blue-600 transition" href="index.php">Dashboard</a>
                    <span class="text-xs font-semibold">&gt;</span>
                    <span class="text-slate-700 font-semibold">Kelas Saya</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <?php if (!empty($kelas_tersedia)): ?>
                    <button onclick="openModalDaftar()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#19376D] hover:bg-blue-800 text-white text-xs font-semibold transition-all shadow-xs" type="button">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Daftar Kelas Baru</span>
                    </button>
                <?php endif; ?>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-all shadow-xs" type="button">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Unduh Daftar Kelas (PDF)</span>
                </button>
            </div>
        </div>

        <!-- Bar Filter & Pencarian Simpel -->
        <form method="GET" action="kelas.php" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                    <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full pl-9 pr-4 py-2 bg-slate-50 rounded-lg text-xs text-slate-700 placeholder:text-slate-400 border border-slate-200 focus:outline-none focus:border-blue-600 focus:bg-white transition" placeholder="Cari kelas, program, atau pengajar..." type="text">
                </div>
                <div class="relative">
                    <select name="tingkat" onchange="this.form.submit()" class="appearance-none bg-slate-50 pl-3 pr-8 py-2 rounded-lg text-xs text-slate-700 border border-slate-200 focus:outline-none focus:border-blue-600 cursor-pointer">
                        <option value="all">Semua Jenjang</option>
                        <option value="SD" <?= ($filter_tingkat === 'SD') ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= ($filter_tingkat === 'SMP') ? 'selected' : '' ?>>SMP</option>
                        <option value="SMA" <?= ($filter_tingkat === 'SMA') ? 'selected' : '' ?>>SMA</option>
                        <option value="Alumni" <?= ($filter_tingkat === 'Alumni') ? 'selected' : '' ?>>Alumni</option>
                    </select>
                </div>
                <a href="kelas.php" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset</span>
                </a>
            </div>
            <div class="inline-flex items-center gap-1.5 self-start sm:self-center px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold shrink-0 border border-blue-200/60">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span><?= count($kelas_list) ?> Kelas Terdaftar</span>
            </div>
        </form>

        <!-- Tabel Kelas Minimalis & Bersih -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-3.5 px-6 font-medium text-center w-12">NO</th>
                            <th class="py-3.5 px-6 font-medium">INFORMASI KELAS</th>
                            <th class="py-3.5 px-6 font-medium">JENJANG</th>
                            <th class="py-3.5 px-6 font-medium">BIAYA BIMBEL</th>
                            <th class="py-3.5 px-6 font-medium">STATUS PENDAFTARAN</th>
                            <th class="py-3.5 px-6 font-medium">STATUS PEMBAYARAN</th>
                            <th class="py-3.5 px-6 font-medium text-center w-36">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($kelas_list)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">school</span>
                                    Anda belum terdaftar pada kelas bimbingan apapun. Klik tombol <strong>+ Daftar Kelas Baru</strong> untuk bergabung.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kelas_list as $idx => $k): 
                                $st_pendaftaran = $k['status_pendaftaran'];
                                $st_pembayaran = $k['status_pembayaran'] ?? 'Belum Lunas';
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-mono text-slate-400 font-medium"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($k['nama_kelas']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-normal"><?= htmlspecialchars($k['deskripsi'] ?? 'Program bimbingan intensif') ?></div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                            <?= htmlspecialchars($k['tingkat']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-slate-800">
                                        Rp <?= number_format($k['biaya'], 0, ',', '.') ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($st_pendaftaran === 'Diterima'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Diterima</span>
                                            </span>
                                        <?php elseif ($st_pendaftaran === 'Ditolak'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Menunggu</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($st_pembayaran === 'Lunas'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                Lunas
                                            </span>
                                        <?php else: ?>
                                            <a href="pembayaran.php" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">
                                                <span>Belum Lunas</span>
                                                <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <a href="jadwal.php" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition">
                                            <span class="material-symbols-outlined text-[15px]">calendar_month</span>
                                            <span>Jadwal</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-white flex items-center justify-between border-t border-slate-100 text-xs text-slate-500">
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($kelas_list) ?></span> kelas</span>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold flex items-center justify-center">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<!-- Modal Daftar Kelas Baru -->
<div id="modalDaftarKelas" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-bold text-slate-900">Daftar Kelas Bimbingan Baru</h3>
            <button type="button" onclick="closeModalDaftar()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="kelas.php" class="p-6 space-y-4">
            <input type="hidden" name="action" value="daftar_kelas">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Kelas Bimbingan <span class="text-red-500">*</span></label>
                <select name="id_kelas" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Kelas Tersedia --</option>
                    <?php foreach ($kelas_tersedia as $kt): ?>
                        <option value="<?= $kt['id_kelas'] ?>">
                            <?= htmlspecialchars($kt['nama_kelas']) ?> (<?= htmlspecialchars($kt['tingkat']) ?>) - Rp <?= number_format($kt['biaya'], 0, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pendaftaran kelas akan ditinjau oleh Admin dan invoice pembayaran otomatis diterbitkan.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalDaftar()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Kirim Pendaftaran</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalDaftar() {
    document.getElementById('modalDaftarKelas').classList.remove('hidden');
}
function closeModalDaftar() {
    document.getElementById('modalDaftarKelas').classList.add('hidden');
}
</script>
