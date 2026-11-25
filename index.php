<?php
require_once 'includes/auth.php';
$judul_halaman = 'Beranda';
require_once 'includes/header.php';
?>

<a href="admin/login.php" class=" position-absolute top-0 end-0 m-3" style="z-index:10;">
  <i class="bi bi-person-badge"></i>
</a>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:90vh;">
    <div class="col-md-8 col-lg-6 text-center">
      <img src="assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="hero-logo">
      <h1 class="mt-2 mb-2">Website Ulangan Online</h1>
      
      <div class="row g-3 justify-content-center">
        <div class="col-8 col-md-6">
          <a href="siswa/login.php" class="btn btn-success w-100 py-3">
            <i class="bi bi-person fs-4 d-block mb-1"></i> Login Siswa
          </a>
        </div>
      </div>
      <p class="mt-4 mb-5"><a href="siswa/register.php">Belum punya akun siswa? Daftar di sini</a></p>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
