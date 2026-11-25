<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tb_admin WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_nama'] = $admin['nama'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Username atau password salah.';
}

$judul_halaman = 'Login Admin';
require_once '../includes/header.php';
?>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:90vh;">
    <div class="col-md-4">
      <div class="card auth-card">
        <div class="card-body">
          <img src="../assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="auth-logo">
          <h4 class="mb-3 text-center">Login Admin</h4>
          <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" required data-fokus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
          </form>
          <div class="text-center mt-3">
            <a href="../index.php"><i class="bi bi-arrow-left"></i> Kembali ke beranda</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
