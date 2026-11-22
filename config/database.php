<?php
// config/database.php
// Konfigurasi Database & Helper Functions untuk Sistem Informasi Bimbel Tunas Cendekia

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_tunas_cendekia';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error . ". Pastikan MySQL di XAMPP sudah berjalan.");
}

$conn->set_charset("utf8mb4");

// PDO Connection
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi database PDO gagal: " . $e->getMessage() . ". Pastikan MySQL di XAMPP sudah berjalan.");
}

// Helper Query functions
function db_query($sql) {
    global $conn;
    $res = $conn->query($sql);
    if (!$res) {
        error_log("Database Error: " . $conn->error . " in query: " . $sql);
    }
    return $res;
}

function db_fetch_all($sql) {
    $res = db_query($sql);
    $data = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function db_fetch_one($sql) {
    $res = db_query($sql);
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc();
    }
    return null;
}

function db_escape($str) {
    global $conn;
    return $conn->real_escape_string(trim($str));
}

// Auth Helper
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id_akun']);
}

function get_current_user_data() {
    if (!is_logged_in()) return null;
    return $_SESSION['user'];
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function check_access($allowed_roles = []) {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit;
    }
    $role = $_SESSION['user']['peran'] ?? '';
    if (!empty($allowed_roles) && !in_array($role, $allowed_roles)) {
        if ($role === 'Admin') header("Location: ../admin/index.php");
        else if ($role === 'Guru') header("Location: ../guru/index.php");
        else header("Location: ../siswa/index.php");
        exit;
    }
}

function require_role($roles) {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit;
    }
    $current_role = strtolower($_SESSION['user']['peran'] ?? $_SESSION['user']['role'] ?? '');
    $allowed = is_array($roles) ? array_map('strtolower', $roles) : [strtolower($roles)];
    if (!in_array($current_role, $allowed)) {
        if ($current_role === 'admin') {
            header("Location: ../admin/index.php");
        } elseif ($current_role === 'guru') {
            header("Location: ../guru/index.php");
        } else {
            header("Location: ../siswa/index.php");
        }
        exit;
    }
}

// Formatting Helpers
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function format_tanggal_indo($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecah = explode('-', $tanggal);
    if (count($pecah) === 3) {
        $bln = (int)$pecah[1];
        return $pecah[2] . ' ' . ($bulan[$bln] ?? '') . ' ' . $pecah[0];
    }
    return $tanggal;
}

function get_hari_tanggal_sekarang() {
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $h = $hari[date('l')];
    $t = date('j');
    $b = $bulan[(int)date('n')];
    $y = date('Y');
    return "$h, $t $b $y";
}

// Notification Provider based on role & DB data
function get_user_notifications() {
    $user = get_current_user_data();
    if (!$user) return [];

    $notifications = [];
    $role = $user['peran'];

    if ($role === 'Admin') {
        // 1. Pembayaran Masuk
        $bayar = db_fetch_all("SELECT p.*, s.nama_siswa, k.nama_kelas 
                               FROM pembayaran p 
                               JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran 
                               JOIN siswa s ON pd.id_siswa = s.id_siswa 
                               JOIN kelas k ON pd.id_kelas = k.id_kelas 
                               ORDER BY p.id_pembayaran DESC LIMIT 3");
        foreach ($bayar as $b) {
            $status_txt = $b['status_pembayaran'] == 'Lunas' ? 'Lunas' : 'Menunggu Konfirmasi';
            $notifications[] = [
                'type' => 'pembayaran',
                'title' => 'Pembayaran SPP (' . $b['metode_bayar'] . ')',
                'desc' => $b['nama_siswa'] . ' - ' . $b['nama_kelas'] . ' (' . format_rupiah($b['jumlah_bayar']) . ')',
                'time' => format_tanggal_indo($b['tanggal_bayar']),
                'unread' => ($b['status_pembayaran'] == 'Menunggu'),
                'icon' => 'account_balance_wallet',
                'color' => 'bg-blue-100 text-blue-600'
            ];
        }

        // 2. Pendaftaran Baru
        $daftar = db_fetch_all("SELECT pd.*, s.nama_siswa, k.nama_kelas 
                                FROM pendaftaran pd 
                                JOIN siswa s ON pd.id_siswa = s.id_siswa 
                                JOIN kelas k ON pd.id_kelas = k.id_kelas 
                                ORDER BY pd.id_pendaftaran DESC LIMIT 3");
        foreach ($daftar as $d) {
            $notifications[] = [
                'type' => 'pendaftaran',
                'title' => 'Pendaftaran Siswa Baru',
                'desc' => $d['nama_siswa'] . ' mendaftar di ' . $d['nama_kelas'],
                'time' => format_tanggal_indo($d['tanggal_daftar']),
                'unread' => ($d['status_pendaftaran'] == 'Menunggu'),
                'icon' => 'person_add',
                'color' => 'bg-emerald-100 text-emerald-600'
            ];
        }

        // 3. Kehadiran
        $hadir = db_fetch_all("SELECT kh.*, s.nama_siswa 
                               FROM kehadiran kh 
                               JOIN siswa s ON kh.id_siswa = s.id_siswa 
                               ORDER BY kh.id_kehadiran DESC LIMIT 2");
        foreach ($hadir as $h) {
            $notifications[] = [
                'type' => 'presensi',
                'title' => 'Presensi Siswa: ' . $h['status_kehadiran'],
                'desc' => $h['nama_siswa'] . ' (' . $h['status_kehadiran'] . ') pada sesi ' . substr($h['jam_masuk'] ?? '08:00', 0, 5),
                'time' => format_tanggal_indo($h['tanggal_kehadiran']),
                'unread' => false,
                'icon' => 'assignment_turned_in',
                'color' => 'bg-amber-100 text-amber-600'
            ];
        }
    } 
    else if ($role === 'Guru') {
        $id_guru = $user['id_guru'] ?? 0;
        // Jadwal Mengajar
        $jadwal = db_fetch_all("SELECT j.*, m.nama_mapel, k.nama_kelas 
                                FROM jadwal j 
                                JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
                                LEFT JOIN kelas k ON j.id_kelas = k.id_kelas 
                                WHERE j.id_guru = $id_guru 
                                ORDER BY j.id_jadwal DESC LIMIT 3");
        foreach ($jadwal as $j) {
            $notifications[] = [
                'type' => 'jadwal',
                'title' => 'Jadwal Mengajar: ' . $j['nama_mapel'],
                'desc' => 'Hari ' . $j['hari'] . ' (' . substr($j['jam_mulai'],0,5) . ' - ' . substr($j['jam_selesai'],0,5) . ') Kelas ' . ($j['nama_kelas'] ?? 'Reguler'),
                'time' => 'Minggu ini',
                'unread' => true,
                'icon' => 'calendar_month',
                'color' => 'bg-blue-100 text-blue-600'
            ];
        }
        // Hasil Latihan
        $hasil = db_fetch_all("SELECT h.*, s.nama_siswa, l.judul_latihan 
                               FROM hasil_latihan h 
                               JOIN siswa s ON h.id_siswa = s.id_siswa 
                               JOIN latihan_soal l ON h.id_latihan = l.id_latihan 
                               WHERE l.id_guru = $id_guru 
                               ORDER BY h.id_hasil DESC LIMIT 3");
        foreach ($hasil as $hs) {
            $notifications[] = [
                'type' => 'hasil',
                'title' => 'Jawaban Masuk: ' . $hs['nama_siswa'],
                'desc' => $hs['judul_latihan'] . ' (Nilai: ' . $hs['nilai'] . ')',
                'time' => format_tanggal_indo($hs['tanggal_pengerjaan']),
                'unread' => false,
                'icon' => 'verified',
                'color' => 'bg-emerald-100 text-emerald-600'
            ];
        }
    } 
    else if ($role === 'Siswa') {
        $id_siswa = $user['id_siswa'] ?? 0;
        // Pendaftaran & Pembayaran Siswa
        $pendaftarans = db_fetch_all("SELECT pd.*, k.nama_kelas, p.status_pembayaran 
                                      FROM pendaftaran pd 
                                      JOIN kelas k ON pd.id_kelas = k.id_kelas 
                                      LEFT JOIN pembayaran p ON pd.id_pendaftaran = p.id_pendaftaran 
                                      WHERE pd.id_siswa = $id_siswa 
                                      ORDER BY pd.id_pendaftaran DESC LIMIT 2");
        foreach ($pendaftarans as $p) {
            $notifications[] = [
                'type' => 'status_daftar',
                'title' => 'Status Pendaftaran ' . $p['nama_kelas'],
                'desc' => 'Status Pendaftaran: ' . $p['status_pendaftaran'] . ' | Pembayaran: ' . ($p['status_pembayaran'] ?? 'Belum Lunas'),
                'time' => format_tanggal_indo($p['tanggal_daftar']),
                'unread' => ($p['status_pendaftaran'] == 'Menunggu'),
                'icon' => 'school',
                'color' => 'bg-blue-100 text-blue-600'
            ];
        }
        // Latihan Soal Tersedia
        $latihan = db_fetch_all("SELECT l.*, m.nama_mapel 
                                 FROM latihan_soal l 
                                 JOIN mata_pelajaran m ON l.id_mapel = m.id_mapel 
                                 ORDER BY l.id_latihan DESC LIMIT 3");
        foreach ($latihan as $l) {
            $notifications[] = [
                'type' => 'latihan',
                'title' => 'Latihan Soal: ' . $l['nama_mapel'],
                'desc' => $l['judul_latihan'] . ' telah ditambahkan',
                'time' => format_tanggal_indo($l['tanggal_dibuat']),
                'unread' => true,
                'icon' => 'assignment',
                'color' => 'bg-amber-100 text-amber-600'
            ];
        }
    }

    return $notifications;
}
?>