<?php
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

// Ambil daftar kelas untuk dropdown
$stmt_kelas = $pdo->query("SELECT id_kelas, nama_kelas, tingkat, biaya FROM kelas WHERE status_kelas = 'Aktif' ORDER BY id_kelas ASC");
$daftar_kelas = $stmt_kelas->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $ttl = trim($_POST['ttl'] ?? '');
    $jenis_kelamin_raw = trim($_POST['jenis_kelamin'] ?? '');
    $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $id_kelas = (int)($_POST['id_kelas'] ?? 0);
    $alamat = trim($_POST['alamat'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Format jenis kelamin untuk enum DB: 'laki-laki' atau 'perempuan'
    $jenis_kelamin = (strtolower($jenis_kelamin_raw) === 'perempuan') ? 'perempuan' : 'laki-laki';

    // Parse tanggal lahir jika ada format YYYY-MM-DD atau DD/MM/YYYY
    $tanggal_lahir = date('Y-m-d');
    if (!empty($ttl)) {
        $parsed = strtotime($ttl);
        if ($parsed) {
            $tanggal_lahir = date('Y-m-d', $parsed);
        }
    }

    if (empty($nama_lengkap) || empty($no_hp) || empty($id_kelas)) {
        $error = 'Mohon lengkapi Nama Lengkap, Nomor HP, dan Pilihan Kelas.';
    } else {
        // Generate username & password default jika kosong
        if (empty($username)) {
            $username = 'siswa_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($nama_lengkap, 0, 8))) . rand(10, 99);
        }
        if (empty($password)) {
            $password = 'siswa123';
        }

        // Cek username duplicate
        $cek = $pdo->prepare("SELECT id_akun FROM akun WHERE username = ?");
        $cek->execute([$username]);
        if ($cek->fetch()) {
            $username .= rand(100, 999);
        }

        try {
            $pdo->beginTransaction();

            // 1. Insert Akun
            $stmt_akun = $pdo->prepare("INSERT INTO akun (username, password, peran) VALUES (?, ?, 'Siswa')");
            $stmt_akun->execute([$username, $password]);
            $id_akun = $pdo->lastInsertId();

            // 2. Insert Siswa
            $stmt_siswa = $pdo->prepare("INSERT INTO siswa (id_akun, nama_siswa, jenis_Kelamin, tanggal_lahir, alamat, no_telepon, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt_siswa->execute([$id_akun, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, $alamat, $no_hp]);
            $id_siswa = $pdo->lastInsertId();

            // 3. Insert Pendaftaran
            $stmt_daftar = $pdo->prepare("INSERT INTO pendaftaran (id_siswa, id_kelas, tanggal_daftar, status_pendaftaran) VALUES (?, ?, CURDATE(), 'Menunggu')");
            $stmt_daftar->execute([$id_siswa, $id_kelas]);

            $pdo->commit();

            $success = "Pendaftaran berhasil! Akun Anda telah dibuat dengan Username: <strong>$username</strong> dan Password: <strong>$password</strong>. Silakan simpan untuk login.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan saat pendaftaran: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Halaman Pendaftaran Siswa - Bimbel Tunas Cendekia</title>
<!-- Tailwind CSS v3 with Plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Tailwind Configuration -->
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#f0f5ff',
              100: '#e0ecff',
              200: '#c5dcff',
              500: '#2b6cb0',
              600: '#1d4ed8',
              800: '#133560',
              900: '#0e2440'
            }
          }
        }
      }
    }
  </script>
<!-- Font Awesome for Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<style data-purpose="custom-styling">
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
  </style>
</head>
<body class="bg-[#eef4fc] min-h-screen flex items-center justify-center p-4 md:p-8 antialiased text-gray-800">
<!-- BEGIN: RegistrationCard -->
<main class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100 flex flex-col md:flex-row" data-purpose="registration-card">
<!-- BEGIN: LeftHeroIllustration -->
<!-- Kolom Kiri: Ilustrasi Siswa & Teks Promosi Sesuai Referensi -->
<div class="w-full md:w-5/12 bg-gradient-to-b from-[#e3efff] to-[#d3e5fc] p-8 md:p-10 flex flex-col items-center justify-center text-center relative border-b md:border-b-0 md:border-r border-blue-100" data-purpose="illustration-panel">
<!-- Ilustrasi Siswa & Clipboard -->
<div class="w-64 h-64 relative flex items-center justify-center mb-6" data-purpose="vector-illustration">
<!-- Background circular glow -->
<div class="absolute w-52 h-52 bg-white/70 rounded-full blur-md"></div>
<!-- SVG Ilustrasi Vektor Kustom Siswa & Clipboard Pendaftaran -->
<svg class="relative z-10 w-full h-full drop-shadow-md" fill="none" viewbox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
<!-- Clipboard Base -->
<rect fill="#3B82F6" height="175" opacity="0.15" rx="14" width="130" x="40" y="55"></rect>
<rect fill="#FFFFFF" height="175" rx="14" stroke="#1D4ED8" stroke-width="4" width="130" x="46" y="50"></rect>
<!-- Clipboard Top Clip -->
<rect fill="#1E3A8A" height="24" rx="6" width="50" x="86" y="38"></rect>
<circle cx="111" cy="46" fill="#FFFFFF" r="4"></circle>
<!-- Checklist lines & Checkmarks inside clipboard -->
<circle cx="70" cy="85" fill="#10B981" r="7"></circle>
<path d="M67 85L69 87L73 83" stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<rect fill="#E2E8F0" height="8" rx="4" width="70" x="85" y="81"></rect>
<circle cx="70" cy="115" fill="#10B981" r="7"></circle>
<path d="M67 115L69 117L73 113" stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<rect fill="#E2E8F0" height="8" rx="4" width="60" x="85" y="111"></rect>
<circle cx="70" cy="145" fill="#10B981" r="7"></circle>
<path d="M67 145L69 147L73 143" stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<rect fill="#E2E8F0" height="8" rx="4" width="72" x="85" y="141"></rect>
<circle cx="70" cy="175" fill="#93C5FD" r="7"></circle>
<rect fill="#E2E8F0" height="8" rx="4" width="45" x="85" y="171"></rect>
<!-- Pencil Accent -->
<g transform="rotate(28 175 125)">
<rect fill="#F59E0B" height="75" rx="3" stroke="#B45309" stroke-width="2" width="16" x="155" y="70"></rect>
<polygon fill="#FDE68A" points="155,145 171,145 163,160" stroke="#B45309" stroke-width="2"></polygon>
<polygon fill="#1F2937" points="160,154 166,154 163,160"></polygon>
<rect fill="#EC4899" height="10" rx="2" width="16" x="155" y="65"></rect>
</g>
<!-- Student Character Vector -->
<g transform="translate(145, 100)">
<!-- Hair Back -->
<ellipse cx="65" cy="55" fill="#1E293B" rx="26" ry="24"></ellipse>
<!-- Head & Face -->
<ellipse cx="65" cy="58" fill="#FCD34D" rx="20" ry="22"></ellipse>
<!-- Hair Front/Bangs -->
<path d="M47 52C50 40 60 38 65 38C75 38 85 43 85 52C80 47 70 48 65 48C58 48 52 50 47 52Z" fill="#1E293B"></path>
<!-- Eyes & Smile -->
<circle cx="58" cy="57" fill="#1E293B" r="2.5"></circle>
<circle cx="72" cy="57" fill="#1E293B" r="2.5"></circle>
<path d="M62 65C64 68 67 68 69 65" fill="none" stroke="#1E293B" stroke-linecap="round" stroke-width="2"></path>
<!-- Neck & Collar -->
<rect fill="#FCD34D" height="8" width="9" x="61" y="78"></rect>
<path d="M54 84L65 91L76 84" fill="#FFFFFF"></path>
<!-- Body / Jacket -->
<path d="M40 92C40 85 50 84 65 84C80 84 90 85 90 92L95 160H35L40 92Z" fill="#1E3A8A"></path>
<path d="M58 86L65 110L72 86" fill="#3B82F6"></path>
<!-- Thumbs Up Hand -->
<circle cx="36" cy="115" fill="#FCD34D" r="10"></circle>
<rect fill="#FCD34D" height="12" rx="3" width="7" x="33" y="103"></rect>
<!-- Graduation Cap floating decoration -->
<polygon fill="#1E3A8A" points="65,10 95,22 65,34 35,22"></polygon>
<rect fill="#1E40AF" height="8" rx="3" width="26" x="52" y="27"></rect>
<path d="M85 24V40" stroke="#F59E0B" stroke-linecap="round" stroke-width="3"></path>
<circle cx="85" cy="42" fill="#F59E0B" r="3"></circle>
</g>
<!-- Decorative Stars -->
<path d="M30 65L32 60L37 58L32 56L30 51L28 56L23 58L28 60Z" fill="#3B82F6"></path>
<path d="M265 95L267 90L272 88L267 86L265 81L263 86L258 88L263 90Z" fill="#F59E0B"></path>
<circle cx="280" cy="160" fill="#60A5FA" r="4"></circle>
</svg>
</div>
<!-- Headline and Subtitle -->
<h2 class="text-2xl md:text-3xl font-extrabold text-[#0e2440] tracking-tight mb-2">
  Daftar Sekarang!
</h2>
<p class="text-sm md:text-base text-gray-600 font-normal leading-relaxed max-w-xs">
  Bergabunglah bersama <span class="font-semibold text-brand-800">Bimbel Tunas Cendekia</span> untuk meraih prestasi terbaik.
</p>
<div class="mt-6">
  <a href="login.php" class="inline-flex items-center gap-1.5 text-xs text-blue-700 hover:text-blue-900 font-semibold bg-white/80 hover:bg-white px-4 py-2 rounded-full border border-blue-200 transition-colors shadow-xs">
    <i class="fa-solid fa-arrow-left text-xs"></i> Sudah punya akun? Login
  </a>
</div>
</div>
<!-- END: LeftHeroIllustration -->

<!-- BEGIN: RightRegistrationForm -->
<!-- Kolom Kanan: Formulir Isian Pendaftaran -->
<div class="w-full md:w-7/12 p-6 md:p-10 flex flex-col justify-center" data-purpose="form-panel">
<!-- Header Form -->
<div class="mb-5">
<h3 class="text-xl md:text-2xl font-bold text-[#0e2440] mb-1">
  Formulir Pendaftaran
</h3>
<p class="text-xs text-gray-500">Lengkapi data diri calon siswa dengan benar di bawah ini.</p>
</div>

<?php if ($error): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
    <div class="flex items-center gap-2 font-bold text-sm mb-1">
      <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
      <span>Pendaftaran Berhasil!</span>
    </div>
    <div class="leading-relaxed"><?= $success ?></div>
    <div class="mt-3">
      <a href="login.php" class="inline-block px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition-colors shadow-xs">
        Menuju Halaman Login &rarr;
      </a>
    </div>
  </div>
<?php endif; ?>

<!-- Form Elements -->
<form action="daftar.php" class="space-y-4" id="form-pendaftaran-siswa" method="POST">
<!-- Row 1: Nama Lengkap & Tempat, Tgl Lahir -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="nama_lengkap">Nama Lengkap</label>
<input class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Andi Pratama" required="" type="text" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"/>
</div>
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="ttl">Tanggal Lahir</label>
<input class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" id="ttl" name="ttl" placeholder="YYYY-MM-DD" type="date" value="<?= htmlspecialchars($_POST['ttl'] ?? '') ?>"/>
</div>
</div>

<!-- Row 2: Jenis Kelamin & Nomor HP / WhatsApp -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="jenis_kelamin">Jenis Kelamin</label>
<div class="relative">
<select class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" id="jenis_kelamin" name="jenis_kelamin" required="">
<option disabled="" selected="" value="">Pilih Jenis Kelamin</option>
<option value="Laki-laki" <?= (($_POST['jenis_kelamin'] ?? '') === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
<option value="Perempuan" <?= (($_POST['jenis_kelamin'] ?? '') === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
</select>
</div>
</div>
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="no_hp">Nomor HP / WhatsApp</label>
<input class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" id="no_hp" name="no_hp" placeholder="08123456789" required="" type="tel" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"/>
</div>
</div>

<!-- Row 3: Pilihan Kelas Bimbel -->
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="id_kelas">Pilihan Kelas Bimbel</label>
<div class="relative">
<select class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" id="id_kelas" name="id_kelas" required="">
<option disabled="" selected="" value="">Pilih Kelas</option>
<?php foreach ($daftar_kelas as $k): ?>
  <option value="<?= $k['id_kelas'] ?>" <?= (($_POST['id_kelas'] ?? '') == $k['id_kelas']) ? 'selected' : '' ?>>
    <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['tingkat']) ?>) - Rp <?= number_format($k['biaya'], 0, ',', '.') ?>
  </option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- Row 4: Alamat Lengkap -->
<div>
<label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="alamat">Alamat Lengkap</label>
<textarea class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 resize-none" id="alamat" name="alamat" placeholder="Masukkan alamat domisili saat ini..." rows="2"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
</div>

<!-- Optional Username & Password -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
<div>
  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="username">Username (Opsional)</label>
  <input class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="username" name="username" placeholder="Otomatis jika kosong" type="text" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"/>
</div>
<div>
  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1" for="password">Password (Opsional)</label>
  <input class="w-full px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="password" name="password" placeholder="Default: siswa123" type="password"/>
</div>
</div>

<!-- Submit Button -->
<div class="pt-3">
<button class="w-full py-2.5 px-4 bg-[#133560] hover:bg-[#0c2545] text-white font-medium text-sm rounded-lg shadow hover:shadow-md transition-all duration-200 transform active:scale-[0.99] flex items-center justify-center space-x-2" type="submit">
<span>Daftar Sekarang</span>
<i class="fa-solid fa-arrow-right text-xs"></i>
</button>
</div>
</form>
</div>
<!-- END: RightRegistrationForm -->
</main>
<!-- END: RegistrationCard -->
</body>
</html>
