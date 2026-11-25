<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (isset($_SESSION['siswa_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$info = isset($_GET['daftar']) ? 'Pendaftaran berhasil! Silakan login.' : '';
if (isset($_GET['terkunci'])) {
    $error = 'Akun Anda dikunci oleh sistem/admin sehingga sesi ujian dihentikan. Hubungi admin/guru untuk membuka kembali akun Anda.';
} elseif (isset($_GET['pelanggaran'])) {
    $error = 'Anda dikeluarkan otomatis karena melebihi batas pelanggaran (berpindah tab / meninggalkan halaman ujian) saat mengerjakan ujian. Jawaban terakhir Anda sudah tersimpan.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tb_siswa WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $siswa = $result->fetch_assoc();
        if (password_verify($password, $siswa['password'])) {
            if (($siswa['status_akun'] ?? 'aktif') === 'terkunci') {
                $error = 'Akun Anda dikunci karena terdeteksi pelanggaran saat ujian ('
                    . e($siswa['alasan_terkunci'] ?: 'melebihi batas pelanggaran')
                    . '). Hubungi admin/guru untuk membuka kembali akun Anda.';
            } else {
                $_SESSION['siswa_id'] = $siswa['id_siswa'];
                $_SESSION['siswa_nama'] = $siswa['nama'];
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Username atau password salah.';
    }
}

$judul_halaman = 'Login Siswa';
require_once '../includes/header.php';
?>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:90vh;">
    <div class="col-md-4">
      <div class="card auth-card">
        <div class="card-body">
          <img src="../assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="auth-logo">
          <h4 class="mb-3 text-center">Login Siswa</h4>
          <?php if ($info): ?><div class="alert alert-success py-2"><?= e($info) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" required data-fokus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Masuk</button>
          </form>
          <div class="text-center mt-3">
            <a href="register.php">Belum punya akun? Daftar</a><br>
            <a href="../index.php"><i class="bi bi-arrow-left"></i> Kembali ke beranda</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
