<?php
// Endpoint: mencatat aktivitas/pelanggaran siswa saat ujian (dipanggil otomatis dari kerjakan.php)
require_once '../includes/auth.php';
cek_login_siswa();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$jenis_valid = ['keluar_tab', 'keluar_fullscreen', 'pindah_jendela', 'kembali', 'shortcut', 'salin_tempel', 'klik_kanan'];
$jenis      = $_POST['jenis'] ?? '';
$id_ujian   = (int)($_POST['id_ujian'] ?? 0);
$id_siswa   = (int)$_SESSION['siswa_id'];
$keterangan = mb_substr(trim($_POST['keterangan'] ?? ''), 0, 255);
$durasi     = isset($_POST['durasi']) ? max(0, min(86400, (int)$_POST['durasi'])) : null;

if (!in_array($jenis, $jenis_valid, true) || $id_ujian <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tb_pelanggaran (id_siswa, id_ujian, jenis, keterangan, durasi_detik) VALUES (?,?,?,?,?)");
$stmt->bind_param('iissi', $id_siswa, $id_ujian, $jenis, $keterangan, $durasi);
$stmt->execute();

echo json_encode(['ok' => true]);
