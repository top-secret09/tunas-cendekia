<?php
require_once __DIR__ . '/config/database.php';
$is_logged_in = is_logged_in();
$user_role = $is_logged_in ? strtolower($_SESSION['user']['peran'] ?? $_SESSION['user']['role'] ?? '') : null;
$dashboard_url = 'login.php';
if ($is_logged_in) {
    if ($user_role === 'admin') $dashboard_url = 'admin/index.php';
    elseif ($user_role === 'guru') $dashboard_url = 'guru/index.php';
    else $dashboard_url = 'siswa/index.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Bimbel Tunas Cendekia - Wujudkan Prestasi dan Masa Depan Gemilang</title>
<!-- Tailwind CSS v3 -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Tailwind Custom Configuration -->
<script data-purpose="tailwind-config">
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#1b3a6b',
              navyDark: '#122544',
              navyHover: '#152f55',
              blue: '#2563eb',
              lightBlue: '#eef6fc',
              cardBg: '#e9f3fb'
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif']
          }
        }
      }
    }
  </script>
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<style data-purpose="custom-typography">
    body {
      font-family: 'Inter', sans-serif;
      background-color: #ffffff;
      color: #1f2937;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col justify-between antialiased selection:bg-blue-100 selection:text-brand-navy">
<!-- BEGIN: MainHeader -->
<header class="w-full bg-white border-b border-gray-100 sticky top-0 z-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
<!-- Brand Logo Left -->
<a class="flex items-center gap-3 group" data-purpose="site-logo" href="index.php">
<div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center border border-blue-100 shadow-sm bg-white shrink-0">
<img alt="Logo Bimbel Tunas Cendekia" class="w-full h-full object-contain p-0.5" src="assets/images/logo.png">
</div>
<div class="flex flex-col leading-tight">
<span class="text-xs uppercase tracking-wider text-blue-600 font-semibold">Bimbel</span>
<span class="text-base font-bold text-brand-navy">Tunas Cendekia</span>
</div>
</a>
<!-- Navigation Links Center -->
<nav class="hidden md:flex items-center gap-8 text-[15px] font-medium text-gray-700" data-purpose="main-nav">
<a class="text-brand-navy font-semibold hover:text-blue-600 transition-colors" href="index.php">Beranda</a>
<a class="hover:text-blue-600 transition-colors" href="#tentang-kami">Tentang Kami</a>
<a class="hover:text-blue-600 transition-colors" href="#program-belajar">Program Belajar</a>
<a class="hover:text-blue-600 transition-colors" href="#keunggulan">Keunggulan</a>
<a class="hover:text-blue-600 transition-colors" href="#kontak">Informasi</a>
</nav>
<!-- Action Button Right -->
<div class="flex items-center gap-3">
<?php if ($is_logged_in): ?>
  <a class="bg-brand-navy hover:bg-brand-navyHover text-white px-6 py-2 rounded-full text-sm font-semibold shadow-sm transition-all duration-150 transform active:scale-95" href="<?= $dashboard_url ?>">
    Dashboard (<?= ucfirst($user_role) ?>)
  </a>
<?php else: ?>
  <a class="bg-brand-navy hover:bg-brand-navyHover text-white px-7 py-2 rounded-full text-sm font-semibold shadow-sm transition-all duration-150 transform active:scale-95" data-purpose="login-button" href="login.php">
    Login
  </a>
  <a class="hidden sm:inline-flex bg-blue-50 hover:bg-blue-100 text-brand-navy border border-blue-200 px-5 py-2 rounded-full text-sm font-semibold transition-all duration-150" href="daftar.php">
    Daftar
  </a>
<?php endif; ?>
<!-- Mobile Menu Button -->
<button class="md:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100" id="mobile-menu-btn" type="button">
  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</button>
</div>
</div>
<!-- Mobile Menu Dropdown -->
<div class="hidden md:hidden border-t border-gray-100 px-4 py-3 bg-white space-y-2" id="mobile-menu">
  <a class="block py-2 text-sm font-semibold text-brand-navy" href="index.php">Beranda</a>
  <a class="block py-2 text-sm font-medium text-gray-700 hover:text-blue-600" href="#tentang-kami">Tentang Kami</a>
  <a class="block py-2 text-sm font-medium text-gray-700 hover:text-blue-600" href="#program-belajar">Program Belajar</a>
  <a class="block py-2 text-sm font-medium text-gray-700 hover:text-blue-600" href="#keunggulan">Keunggulan</a>
  <a class="block py-2 text-sm font-medium text-gray-700 hover:text-blue-600" href="#kontak">Informasi</a>
  <div class="pt-2 border-t border-gray-100 flex flex-col gap-2">
    <?php if ($is_logged_in): ?>
      <a class="text-center bg-brand-navy text-white py-2 rounded-lg text-sm font-semibold" href="<?= $dashboard_url ?>">Dashboard</a>
    <?php else: ?>
      <a class="text-center bg-brand-navy text-white py-2 rounded-lg text-sm font-semibold" href="login.php">Login</a>
      <a class="text-center bg-blue-50 text-brand-navy border border-blue-200 py-2 rounded-lg text-sm font-semibold" href="daftar.php">Daftar Siswa Baru</a>
    <?php endif; ?>
  </div>
</div>
</header>
<!-- END: MainHeader -->
<!-- BEGIN: MainContent -->
<main class="flex-grow">
<!-- BEGIN: HeroSection -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16" data-purpose="hero-section">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
<!-- Hero Text Left -->
<div class="lg:col-span-7 flex flex-col justify-center text-left">
<h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-navy leading-tight md:leading-[1.18] tracking-tight">
  Wujudkan Prestasi<br class="hidden sm:inline">
  dan Masa Depan Gemilang<br class="hidden sm:inline">
  Bersama Tunas Cendekia
</h1>
<p class="mt-5 text-gray-600 text-base sm:text-lg leading-relaxed max-w-xl">
  Bimbingan belajar berkualitas dengan pengajar profesional untuk meraih masa depan terbaik.
</p>
<div class="mt-8 flex flex-wrap items-center gap-4">
<a class="bg-brand-navy hover:bg-brand-navyHover text-white px-6 py-3 rounded-lg text-sm sm:text-base font-semibold shadow transition-colors" href="daftar.php">
  Daftar Sekarang
</a>
<a class="bg-white hover:bg-gray-50 border border-brand-navy text-brand-navy px-6 py-3 rounded-lg text-sm sm:text-base font-semibold shadow-sm transition-colors" href="#tentang-kami">
  Pelajari Lebih Lanjut
</a>
</div>
</div>
<!-- Hero Graphic Illustration Right (Flat vector education theme with mortarboard, books, screen) -->
<div class="lg:col-span-5 flex justify-center items-center" data-purpose="hero-vector-illustration">
<div class="relative w-full max-w-md aspect-[4/3] flex items-center justify-center p-4">
<!-- Background circular accent -->
<div class="absolute inset-0 bg-blue-50/70 rounded-full scale-95 -z-10 blur-xl"></div>
<svg class="w-full h-full text-brand-navy drop-shadow-sm" fill="none" viewBox="0 0 500 380" xmlns="http://www.w3.org/2000/svg">
<!-- Soft Glow Base -->
<ellipse cx="250" cy="330" fill="#e2edf8" rx="200" ry="25"></ellipse>
<!-- Stack of books / base platform -->
<path d="M120 310L250 335L380 310L250 285L120 310Z" fill="#cfe0f2"></path>
<path d="M120 310L250 335V348L120 323V310Z" fill="#1b3a6b"></path>
<path d="M380 310L250 335V348L380 323V310Z" fill="#152f55"></path>
<path d="M135 285L250 307L365 285L250 263L135 285Z" fill="#3b82f6"></path>
<path d="M135 285L250 307V318L135 296V285Z" fill="#1d4ed8"></path>
<path d="M365 285L250 307V318L365 296V285Z" fill="#1e40af"></path>
<!-- Monitor / Digital Classroom Screen -->
<rect fill="#ffffff" height="115" rx="10" stroke="#1b3a6b" stroke-width="6" width="160" x="170" y="125"></rect>
<rect fill="#eff6ff" height="91" rx="4" width="136" x="182" y="137"></rect>
<line stroke="#93c5fd" stroke-linecap="round" stroke-width="4" x1="200" x2="270" y1="160" y2="160"></line>
<line stroke="#bfdbfe" stroke-linecap="round" stroke-width="4" x1="200" x2="300" y1="175" y2="175"></line>
<line stroke="#bfdbfe" stroke-linecap="round" stroke-width="4" x1="200" x2="250" y1="190" y2="190"></line>
<circle cx="295" cy="205" fill="#3b82f6" r="10"></circle>
<!-- Screen Stand -->
<rect fill="#1b3a6b" height="28" width="16" x="242" y="240"></rect>
<ellipse cx="250" cy="268" fill="#1b3a6b" rx="35" ry="7"></ellipse>
<!-- Graduation Cap (Toga) Floating / Crown -->
<g transform="translate(185, 30)">
<!-- Cap Base Diamond -->
<polygon fill="#1b3a6b" points="65,0 130,22 65,44 0,22"></polygon>
<polygon fill="#244882" points="65,2 126,22 65,42 4,22"></polygon>
<!-- Skull Cap Underneath -->
<path d="M28 27C28 27 30 46 65 46C100 46 102 27 102 27V38C102 48 85 58 65 58C45 58 28 48 28 38V27Z" fill="#122544"></path>
<!-- Tassel Ribbon & Cord -->
<path d="M65 22C65 22 40 28 30 42C25 49 22 65 22 75" fill="none" stroke="#f59e0b" stroke-linecap="round" stroke-width="3"></path>
<circle cx="65" cy="22" fill="#f59e0b" r="3.5"></circle>
<polygon fill="#f59e0b" points="18,75 26,75 24,90 20,90"></polygon>
</g>
<!-- Education Elements: Pencil & Stars -->
<g transform="rotate(35 370 140)">
<rect fill="#f59e0b" height="60" rx="3" width="16" x="360" y="110"></rect>
<polygon fill="#fed7aa" points="360,170 376,170 368,185"></polygon>
<polygon fill="#1e293b" points="365,180 371,180 368,185"></polygon>
<rect fill="#e2e8f0" height="12" width="16" x="360" y="110"></rect>
</g>
<!-- Floating Sparkles -->
<path d="M120 150L123 158L131 161L123 164L120 172L117 164L109 161L117 158Z" fill="#3b82f6"></path>
<path d="M375 80L377 85L382 87L377 89L375 94L373 89L368 87L373 85Z" fill="#60a5fa"></path>
<path d="M150 70L152 76L158 78L152 80L150 86L148 80L142 78L148 76Z" fill="#93c5fd"></path>
</svg>
</div>
</div>
</div>
</section>
<!-- END: HeroSection -->

<!-- BEGIN: KeunggulanKamiSection -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="keunggulan" data-purpose="keunggulan-section">
<div class="text-center mb-8">
<h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Keunggulan Kami</h2>
</div>
<!-- 4 Pillars Grid Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
<!-- Card 1: Pengajar Profesional -->
<div class="bg-brand-cardBg rounded-xl p-6 flex flex-col items-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-blue-100 hover:shadow-md transition-shadow">
<div class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-brand-navy shadow-xs mb-4">
<svg class="w-7 h-7 stroke-current" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M12 14l9-5-9-5-9 5 9 5z" stroke-linecap="round" stroke-linejoin="round"></path>
<path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base leading-snug">Pengajar<br>Profesional</h3>
</div>
<!-- Card 2: Metode Belajar Interaktif -->
<div class="bg-brand-cardBg rounded-xl p-6 flex flex-col items-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-blue-100 hover:shadow-md transition-shadow">
<div class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-brand-navy shadow-xs mb-4">
<svg class="w-7 h-7 stroke-current" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-linecap="round" stroke-linejoin="round"></polygon>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base leading-snug">Metode Belajar<br>Interaktif</h3>
</div>
<!-- Card 3: Program Lengkap SD-SMA -->
<div class="bg-brand-cardBg rounded-xl p-6 flex flex-col items-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-blue-100 hover:shadow-md transition-shadow">
<div class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-brand-navy shadow-xs mb-4">
<svg class="w-7 h-7 stroke-current" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base leading-snug">Program Lengkap<br>SD-SMA</h3>
</div>
<!-- Card 4: Pendampingan Personal -->
<div class="bg-brand-cardBg rounded-xl p-6 flex flex-col items-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-blue-100 hover:shadow-md transition-shadow">
<div class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-brand-navy shadow-xs mb-4">
<svg class="w-7 h-7 stroke-current" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base leading-snug">Pendampingan<br>Personal</h3>
</div>
</div>
</section>
<!-- END: KeunggulanKamiSection -->

<!-- BEGIN: ProgramsAndAboutSection -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" id="program-belajar" data-purpose="programs-and-about-wrapper">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
<!-- Left Side: Program Bimbingan Belajar (Span 7) -->
<div class="lg:col-span-7" data-purpose="program-bimbingan-belajar">
<h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Program Bimbingan Belajar</h2>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<!-- Program 1: Reguler -->
<div class="bg-white rounded-xl border border-gray-200/80 p-5 text-center flex flex-col items-center shadow-xs hover:border-blue-400 hover:shadow-md transition-all">
<div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base">Program Reguler</h3>
<p class="text-xs text-gray-500 mt-1">Untuk semua jenjang</p>
</div>
<!-- Program 2: Intensif -->
<div class="bg-white rounded-xl border border-gray-200/80 p-5 text-center flex flex-col items-center shadow-xs hover:border-blue-400 hover:shadow-md transition-all">
<div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base">Program Intensif</h3>
<p class="text-xs text-gray-500 mt-1">Persiapan ujian</p>
</div>
<!-- Program 3: Prestasi -->
<div class="bg-white rounded-xl border border-gray-200/80 p-5 text-center flex flex-col items-center shadow-xs hover:border-blue-400 hover:shadow-md transition-all">
<div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<circle cx="12" cy="12" r="9"></circle>
<circle cx="12" cy="12" r="5"></circle>
<circle cx="12" cy="12" r="1.5"></circle>
</svg>
</div>
<h3 class="font-bold text-gray-900 text-base">Program Prestasi</h3>
<p class="text-xs text-gray-500 mt-1">Meraih hasil terbaik</p>
</div>
</div>
</div>
<!-- Right Side: Tentang Kami (Span 5) -->
<div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200/80 p-6 flex flex-col justify-between shadow-xs" id="tentang-kami" data-purpose="tentang-kami-box">
<div>
<h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Tentang Kami</h2>
<p class="text-gray-600 text-sm leading-relaxed mb-3">
  Bimbel Tunas Cendekia hadir untuk mendukung setiap siswa meraih prestasi akademik terbaik melalui metode pembelajaran terarah, terstruktur, dan dibimbing oleh tentor berpengalaman.
</p>
<a class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors mb-4 group" href="daftar.php">
  Daftar Sekarang 
  <span class="ml-1 tracking-normal transition-transform group-hover:translate-x-1">→</span>
</a>
</div>
<!-- Building/School Facility Illustration Vector -->
<div class="w-full h-32 rounded-xl overflow-hidden bg-slate-100 flex items-center justify-center relative border border-slate-200">
<svg class="w-full h-full object-cover" fill="none" viewBox="0 0 400 150" xmlns="http://www.w3.org/2000/svg">
<rect fill="#f1f5f9" height="150" width="400"></rect>
<!-- Sky / Clouds -->
<circle cx="80" cy="30" fill="#e2e8f0" r="15"></circle>
<circle cx="95" cy="25" fill="#e2e8f0" r="20"></circle>
<circle cx="115" cy="30" fill="#e2e8f0" r="15"></circle>
<!-- Trees -->
<circle cx="45" cy="95" fill="#a7f3d0" r="25"></circle>
<rect fill="#78350f" height="35" width="6" x="42" y="95"></rect>
<circle cx="355" cy="95" fill="#a7f3d0" r="25"></circle>
<rect fill="#78350f" height="35" width="6" x="352" y="95"></rect>
<!-- Main Building (Bimbel Centre) -->
<rect fill="#cbd5e1" height="85" rx="3" stroke="#334155" stroke-width="2" width="240" x="80" y="45"></rect>
<!-- Building Roof Line / Header -->
<rect fill="#1e3a8a" height="10" width="250" x="75" y="40"></rect>
<!-- Windows in Grid -->
<rect fill="#60a5fa" height="20" rx="2" width="24" x="100" y="58"></rect>
<rect fill="#60a5fa" height="20" rx="2" width="24" x="135" y="58"></rect>
<rect fill="#60a5fa" height="20" rx="2" width="24" x="170" y="58"></rect>
<rect fill="#60a5fa" height="20" rx="2" width="24" x="205" y="58"></rect>
<rect fill="#60a5fa" height="20" rx="2" width="24" x="240" y="58"></rect>
<rect fill="#60a5fa" height="20" rx="2" width="24" x="275" y="58"></rect>
<!-- Second Row Windows -->
<rect fill="#93c5fd" height="20" rx="2" width="24" x="100" y="88"></rect>
<rect fill="#93c5fd" height="20" rx="2" width="24" x="135" y="88"></rect>
<rect fill="#93c5fd" height="20" rx="2" width="24" x="240" y="88"></rect>
<rect fill="#93c5fd" height="20" rx="2" width="24" x="275" y="88"></rect>
<!-- Main Glass Entrance Door -->
<rect fill="#38bdf8" height="42" stroke="#1e293b" stroke-width="2" width="40" x="180" y="88"></rect>
<line stroke="#1e293b" stroke-width="1.5" x1="200" x2="200" y1="88" y2="130"></line>
<!-- Foreground ground -->
<rect fill="#e2e8f0" height="20" width="400" y="130"></rect>
<line stroke="#94a3b8" stroke-width="2" x1="0" x2="400" y1="130" y2="130"></line>
</svg>
</div>
</div>
</div>
</section>
<!-- END: ProgramsAndAboutSection -->
</main>
<!-- END: MainContent -->

<!-- BEGIN: MainFooter -->
<footer class="w-full bg-brand-navyDark text-white pt-12 pb-8 mt-12" id="kontak" data-purpose="site-footer">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-blue-900/60">
<!-- Left Column: Logo & Slogan (Span 5) -->
<div class="md:col-span-5 flex flex-col justify-between">
<div class="space-y-3">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full overflow-hidden bg-white p-0.5 flex items-center justify-center shrink-0">
<img alt="Logo Bimbel Tunas Cendekia" class="w-full h-full object-contain" src="assets/images/logo.png">
</div>
<div class="flex flex-col leading-tight">
<span class="text-xs uppercase tracking-wider text-blue-300 font-semibold">Bimbel</span>
<span class="text-base font-bold text-white tracking-wide">Tunas Cendekia</span>
</div>
</div>
<p class="text-xs sm:text-sm text-blue-200/90 font-medium tracking-wide">
  Belajar • Berkembang • Berprestasi
</p>
</div>
<p class="text-xs text-blue-300/70 mt-6 md:mt-0">
  © 2025 Bimbel Tunas Cendekia. All rights reserved.
</p>
</div>
<!-- Middle Column: Kontak Kami (Span 4) -->
<div class="md:col-span-4 space-y-3">
<h4 class="text-base font-bold text-white tracking-wide">Kontak Kami</h4>
<ul class="space-y-2 text-xs sm:text-sm text-blue-100/90">
<li class="flex items-center gap-2.5">
<svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
<span>0812-3456-7890</span>
</li>
<li class="flex items-center gap-2.5">
<svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
<span>info@tunascendekia.sch.id</span>
</li>
<li class="flex items-start gap-2.5">
<svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
<span>Jl. Pendidikan No. 12, Jakarta</span>
</li>
</ul>
</div>
<!-- Right Column: Ikuti Kami (Span 3) -->
<div class="md:col-span-3 space-y-3">
<h4 class="text-base font-bold text-white tracking-wide">Ikuti Kami</h4>
<div class="flex items-center gap-3">
<!-- Facebook -->
<a aria-label="Facebook" class="w-9 h-9 rounded-full bg-blue-900/60 hover:bg-blue-600 flex items-center justify-center transition-colors" href="#">
<svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24">
<path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.6 5H18V0h-3.808C10.596 0 9 1.583 9 4.615V8z"></path>
</svg>
</a>
<!-- Twitter/X -->
<a aria-label="Twitter" class="w-9 h-9 rounded-full bg-blue-900/60 hover:bg-blue-600 flex items-center justify-center transition-colors" href="#">
<svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24">
<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.936 9.936 0 0024 4.59z"></path>
</svg>
</a>
<!-- Instagram -->
<a aria-label="Instagram" class="w-9 h-9 rounded-full bg-blue-900/60 hover:bg-blue-600 flex items-center justify-center transition-colors" href="#">
<svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24">
<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path>
</svg>
</a>
<!-- YouTube -->
<a aria-label="YouTube" class="w-9 h-9 rounded-full bg-blue-900/60 hover:bg-blue-600 flex items-center justify-center transition-colors" href="#">
<svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24">
<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path>
</svg>
</a>
</div>
</div>
</div>
</div>
</footer>
<!-- END: MainFooter -->

<script>
  const mobileBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileBtn && mobileMenu) {
    mobileBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }
</script>
</body>
</html>
