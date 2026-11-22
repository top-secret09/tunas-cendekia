<?php
// includes/modals.php
// Modals konsisten untuk SEMUA akun: Modal Profil Saya & Modal Konfirmasi Keluar
$user = get_current_user_data();
$display_name = $user['nama'] ?? $user['username'] ?? 'Pengguna';
$role = $user['peran'] ?? 'Admin';
$username = $user['username'] ?? '';
$no_telepon = $user['no_telepon'] ?? '';
$alamat = $user['alamat'] ?? '';
$pendidikan = $user['pendidikan_terakhir'] ?? '';
$bidang_mapel = $user['bidang_mapel'] ?? '';
$tgl_lahir = $user['tanggal_lahir'] ?? '';
$jk = $user['jenis_Kelamin'] ?? '';
?>

<!-- BEGIN: Modal Profil Saya -->
<div id="modal-profile" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200">
    <div id="modal-profile-card" class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-lg w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 sticky top-0 z-10">
            <div>
                <h3 class="font-display text-base sm:text-lg font-bold text-slate-900 tracking-tight">Profil Pengguna</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola informasi profil akun <?= htmlspecialchars($role) ?></p>
            </div>
            <button type="button" class="modal-close-profile w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <!-- Form Body -->
        <form action="../update_profile.php" method="POST" class="p-6 space-y-4" id="form-update-profile">
            <input type="hidden" name="id_akun" value="<?= htmlspecialchars($user['id_akun'] ?? '') ?>">
            
            <!-- Avatar Header -->
            <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                <div class="w-14 h-14 rounded-full bg-[#143d73] text-white flex items-center justify-center text-xl font-bold shadow-md ring-4 ring-blue-50">
                    <?= strtoupper(substr($display_name, 0, 1)) ?>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold border border-blue-100">
                            <?= htmlspecialchars($role) ?> Tunas Cendekia
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 truncate">ID Akun: #<?= htmlspecialchars($user['id_akun'] ?? '') ?> (Username: <?= htmlspecialchars($username) ?>)</p>
                </div>
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($display_name) ?>" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
            </div>

            <!-- Username & Telepon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Username Login</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-600 font-medium" readonly>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="no_telepon" value="<?= htmlspecialchars($no_telepon) ?>" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium" placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <!-- Detail Khusus Guru -->
            <?php if ($role === 'Guru'): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" value="<?= htmlspecialchars($pendidikan) ?>" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bidang Mapel</label>
                        <input type="text" name="bidang_mapel" value="<?= htmlspecialchars($bidang_mapel) ?>" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Detail Khusus Siswa -->
            <?php if ($role === 'Siswa'): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($tgl_lahir) ?>" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
                            <option value="laki-laki" <?= ($jk == 'laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="perempuan" <?= ($jk == 'perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alamat -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Domisili</label>
                <textarea name="alamat" rows="2" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium resize-none"><?= htmlspecialchars($alamat) ?></textarea>
            </div>

            <!-- Ganti Password -->
            <div class="pt-2 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password_baru" placeholder="Masukkan password baru..." class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-800 font-medium">
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                <button type="button" class="modal-close-profile px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-lg transition-colors">
                    Tutup / Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#143d73] hover:bg-[#1a4a88] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
<!-- END: Modal Profil Saya -->

<!-- BEGIN: Modal Konfirmasi Keluar -->
<div id="modal-logout" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200">
    <div id="modal-logout-card" class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-sm w-full p-6 text-center transform scale-95 transition-transform duration-200">
        <!-- Alert Icon -->
        <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-red-50/50">
            <span class="material-symbols-outlined text-3xl">logout</span>
        </div>
        <h3 class="font-display text-lg font-bold text-slate-900 tracking-tight mb-2">Konfirmasi Keluar</h3>
        <p class="text-xs text-slate-600 leading-relaxed mb-6">
            Apakah Anda yakin ingin keluar dari akun <?= htmlspecialchars($role) ?> Bimbel Tunas Cendekia? Anda perlu masuk kembali untuk mengakses sistem.
        </p>
        <div class="flex items-center justify-center gap-3">
            <button type="button" class="modal-close-logout flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-xl transition-colors">
                Batal
            </button>
            <a href="../logout.php" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors shadow-red-600/20 text-center">
                Ya, Keluar
            </a>
        </div>
    </div>
</div>
<!-- END: Modal Konfirmasi Keluar -->
