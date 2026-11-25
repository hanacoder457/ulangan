<?php
require_once '../includes/auth.php';
cek_login_siswa();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$id_siswa = $_SESSION['siswa_id'];
$id_ujian = (int)$_POST['id_ujian'];
$jawaban_user = $_POST['jawaban'] ?? []; // [id_soal => 'A'/'B'/'C'/'D']

// Cegah double submit / kecurangan (sudah pernah mengerjakan)
$cek = $conn->prepare("SELECT id_hasil FROM tb_hasil WHERE id_siswa = ? AND id_ujian = ?");
$cek->bind_param('ii', $id_siswa, $id_ujian);
$cek->execute();
if ($cek->get_result()->num_rows > 0) {
    header('Location: nilai.php');
    exit;
}

$stmt = $conn->prepare("SELECT id_soal, kunci_jawaban FROM tb_soal WHERE id_ujian = ?");
$stmt->bind_param('i', $id_ujian);
$stmt->execute();
$soal_list = $stmt->get_result();

$jumlah_soal = 0;
$jumlah_benar = 0;
$detail = []; // untuk disimpan ke tb_jawaban setelah tb_hasil dibuat

while ($s = $soal_list->fetch_assoc()) {
    $jumlah_soal++;
    $id_soal = $s['id_soal'];
    $jawab_siswa = $jawaban_user[$id_soal] ?? '-';
    $benar = ($jawab_siswa === $s['kunci_jawaban']) ? 1 : 0;
    if ($benar) $jumlah_benar++;
    $detail[] = [$id_soal, $jawab_siswa, $benar];
}

$nilai = $jumlah_soal > 0 ? round(($jumlah_benar / $jumlah_soal) * 100, 2) : 0;

// Simpan hasil
$stmt = $conn->prepare("INSERT INTO tb_hasil (id_siswa, id_ujian, jumlah_benar, jumlah_soal, nilai) VALUES (?,?,?,?,?)");
$stmt->bind_param('iiiid', $id_siswa, $id_ujian, $jumlah_benar, $jumlah_soal, $nilai);
$stmt->execute();
$id_hasil = $conn->insert_id;

// Simpan detail jawaban
$stmt = $conn->prepare("INSERT INTO tb_jawaban (id_hasil, id_soal, jawaban_siswa, benar) VALUES (?,?,?,?)");
foreach ($detail as $d) {
    $stmt->bind_param('iisi', $id_hasil, $d[0], $d[1], $d[2]);
    $stmt->execute();
}

// Jika jawaban ini dikumpulkan paksa karena siswa melebihi batas pelanggaran
// (berpindah tab berkali-kali): simpan jawabannya sebagai
// final, KUNCI akunnya (supaya tidak bisa login lagi tanpa persetujuan admin),
// lalu logout paksa.
if (($_POST['paksa_logout'] ?? '') === '1') {
    $alasan = 'Melebihi batas pelanggaran (berpindah tab / meninggalkan halaman ujian) saat mengerjakan ujian.';

    // Kunci akun. Dibungkus try/catch supaya error database tidak membuat halaman 500.
    try {
        $stmt = $conn->prepare("UPDATE tb_siswa SET status_akun = 'terkunci', alasan_terkunci = ?, terkunci_pada = NOW() WHERE id_siswa = ?");
        $stmt->bind_param('si', $alasan, $id_siswa);
        $stmt->execute();
    } catch (Throwable $e) {
        error_log('proses_jawaban: gagal mengunci akun: ' . $e->getMessage());
    }

    // Catat pengeluaran paksa. Jika tabel di hosting belum punya jenis 'dikeluarkan_paksa'
    // (database lama), pencatatan dilewati dan siswa tetap dikeluarkan dengan benar.
    try {
        $jenis = 'dikeluarkan_paksa';
        $stmt = $conn->prepare("INSERT INTO tb_pelanggaran (id_siswa, id_ujian, jenis) VALUES (?,?,?)");
        $stmt->bind_param('iis', $id_siswa, $id_ujian, $jenis);
        $stmt->execute();
    } catch (Throwable $e) {
        error_log('proses_jawaban: gagal mencatat pelanggaran: ' . $e->getMessage());
    }

    session_destroy();
    header('Location: login.php?pelanggaran=1');
    exit;
}

header('Location: nilai.php?selesai=' . $id_hasil);
exit;