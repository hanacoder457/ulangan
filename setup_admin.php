<?php
// =========================================================
// SETUP AKUN ADMIN DEFAULT
// Jalankan file ini SATU KALI lewat browser setelah database
// selesai di-import, untuk membuat akun admin pertama.
//
// Setelah berhasil (atau kalau akun admin sudah ada),
// SEGERA HAPUS FILE INI dari server demi keamanan.
// =========================================================
require_once 'config/database.php';

$username_default = 'admin';
$password_default = 'admin123';
$nama_default      = 'Administrator';

$pesan = '';
$sudah_ada = false;

// Cek apakah akun admin dengan username ini sudah ada
$cek = $conn->prepare("SELECT id_admin FROM tb_admin WHERE username = ?");
$cek->bind_param('s', $username_default);
$cek->execute();
if ($cek->get_result()->num_rows > 0) {
    $sudah_ada = true;
} else {
    $hash = password_hash($password_default, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO tb_admin (username, password, nama) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username_default, $hash, $nama_default);
    if ($stmt->execute()) {
        $pesan = 'Akun admin berhasil dibuat.';
    } else {
        $pesan = 'Gagal membuat akun admin: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Akun Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f4f6f9;}</style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:100vh;">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">

          <?php if ($sudah_ada): ?>
            <h4 class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Akun Admin Sudah Ada</h4>
            <p>Username <code><?= htmlspecialchars($username_default) ?></code> sudah terdaftar di database, jadi tidak dibuat ulang (untuk menghindari menimpa password yang sudah pernah diganti).</p>
            <p class="mb-0">Silakan langsung login, atau kalau lupa password, reset manual lewat phpMyAdmin.</p>

          <?php elseif (strpos($pesan, 'berhasil') !== false): ?>
            <h4 class="text-success"><i class="bi bi-check-circle-fill"></i> Akun Admin Berhasil Dibuat</h4>
            <p>Gunakan kredensial berikut untuk login:</p>
            <table class="table table-bordered w-auto">
              <tr><th class="bg-light">Username</th><td><code><?= htmlspecialchars($username_default) ?></code></td></tr>
              <tr><th class="bg-light">Password</th><td><code><?= htmlspecialchars($password_default) ?></code></td></tr>
            </table>
            <a href="admin/login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Login Sekarang</a>

          <?php else: ?>
            <h4 class="text-danger"><i class="bi bi-x-circle-fill"></i> Gagal</h4>
            <p><?= htmlspecialchars($pesan) ?></p>
            <p class="text-muted mb-0">Pastikan database sudah di-import dengan benar (file <code>database.sql</code>) dan konfigurasi di <code>config/database.php</code> sudah sesuai.</p>
          <?php endif; ?>

          <hr>
          <div class="alert alert-danger mb-0">
            <i class="bi bi-shield-exclamation"></i>
            <strong>Penting:</strong> Setelah ini, segera <strong>hapus file <code>setup_admin.php</code></strong> dari server (lewat File Manager / FTP) supaya orang lain tidak bisa membuat/melihat akun admin lewat halaman ini. Sebaiknya juga segera ganti password default setelah login pertama.
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
