<?php
require_once '../includes/auth.php';
cek_login_siswa();
require_once '../config/database.php';

$id_siswa = $_SESSION['siswa_id'];

$stmt = $conn->prepare("SELECT kelas FROM tb_siswa WHERE id_siswa = ?");
$stmt->bind_param('i', $id_siswa);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
$kelas_siswa = $siswa['kelas'];

$sql = "SELECT u.* FROM tb_ujian u
        WHERE u.status = 'aktif'
        AND (u.kelas_target = 'semua' OR u.kelas_target = ? OR u.kelas_target = SUBSTRING_INDEX(?, ' ', 1))
        AND (SELECT COUNT(*) FROM tb_soal s WHERE s.id_ujian = u.id_ujian) > 0
        AND NOT EXISTS (SELECT 1 FROM tb_hasil h WHERE h.id_ujian = u.id_ujian AND h.id_siswa = ?)
        ORDER BY u.created_at ASC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssi', $kelas_siswa, $kelas_siswa, $id_siswa);
$stmt->execute();
$ujian = $stmt->get_result()->fetch_assoc();

if ($ujian) {
    header('Location: kerjakan.php?id_ujian=' . $ujian['id_ujian']);
    exit;
}

// Bagian Ujian
$judul_halaman = 'Ujian Saya';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:60vh;">
    <div class="col-md-6 text-center">
      <i class="bi bi-hourglass-split" style="font-size:3.5rem;color:#6c757d;"></i>
      <h4 class="mt-3">Belum Ada Ujian Tersedia</h4>
      <p class="text-muted mb-1">Kelas Anda: <strong><?= e($kelas_siswa ?: '-') ?></strong></p>
      <p class="text-muted">Saat ini tidak ada ujian aktif</p>
      <a href="nilai.php" class="btn btn-outline-success"><i class="bi bi-graph-up"></i> Lihat Nilai Saya</a>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>