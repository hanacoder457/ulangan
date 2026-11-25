<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/kelas_helper.php';
require_once '../includes/gambar_helper.php';

$sukses = $error = '';

// Tambah ujian baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $judul = trim($_POST['judul']);
    $mata_pelajaran = trim($_POST['mata_pelajaran']);
    $deskripsi = trim($_POST['deskripsi']);
    $durasi = (int)$_POST['durasi'];
    $status = $_POST['status'];
    $kelas_target = $_POST['kelas_target'];

    if (!in_array($kelas_target, daftar_target_ujian(), true)) {
        $error = 'Target kelas tidak valid.';
    } elseif ($judul === '' || $mata_pelajaran === '' || $durasi <= 0) {
        $error = 'Judul, mata pelajaran, dan durasi wajib diisi dengan benar.';
    } else {
        $stmt = $conn->prepare("INSERT INTO tb_ujian (judul, mata_pelajaran, deskripsi, durasi, status, kelas_target) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssiss', $judul, $mata_pelajaran, $deskripsi, $durasi, $status, $kelas_target);
        $stmt->execute();
        $sukses = 'Paket ujian berhasil ditambahkan.';
    }
}

// hapus ujian
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("SELECT gambar FROM tb_soal WHERE id_ujian = ? AND gambar IS NOT NULL");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    foreach ($stmt->get_result() as $g) { hapus_file_gambar($g['gambar']); }
    $stmt = $conn->prepare("DELETE FROM tb_ujian WHERE id_ujian = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $sukses = 'Paket ujian dihapus.';
}

// Ubah status 
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE tb_ujian SET status = IF(status='aktif','nonaktif','aktif') WHERE id_ujian = $id");
}

$list = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM tb_soal s WHERE s.id_ujian = u.id_ujian) AS jml_soal FROM tb_ujian u ORDER BY u.created_at DESC");

$daftar_mapel = $conn->query("SELECT DISTINCT mata_pelajaran FROM tb_ujian WHERE mata_pelajaran IS NOT NULL AND mata_pelajaran <> '' ORDER BY mata_pelajaran");

$judul_halaman = 'Kelola Ujian';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Kelola Paket Ujian</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
      <i class="bi bi-plus-lg"></i> Tambah Paket Ujian
    </button>
  </div>

  <?php if ($sukses): ?><div class="alert alert-success"><?= e($sukses) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Judul</th>
            <th>Mata Pelajaran</th>
            <th>Target Kelas</th>
            <th>Durasi</th>
            <th>Jumlah Soal</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($list->num_rows === 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada paket ujian.</td></tr>
          <?php endif; ?>
          <?php while ($row = $list->fetch_assoc()): ?>
          <tr>
            <td>
              <strong><?= e($row['judul']) ?></strong><br>
              <small class="text-muted"><?= e($row['deskripsi']) ?></small>
            </td>
            <td><?= $row['mata_pelajaran'] ? e($row['mata_pelajaran']) : '<span class="text-muted">-</span>' ?></td>
            <td>
              <span class="badge bg-info text-dark">
                <?= $row['kelas_target']=='semua' ? 'Semua Kelas' : 'Kelas '.e($row['kelas_target']) ?>
              </span>
            </td>
            <td><?= (int)$row['durasi'] ?> menit</td>
            <td><?= (int)$row['jml_soal'] ?> soal<?php if ((int)$row['jml_soal'] === 0): ?> <span class="badge bg-warning" title="Ujian tanpa soal tidak akan tampil ke siswa">Belum ada soal</span><?php endif; ?></td>
            <td>
              <a href="?toggle=<?= $row['id_ujian'] ?>" class="badge text-decoration-none <?= $row['status']=='aktif' ? 'bg-success':'bg-secondary' ?>">
                <?= $row['status']=='aktif' ? 'Aktif' : 'Nonaktif' ?>
              </a>
            </td>
            <td class="text-end">
              <a href="soal.php?id_ujian=<?= $row['id_ujian'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-list-ul"></i> Bank Soal</a>
              <a href="?hapus=<?= $row['id_ujian'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus paket ujian ini beserta seluruh soalnya?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Paket Ujian</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Judul Ujian</label>
            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Ulangan Harian Matematika Bab 1">
          </div>
          <div class="mb-3">
            <label class="form-label">Mata Pelajaran</label>
            <input type="text" name="mata_pelajaran" class="form-control" list="listMapel" required placeholder="Contoh: Matematika">
            <datalist id="listMapel">
              <?php while ($m = $daftar_mapel->fetch_assoc()): ?>
                <option value="<?= e($m['mata_pelajaran']) ?>">
              <?php endwhile; ?>
            </datalist>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Durasi (menit)</label>
            <input type="number" name="durasi" class="form-control" value="30" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Target Kelas</label>
            <select name="kelas_target" class="form-select">
              <option value="semua">Semua Kelas</option>
              <?php foreach (TINGKAT_LIST as $t): ?>
                <optgroup label="Kelas <?= $t ?>">
                  <option value="<?= $t ?>">Kelas <?= $t ?> (semua jurusan)</option>
                  <?php foreach (JURUSAN_LIST as $j): ?>
                    <option value="<?= e($t . ' ' . $j) ?>"><?= e($t . ' ' . $j) ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Hanya siswa dari kelas yang dipilih yang akan otomatis diarahkan mengerjakan ujian setelah login.</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="nonaktif">Nonaktif (belum tampil ke siswa)</option>
              <option value="aktif">Aktif (langsung tampil ke siswa)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
