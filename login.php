<?php
require_once __DIR__ . '/config/database.php';

// Demo login handling should occur before any session checks to avoid redirecting an existing logged‑in user.
if (isset($_GET['demo'])) {
    // Clear any existing session to ensure demo works correctly.
    session_destroy();
    session_start();
    $demoMap = ['admin' => 'admin01', 'guru' => 'guru01', 'siswa' => 'siswa01'];
    $roleKey = $_GET['demo'];
    if (array_key_exists($roleKey, $demoMap)) {
        $username = $demoMap[$roleKey];
        $stmtDemo = $pdo->prepare("SELECT * FROM akun WHERE username = ? LIMIT 1");
        $stmtDemo->execute([$username]);
        $demoAkun = $stmtDemo->fetch();
        if ($demoAkun) {
            $peran = ucfirst(strtolower($demoAkun['peran']));
            $_SESSION['user'] = [
                'id_akun' => (int)$demoAkun['id_akun'],
                'username' => $demoAkun['username'],
                'peran' => $peran,
                'role' => strtolower($peran)
            ];
            // Load profile details based on role and redirect immediately.
            if ($peran === 'Guru') {
                $g = $pdo->prepare("SELECT * FROM guru WHERE id_akun = ?");
                $g->execute([$demoAkun['id_akun']]);
                $guru_data = $g->fetch();
                if ($guru_data) {
                    $_SESSION['user']['id_guru'] = (int)$guru_data['id_guru'];
                    $_SESSION['user']['nama'] = $guru_data['nama_guru'];
                    $_SESSION['user']['no_telepon'] = $guru_data['no_telepon'];
                    $_SESSION['user']['alamat'] = $guru_data['alamat'];
                    $_SESSION['user']['pendidikan_terakhir'] = $guru_data['pendidikan_terakhir'];
                    $_SESSION['user']['bidang_mapel'] = $guru_data['bidang_mapel'];
                }
                redirect('guru/index.php');
            } elseif ($peran === 'Siswa') {
                $s = $pdo->prepare("SELECT * FROM siswa WHERE id_akun = ?");
                $s->execute([$demoAkun['id_akun']]);
                $siswa_data = $s->fetch();
                if ($siswa_data) {
                    $_SESSION['user']['id_siswa'] = (int)$siswa_data['id_siswa'];
                    $_SESSION['user']['nama'] = $siswa_data['nama_siswa'];
                    $_SESSION['user']['jenis_Kelamin'] = $siswa_data['jenis_Kelamin'];
                    $_SESSION['user']['tanggal_lahir'] = $siswa_data['tanggal_lahir'];
                    $_SESSION['user']['alamat'] = $siswa_data['alamat'];
                    $_SESSION['user']['no_telepon'] = $siswa_data['no_telepon'];
                }
                redirect('siswa/index.php');
            } else {
                $_SESSION['user']['nama'] = 'Administrator';
                redirect('admin/index.php');
            }
        } else {
            $error = 'Akun demo tidak ditemukan.';
        }
    } else {
        $error = 'Parameter demo tidak dikenali.';
    }
    // After handling demo we stop further execution.
    exit;
}


if (is_logged_in()) {
    $role = strtolower($_SESSION['user']['peran'] ?? $_SESSION['user']['role'] ?? '');
    if ($role === 'admin') {
        redirect('admin/index.php');
    } elseif ($role === 'guru') {
        redirect('guru/index.php');
    } else {
        redirect('siswa/index.php');
    }
    exit;
}

// Initialize error & success variables for the login form
$error = '';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_input = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email_input) || empty($password)) {
        $error = 'Silakan isi email/username dan password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM akun WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$email_input, $email_input]);
        $akun = $stmt->fetch();

        if ($akun && ($password === $akun['password'] || password_verify($password, $akun['password']))) {
            $peran = ucfirst(strtolower($akun['peran']));

            $_SESSION['user'] = [
                'id_akun' => (int)$akun['id_akun'],
                'username' => $akun['username'],
                'peran' => $peran,
                'role' => strtolower($peran)
            ];

            // Load detail profil sesuai peran
            if ($peran === 'Guru') {
                $g = $pdo->prepare("SELECT * FROM guru WHERE id_akun = ?");
                $g->execute([$akun['id_akun']]);
                $guru_data = $g->fetch();
                if ($guru_data) {
                    $_SESSION['user']['id_guru'] = (int)$guru_data['id_guru'];
                    $_SESSION['user']['nama'] = $guru_data['nama_guru'];
                    $_SESSION['user']['no_telepon'] = $guru_data['no_telepon'];
                    $_SESSION['user']['alamat'] = $guru_data['alamat'];
                    $_SESSION['user']['pendidikan_terakhir'] = $guru_data['pendidikan_terakhir'];
                    $_SESSION['user']['bidang_mapel'] = $guru_data['bidang_mapel'];
                    $_SESSION['guru'] = $guru_data;
                }
                redirect('guru/index.php');
            } elseif ($peran === 'Siswa') {
                $s = $pdo->prepare("SELECT * FROM siswa WHERE id_akun = ?");
                $s->execute([$akun['id_akun']]);
                $siswa_data = $s->fetch();
                if ($siswa_data) {
                    $_SESSION['user']['id_siswa'] = (int)$siswa_data['id_siswa'];
                    $_SESSION['user']['nama'] = $siswa_data['nama_siswa'];
                    $_SESSION['user']['jenis_Kelamin'] = $siswa_data['jenis_Kelamin'];
                    $_SESSION['user']['tanggal_lahir'] = $siswa_data['tanggal_lahir'];
                    $_SESSION['user']['alamat'] = $siswa_data['alamat'];
                    $_SESSION['user']['no_telepon'] = $siswa_data['no_telepon'];
                    $_SESSION['siswa'] = $siswa_data;
                }
                redirect('siswa/index.php');
            } else {
                $_SESSION['user']['nama'] = 'Administrator';
                redirect('admin/index.php');
            }
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Halaman Login - Bimbel Tunas Cendekia</title>
<!-- Load Tailwind CSS v3 with Plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#1b3b6f',
              'navy-hover': '#152e57',
              primary: '#2176ff',
              light: '#f4f7fb',
              border: '#dbe2ea'
            }
          }
        }
      }
    }
  </script>
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<style data-purpose="base-typography">
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-[#f0f4fa] min-h-screen flex items-center justify-center p-4 antialiased text-slate-800">
<!-- BEGIN: LoginContainer -->
<main class="w-full max-w-[420px] bg-white rounded-2xl shadow-[0_4px_25px_rgba(0,0,0,0.06)] border border-slate-100 p-8 sm:p-9" data-purpose="login-card">
<!-- BEGIN: BrandHeader -->
<header class="flex flex-col items-center text-center mb-6">
<!-- Logo Icon: Open Book with Star/Growth Emblem -->
<a href="index.php" class="mb-3 flex items-center justify-center w-20 h-20 rounded-full overflow-hidden shadow-sm border border-slate-100 hover:opacity-90 transition-opacity bg-white p-2" data-purpose="logo-symbol">
  <img src="assets/images/logo.png" alt="Logo Bimbel Tunas Cendekia" class="w-full h-full object-contain">
</a>
<!-- Title & Branding -->
<h1 class="text-xl font-bold text-slate-800 leading-tight">
  Bimbel<br><span class="tracking-tight">Tunas Cendekia</span>
</h1>
<p class="text-xs sm:text-sm text-slate-500 mt-2 font-normal">
  Masuk ke akun Anda
</p>
</header>
<!-- END: BrandHeader -->

<?php if ($error): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
    <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<!-- BEGIN: LoginForm -->
<form action="login.php" class="space-y-4" data-purpose="login-form" method="POST">
<!-- Username Input -->
<div>
<label class="sr-only" for="email">Email Akun</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
<!-- User Icon (keep) -->
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</div>
<input class="block w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3b6f] focus:border-transparent transition" id="email" name="email" placeholder="Masukkan Email Akun" required type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>
</div>

<!-- Password Input -->
<div>
<label class="sr-only" for="password">Password</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
<!-- Lock Icon -->
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<input class="block w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3b6f] focus:border-transparent transition" id="password" name="password" placeholder="Password" required="" type="password">
<!-- Eye / Visibility Toggle Icon -->
<button aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" id="togglePasswordBtn" type="button">
<svg class="w-4 h-4" fill="none" id="eyeIcon" stroke="currentColor" viewBox="0 0 24 24">
<path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</div>

<!-- Remember Me & Forgot Password Row -->
<div class="flex items-center justify-between pt-1">
<label class="flex items-center cursor-pointer select-none">
<input class="w-4 h-4 text-[#1b3b6f] border-slate-300 rounded focus:ring-[#1b3b6f] focus:ring-1" name="remember" type="checkbox">
<span class="ml-2 text-xs text-slate-600">Ingat Saya</span>
</label>
<a class="text-xs text-[#2176ff] hover:underline font-medium" href="#" onclick="alert('Silakan hubungi administrator bimbingan belajar untuk mereset kata sandi Anda.')">
  Lupa Password?
</a>
</div>

<!-- Submit Button -->
<div class="pt-2">
<button class="w-full py-2.5 px-4 bg-[#1b3b6f] hover:bg-[#152e57] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1b3b6f]" type="submit">
  Login
</button>
</div>
<!-- END: LoginForm -->

<!-- BEGIN: RoleSelectorSection -->
<div class="mt-7" data-purpose="role-selection">
<!-- Divider with text -->
<div class="relative flex items-center justify-center mb-5">
<div class="border-t border-slate-200 w-full"></div>
<span class="bg-white px-3 text-[11px] sm:text-xs text-slate-400 absolute">
  atau login cepat sebagai demo
</span>
</div>
<!-- Role Cards Grid -->
<div class="grid grid-cols-3 gap-2.5 pt-1">
<!-- Admin Option -->
<button class="flex flex-col items-center justify-center py-3 px-1 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-slate-700 group focus:outline-none focus:ring-2 focus:ring-[#1b3b6f]" data-purpose="select-role-admin" onclick="window.location.href='login.php?demo=admin'" type="button">
<div class="w-8 h-8 flex items-center justify-center text-slate-700 group-hover:text-[#1b3b6f]">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M19 11l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<span class="text-xs font-medium text-slate-700 mt-1">Admin</span>
</button>
<!-- Pengajar Option -->
<button class="flex flex-col items-center justify-center py-3 px-1 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-slate-700 group focus:outline-none focus:ring-2 focus:ring-[#1b3b6f]" data-purpose="select-role-pengajar" onclick="window.location.href='login.php?demo=guru'" type="button">
<div class="w-8 h-8 flex items-center justify-center text-slate-700 group-hover:text-[#1b3b6f]">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M12 14l9-5-9-5-9 5 9 5z" stroke-linecap="round" stroke-linejoin="round"></path>
<path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-linecap="round" stroke-linejoin="round"></path>
<path d="M21 10v6m-4-3v3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<span class="text-xs font-medium text-slate-700 mt-1">Pengajar</span>
</button>
<!-- Siswa Option -->
<button class="flex flex-col items-center justify-center py-3 px-1 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-slate-700 group focus:outline-none focus:ring-2 focus:ring-[#1b3b6f]" data-purpose="select-role-siswa" onclick="window.location.href='login.php?demo=siswa'" type="button">
<div class="w-8 h-8 flex items-center justify-center text-slate-700 group-hover:text-[#1b3b6f]">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<span class="text-xs font-medium text-slate-700 mt-1">Siswa</span>
</button>
</div>
</div>
<!-- END: RoleSelectorSection -->

<div class="mt-6 text-center text-xs text-slate-500">
  Belum memiliki akun siswa? 
  <a href="daftar.php" class="text-blue-600 hover:underline font-semibold">Daftar Sekarang</a>
</div>

</main>
<!-- END: LoginContainer -->

<script>
  const toggleBtn = document.getElementById('togglePasswordBtn');
  const passInput = document.getElementById('password');
  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', () => {
      const isPass = passInput.getAttribute('type') === 'password';
      passInput.setAttribute('type', isPass ? 'text' : 'password');
    });
  }
</script>
</body>
</html>
