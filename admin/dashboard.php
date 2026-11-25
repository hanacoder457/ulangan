  <?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';

$jumlah_ujian  = $conn->query("SELECT COUNT(*) c FROM tb_ujian")->fetch_assoc()['c'];
$jumlah_soal   = $conn->query("SELECT COUNT(*) c FROM tb_soal")->fetch_assoc()['c'];
$jumlah_siswa  = $conn->query("SELECT COUNT(*) c FROM tb_siswa")->fetch_assoc()['c'];
$jumlah_hasil  = $conn->query("SELECT COUNT(*) c FROM tb_hasil")->fetch_assoc()['c'];

$judul_halaman = 'Dashboard';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <h3 class="mb-4">Dashboard Admin</h3>
  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card text-white bg-primary">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-2 fw-bold"><?= $jumlah_ujian ?></div>
            <div>Paket Ujian</div>
          </div>
          <i class="bi bi-journal-text fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-success">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-2 fw-bold"><?= $jumlah_soal ?></div>
            <div>Total Soal</div>
          </div>
          <i class="bi bi-list-check fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-info">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-2 fw-bold"><?= $jumlah_siswa ?></div>
            <div>Siswa Terdaftar</div>
          </div>
          <i class="bi bi-people fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-warning">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-2 fw-bold"><?= $jumlah_hasil ?></div>
            <div>Ujian Dikerjakan</div>
          </div>
          <i class="bi bi-bar-chart fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
