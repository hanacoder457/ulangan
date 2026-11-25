<?php
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cek_login_admin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function cek_login_siswa() {
    if (!isset($_SESSION['siswa_id'])) {
        header('Location: login.php');
        exit;
    }

    global $conn;
    if (!isset($conn)) {

        require_once __DIR__ . '/../config/database.php';
        $GLOBALS['conn'] = $conn;
    }
    try {
        $stmt = $conn->prepare("SELECT status_akun FROM tb_siswa WHERE id_siswa = ?");
        $id = (int)$_SESSION['siswa_id'];
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    } catch (Throwable $e) {
        return;
    }

    if (!$row || ($row['status_akun'] ?? 'aktif') === 'terkunci') {
        $terkunci = (bool)$row;
        session_unset();
        session_destroy();
        header('Location: login.php' . ($terkunci ? '?terkunci=1' : ''));
        exit;
    }
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
