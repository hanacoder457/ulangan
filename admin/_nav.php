<?php $halaman_ini = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><img src="../assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="brand-logo"> Admin Ulangan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $halaman_ini==='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= in_array($halaman_ini,['ujian.php','soal.php','import_pdf.php'])?'active':'' ?>" href="ujian.php"><i class="bi bi-journal-text"></i> Kelola Ujian</a></li>
        <li class="nav-item"><a class="nav-link <?= in_array($halaman_ini,['rekap.php','detail_jawaban.php'])?'active':'' ?>" href="rekap.php"><i class="bi bi-clipboard-data"></i> Rekap Nilai</a></li>
        <li class="nav-item">
          <a class="nav-link <?= $halaman_ini==='siswa.php'?'active':'' ?>" href="siswa.php">
            <i class="bi bi-people"></i> Akun Siswa
            <?php
              if (isset($conn)) {
                  $jml_terkunci_nav = $conn->query("SELECT COUNT(*) AS jml FROM tb_siswa WHERE status_akun = 'terkunci'")->fetch_assoc()['jml'] ?? 0;
                  if ($jml_terkunci_nav > 0) {
                      echo '<span class="badge bg-danger ms-1">' . (int)$jml_terkunci_nav . '</span>';
                  }
              }
            ?>
          </a>
        </li>
      </ul>
      <span class="navbar-text text-white me-3">
        <i class="bi bi-person-circle"></i> <?= e($_SESSION['admin_nama']) ?>
      </span>
      <a href="ganti_password.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-key"></i> Ganti Password</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </div>
</nav>
