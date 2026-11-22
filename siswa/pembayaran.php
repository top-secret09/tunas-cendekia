<?php
// siswa/pembayaran.php - Pembayaran Siswa & Upload Bukti Bayar
require_once __DIR__ . '/../config/database.php';
check_access(['Siswa']);

$page_title = "Pembayaran";
$current_page = "pembayaran";

$user = get_current_user_data();
$id_siswa = (int)($user['id_siswa'] ?? $_SESSION['siswa']['id_siswa'] ?? 1);

$alert_msg = '';
$alert_type = 'success';

// Handle Upload Bukti Pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bayar') {
    $id_pendaftaran = (int)($_POST['id_pendaftaran'] ?? 0);
    $jumlah_bayar = (float)($_POST['jumlah_bayar'] ?? 0);
    $metode_bayar = db_escape($_POST['metode_bayar'] ?? 'Transfer');
    $tanggal_bayar = db_escape($_POST['tanggal_bayar'] ?? date('Y-m-d'));

    if ($id_pendaftaran <= 0 || $jumlah_bayar <= 0) {
        $alert_msg = "Silakan pilih kelas tagihan dan masukkan jumlah pembayaran.";
        $alert_type = "danger";
    } else {
        $bukti_name = null;
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['bukti_pembayaran']['tmp_name'];
            $ext = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
            $bukti_name = 'bukti_' . $id_siswa . '_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $bukti_name;
            move_uploaded_file($tmp, $dest);
        }

        $bukti_val = $bukti_name ? "'$bukti_name'" : "NULL";

        $q = db_query("INSERT INTO pembayaran (id_pendaftaran, tanggal_bayar, jumlah_bayar, metode_bayar, status_pembayaran, bukti_pembayaran) 
                       VALUES ($id_pendaftaran, '$tanggal_bayar', $jumlah_bayar, '$metode_bayar', 'Menunggu', $bukti_val)");
        if ($q) {
            $alert_msg = "Konfirmasi pembayaran berhasil dikirim. Menunggu verifikasi admin.";
        } else {
            $alert_msg = "Gagal memproses pembayaran.";
            $alert_type = "danger";
        }
    }
}

// Search
$search = db_escape(trim($_GET['q'] ?? ''));

$where_clauses = ["pd.id_siswa = $id_siswa"];
if (!empty($search)) {
    $where_clauses[] = "(k.nama_kelas LIKE '%$search%' OR p.metode_bayar LIKE '%$search%' OR p.status_pembayaran LIKE '%$search%')";
}
$where_sql = implode(' AND ', $where_clauses);

// Riwayat Pembayaran Siswa
$riwayat_bayar = db_fetch_all("SELECT p.*, k.nama_kelas, k.tingkat, k.biaya, pd.tanggal_daftar 
                              FROM pembayaran p 
                              JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran 
                              JOIN kelas k ON pd.id_kelas = k.id_kelas 
                              WHERE $where_sql 
                              ORDER BY p.id_pembayaran DESC");

// Pending / Unpaid registrations
$pending_tagihan = db_fetch_all("SELECT pd.*, k.nama_kelas, k.tingkat, k.biaya 
                                 FROM pendaftaran pd 
                                 JOIN kelas k ON pd.id_kelas = k.id_kelas 
                                 WHERE pd.id_siswa = $id_siswa 
                                 AND pd.id_pendaftaran NOT IN (SELECT id_pendaftaran FROM pembayaran WHERE status_pembayaran = 'Lunas')");

// Metric calculations
$total_lunas_count = 0;
$total_lunas_nominal = 0;
$total_pending_count = 0;
$total_pending_nominal = 0;

foreach ($riwayat_bayar as $rb) {
    if ($rb['status_pembayaran'] === 'Lunas') {
        $total_lunas_count++;
        $total_lunas_nominal += (float)$rb['jumlah_bayar'];
    } elseif ($rb['status_pembayaran'] === 'Menunggu') {
        $total_pending_count++;
        $total_pending_nominal += (float)$rb['jumlah_bayar'];
    }
}
foreach ($pending_tagihan as $pt) {
    // If not already in payments table
    $hasPayment = false;
    foreach ($riwayat_bayar as $rb) {
        if ($rb['id_pendaftaran'] == $pt['id_pendaftaran']) {
            $hasPayment = true;
            break;
        }
    }
    if (!$hasPayment) {
        $total_pending_count++;
        $total_pending_nominal += (float)$pt['biaya'];
    }
}

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

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-slate-500">Tagihan Lunas</p>
                    <p class="text-2xl font-bold text-slate-900 tracking-tight"><?= $total_lunas_count ?> Pembayaran</p>
                    <p class="text-[11px] text-slate-400 font-medium">Total: Rp <?= number_format($total_lunas_nominal, 0, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#dcfce7] border border-[#bbf7d0] flex items-center justify-center text-[#16a34a]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-slate-500">Menunggu Pembayaran / Verifikasi</p>
                    <p class="text-2xl font-bold text-slate-900 tracking-tight"><?= $total_pending_count ?> Tagihan</p>
                    <p class="text-[11px] text-slate-400 font-medium">Total: Rp <?= number_format($total_pending_nominal, 0, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#fef3c7] border border-[#fde68a] flex items-center justify-center text-[#d97706]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </div>

        <!-- Main Card Pembayaran -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-7">
            <!-- Top Title & Action Button -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pembayaran Bimbel</h2>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                        <a href="index.php" class="hover:text-blue-600 transition">Dashboard</a>
                        <span class="text-slate-400">&gt;</span>
                        <span class="text-slate-600 font-medium">Pembayaran</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <button onclick="window.print()" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-lg shadow-xs transition-colors">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">download</span>
                        <span>Ekspor Data</span>
                    </button>
                    <?php if (!empty($pending_tagihan)): ?>
                        <button onclick="openModalBayar()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#19376D] hover:bg-blue-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Kirim Bukti Bayar</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Table Riwayat Pembayaran -->
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/70 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-3.5 px-6 text-center w-12">NO</th>
                            <th class="py-3.5 px-6">ID INVOICE</th>
                            <th class="py-3.5 px-6">KELAS BIMBEL</th>
                            <th class="py-3.5 px-6">TANGGAL BAYAR</th>
                            <th class="py-3.5 px-6">JUMLAH BAYAR</th>
                            <th class="py-3.5 px-6">METODE</th>
                            <th class="py-3.5 px-6 text-center">STATUS</th>
                            <th class="py-3.5 px-6 text-center">BUKTI BAYAR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($riwayat_bayar)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">payments</span>
                                    Belum ada transaksi pembayaran yang tercatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat_bayar as $idx => $rb): 
                                $inv = 'INV-' . date('Ym', strtotime($rb['tanggal_bayar'])) . '-' . sprintf('%03d', $rb['id_pembayaran']);
                                $st = $rb['status_pembayaran'];
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 text-center font-mono text-slate-400"><?= sprintf('%02d', $idx + 1) ?></td>
                                    <td class="py-4 px-6 font-mono font-bold text-blue-900"><?= $inv ?></td>
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-slate-900 text-xs block"><?= htmlspecialchars($rb['nama_kelas']) ?></span>
                                        <span class="text-[11px] text-slate-400"><?= htmlspecialchars($rb['tingkat']) ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500">
                                        <?= format_tanggal_indo($rb['tanggal_bayar']) ?>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-slate-900">
                                        Rp <?= number_format($rb['jumlah_bayar'], 0, ',', '.') ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            <?= htmlspecialchars($rb['metode_bayar']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($st === 'Lunas'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Lunas</span>
                                            </span>
                                        <?php elseif ($st === 'Ditolak'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Menunggu Verifikasi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if (!empty($rb['bukti_pembayaran'])): ?>
                                            <a href="../uploads/<?= htmlspecialchars($rb['bukti_pembayaran']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition">
                                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                <span>Lihat</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs">Tidak ada bukti</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-white flex items-center justify-between border-t border-slate-100 text-xs text-slate-500 mt-4">
                <span>Menampilkan <span class="font-semibold text-slate-700">1 - <?= count($riwayat_bayar) ?></span> data transaksi</span>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold flex items-center justify-center">1</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<!-- Modal Kirim Bukti Pembayaran -->
<div id="modalBayar" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-bold text-slate-900">Kirim Bukti Pembayaran</h3>
            <button type="button" onclick="closeModalBayar()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <form method="POST" action="pembayaran.php" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="action" value="bayar">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Kelas Tagihan <span class="text-red-500">*</span></label>
                <select name="id_pendaftaran" id="selectPendaftaran" required onchange="setBiaya(this)" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="">-- Pilih Tagihan Kelas --</option>
                    <?php foreach ($pending_tagihan as $pt): ?>
                        <option value="<?= $pt['id_pendaftaran'] ?>" data-biaya="<?= $pt['biaya'] ?>">
                            <?= htmlspecialchars($pt['nama_kelas']) ?> - Rp <?= number_format($pt['biaya'], 0, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Bayar (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_bayar" id="inputJumlahBayar" required placeholder="500000" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_bayar" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="metode_bayar" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                    <option value="Transfer Bank (BCA)">Transfer Bank (BCA 8830-1234-5678 a/n Tunas Cendekia)</option>
                    <option value="Transfer Bank (Mandiri)">Transfer Bank (Mandiri 137-00-9876543-2 a/n Tunas Cendekia)</option>
                    <option value="Transfer Bank (BRI)">Transfer Bank (BRI 0021-01-001234-50-1 a/n Tunas Cendekia)</option>
                    <option value="Tunai di Kasir">Tunai di Kasir Kantor Cabang</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unggah Foto Bukti Transfer <span class="text-red-500">*</span></label>
                <input type="file" name="bukti_pembayaran" accept="image/*,.pdf" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 bg-white">
                <p class="text-[11px] text-slate-400 mt-1">Format file: JPG, PNG, atau PDF. Maksimal 2MB.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalBayar()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#19376D] hover:bg-blue-800 rounded-lg transition shadow-xs">Kirim Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalBayar() {
    document.getElementById('modalBayar').classList.remove('hidden');
}
function closeModalBayar() {
    document.getElementById('modalBayar').classList.add('hidden');
}
function setBiaya(sel) {
    const opt = sel.options[sel.selectedIndex];
    const biaya = opt.getAttribute('data-biaya');
    if (biaya) {
        document.getElementById('inputJumlahBayar').value = biaya;
    }
}
</script>
