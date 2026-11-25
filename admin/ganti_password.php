<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';

$sukses = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lama    = $_POST['password_lama'] ?? '';
    $baru    = $_POST['password_baru'] ?? '';
    $konfirm = $_POST['password_konfirmasi'] ?? '';
    $id_admin = (int)$_SESSION['admin_id'];

    $stmt = $conn->prepare("SELECT password FROM tb_admin WHERE id_admin = ?");
    $stmt->bind_param('i', $id_admin);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if (!$admin || !password_verify($lama, $admin['password'])) {
        $error = 'Password lama salah.';
    } elseif (strlen($baru) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($baru !== $konfirm) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } elseif ($baru === $lama) {
        $error = 'Password baru harus berbeda dari password lama.';
    } else {
        $hash = password_hash($baru, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE tb_admin SET password = ? WHERE id_admin = ?");
        $upd->bind_param('si', $hash, $id_admin);
        $upd->execute();
        $sukses = 'Password berhasil diganti.';
    }
}

$judul_halaman = 'Ganti Password';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <h3 class="mb-3">Ganti Password</h3>

      <?php if ($sukses): ?><div class="alert alert-success"><?= e($sukses) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Password Lama</label>
              <input type="password" name="password_lama" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password_baru" class="form-control" minlength="8" autocomplete="new-password" required>
              <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_konfirmasi" class="form-control" minlength="8" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-key"></i> Simpan Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
