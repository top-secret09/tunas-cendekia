<?php
// update_profile.php
require_once __DIR__ . '/config/database.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$user = get_current_user_data();
$id_akun = (int)($user['id_akun'] ?? 0);
$role = $user['peran'] ?? '';

$nama = db_escape($_POST['nama'] ?? '');
$no_telepon = db_escape($_POST['no_telepon'] ?? '');
$alamat = db_escape($_POST['alamat'] ?? '');
$password_baru = $_POST['password_baru'] ?? '';

// Update Password in akun if provided
if (!empty($password_baru)) {
    $pw_escaped = db_escape($password_baru);
    db_query("UPDATE akun SET password = '$pw_escaped' WHERE id_akun = $id_akun");
}

if ($role === 'Admin') {
    $_SESSION['user']['nama'] = !empty($nama) ? $nama : 'Administrator Utama';
} 
else if ($role === 'Guru') {
    $pendidikan = db_escape($_POST['pendidikan_terakhir'] ?? '');
    $bidang_mapel = db_escape($_POST['bidang_mapel'] ?? '');

    db_query("UPDATE guru SET 
                nama_guru = '$nama', 
                no_telepon = '$no_telepon', 
                alamat = '$alamat', 
                pendidikan_terakhir = '$pendidikan', 
                bidang_mapel = '$bidang_mapel' 
              WHERE id_akun = $id_akun");

    $_SESSION['user']['nama'] = $nama;
    $_SESSION['user']['no_telepon'] = $no_telepon;
    $_SESSION['user']['alamat'] = $alamat;
    $_SESSION['user']['pendidikan_terakhir'] = $pendidikan;
    $_SESSION['user']['bidang_mapel'] = $bidang_mapel;
} 
else if ($role === 'Siswa') {
    $tgl_lahir = db_escape($_POST['tanggal_lahir'] ?? '');
    $jk = db_escape($_POST['jenis_kelamin'] ?? 'laki-laki');

    db_query("UPDATE siswa SET 
                nama_siswa = '$nama', 
                no_telepon = '$no_telepon', 
                alamat = '$alamat', 
                tanggal_lahir = '$tgl_lahir', 
                jenis_Kelamin = '$jk' 
              WHERE id_akun = $id_akun");

    $_SESSION['user']['nama'] = $nama;
    $_SESSION['user']['no_telepon'] = $no_telepon;
    $_SESSION['user']['alamat'] = $alamat;
    $_SESSION['user']['tanggal_lahir'] = $tgl_lahir;
    $_SESSION['user']['jenis_Kelamin'] = $jk;
}

$referer = $_SERVER['HTTP_REFERER'] ?? ($role === 'Admin' ? 'admin/index.php' : ($role === 'Guru' ? 'guru/index.php' : 'siswa/index.php'));
header("Location: " . $referer);
exit;
?>