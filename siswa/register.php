<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/kelas_helper.php';

if (isset($_SESSION['siswa_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$nilai_nama = $nilai_kelas = $nilai_username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $nilai_nama = $nama;
    $nilai_kelas = $kelas;
    $nilai_username = $username;

    if ($nama === '' || $username === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!in_array($kelas, daftar_kelas(), true)) {
        $error = 'Silakan pilih kelas dan jurusan.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $cek = $conn->prepare("SELECT id_siswa FROM tb_siswa WHERE username = ?");
        $cek->bind_param('s', $username);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan, silakan pilih username lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO tb_siswa (nama, username, password, kelas) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $nama, $username, $hash, $kelas);
            $stmt->execute();
            header('Location: login.php?daftar=sukses');
            exit;
        }
    }
}

$judul_halaman = 'Daftar Siswa';
require_once '../includes/header.php';
?>
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:90vh;">
    <div class="col-md-5">
      <div class="card auth-card">
        <div class="card-body">
          <img src="../assets/img/logo.png" alt="Logo SMK As-Syafi'iyah" class="auth-logo">
          <h4 class="mb-3 text-center">Daftar Akun Siswa</h4>
          <form method="post" id="formDaftar" novalidate>
            <div class="mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" autocomplete="name" value="<?= e($nilai_nama) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Kelas dan Jurusan</label>
              <select name="kelas" class="form-select" required>
                <option value="" disabled <?= $nilai_kelas==='' ? 'selected' : '' ?>>-- Pilih Kelas dan Jurusan --</option>
                <?php foreach (TINGKAT_LIST as $t): ?>
                  <optgroup label="Kelas <?= $t ?>">
                    <?php foreach (JURUSAN_LIST as $j): $nilai = $t . ' ' . $j; ?>
                      <option value="<?= e($nilai) ?>" <?= $nilai_kelas===$nilai ? 'selected' : '' ?>><?= e($nilai) ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" value="<?= e($nilai_username) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" autocomplete="new-password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" name="konfirmasi" class="form-control" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Daftar</button>
          </form>
          <div class="text-center mt-3">
            <a href="login.php">Sudah punya akun? Login</a> &middot;
            <a href="../index.php">Beranda</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Pop up peringatan -->
<div class="modal fade" id="modalPeringatan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Perhatian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="pesanPeringatan"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('modalPeringatan');
  var modal = new bootstrap.Modal(modalEl);
  var pesanEl = document.getElementById('pesanPeringatan');
  var form = document.getElementById('formDaftar');
  var fokusKe = null;

  function tampilkan(pesan, fokus) {
    pesanEl.textContent = pesan;
    fokusKe = fokus || null;
    modal.show();
  }
  modalEl.addEventListener('hidden.bs.modal', function () {
    if (fokusKe) { fokusKe.focus(); }
  });

  // Validasi di browser: halaman tidak dimuat ulang, jadi semua isian tetap ada
  form.addEventListener('submit', function (e) {
    var f = form.elements;
    var pesan = null, fokus = null;
    if (f.nama.value.trim() === '' || f.username.value.trim() === '') {
      pesan = 'Nama lengkap dan username wajib diisi.';
      fokus = f.nama.value.trim() === '' ? f.nama : f.username;
    } else if (f.kelas.value === '') {
      pesan = 'Silakan pilih kelas dan jurusan.';
      fokus = f.kelas;
    } else if (f.password.value.length < 6) {
      pesan = 'Password minimal 6 karakter.';
      fokus = f.password;
    } else if (f.password.value !== f.konfirmasi.value) {
      pesan = 'Konfirmasi password tidak cocok.';
      fokus = f.konfirmasi;
    }
    if (pesan) {
      e.preventDefault();
      tampilkan(pesan, fokus);
    }
  });

  // Pesan error dari server (cadangan, mis. username sudah dipakai)
  <?php if ($error): ?>
  tampilkan(<?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>, null);
  <?php endif; ?>
});
</script>
<?php require_once '../includes/footer.php'; ?>
