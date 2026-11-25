<?php
// =========================================================
// KONFIGURASI DATABASE
// Ganti sesuai data hosting Anda (cPanel > MySQL Databases)
// =========================================================
$host   = 'sql102.infinityfree.com';
$dbname = 'if0_43351293_db_ulangan';   // sesuaikan, biasanya diawali namauser_
$user   = 'if0_43351293';         // sesuaikan
$pass   = 'bKv5mrHWEasErEr';              // sesuaikan

// Membuat error database (query salah, kolom tidak ada, dll) langsung
// terlihat jelas pesannya, bukan error PHP yang membingungkan.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8mb4');
    // Samakan zona waktu MySQL dengan PHP (WIB) supaya NOW() / TIMESTAMP tidak selisih 7 jam
    $conn->query("SET time_zone = '+07:00'");
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}