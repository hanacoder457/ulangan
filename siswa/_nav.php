<?php $halaman_ini = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><img src="../assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="brand-logo"> Ulangan Online</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navSiswa">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navSiswa">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $halaman_ini==='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="bi bi-list-check"></i> Ujian Saya</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_ini==='nilai.php'?'active':'' ?>" href="nilai.php"><i class="bi bi-graph-up"></i> Nilai Saya</a></li>
      </ul>
      <span class="navbar-text text-white me-3">
        <i class="bi bi-person-circle"></i> <?= e($_SESSION['siswa_nama']) ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </div>
</nav>
