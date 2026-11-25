<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/kelas_helper.php';

$sukses = $error = '';
if (!empty($_SESSION['flash_siswa'])) {
    $sukses = $_SESSION['flash_siswa'];
    unset($_SESSION['flash_siswa']);
}

// Buka kunci akun (setujui login kembali)
if (isset($_GET['buka_kunci'])) {
    $id = (int)$_GET['buka_kunci'];
    $stmt = $conn->prepare("UPDATE tb_siswa SET status_akun = 'aktif', alasan_terkunci = NULL, terkunci_pada = NULL WHERE id_siswa = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $sukses = 'Akun siswa berhasil dibuka kembali. Siswa sudah bisa login lagi.';
}


// Filter: status akun (semua/terkunci), tingkat kelas (semua/10/11/12), jurusan (semua/AKL/MPLB 1/...)
$filter = ($_GET['filter'] ?? 'semua') === 'terkunci' ? 'terkunci' : 'semua';
$kelas_pilih = $_GET['kelas'] ?? 'semua';
if (!in_array($kelas_pilih, array_merge(['semua'], TINGKAT_LIST), true)) {
    $kelas_pilih = 'semua';
}
$jurusan_pilih = $_GET['jurusan'] ?? 'semua';
if (!in_array($jurusan_pilih, array_merge(['semua'], JURUSAN_LIST), true)) {
    $jurusan_pilih = 'semua';
}

// Link yang mempertahankan filter yang sedang aktif
function url_siswa($ubah = []) {
    global $filter, $kelas_pilih, $jurusan_pilih;
    $q = array_merge(['filter' => $filter, 'kelas' => $kelas_pilih, 'jurusan' => $jurusan_pilih], $ubah);
    return 'siswa.php?' . http_build_query($q);
}

// Kunci akun manual (hanya lewat POST, dengan konfirmasi pop up dan alasan opsional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kunci_siswa'])) {
    $id = (int)$_POST['kunci_siswa'];
    $alasan = trim($_POST['alasan'] ?? '');
    $alasan = $alasan === '' ? 'Dikunci manual oleh admin.' : 'Dikunci manual oleh admin: ' . mb_substr($alasan, 0, 200);
    $cek = $conn->prepare("SELECT nama FROM tb_siswa WHERE id_siswa = ?");
    $cek->bind_param('i', $id);
    $cek->execute();
    $data = $cek->get_result()->fetch_assoc();
    if ($data) {
        $upd = $conn->prepare("UPDATE tb_siswa SET status_akun = 'terkunci', alasan_terkunci = ?, terkunci_pada = NOW() WHERE id_siswa = ?");
        $upd->bind_param('si', $alasan, $id);
        $upd->execute();
        $_SESSION['flash_siswa'] = 'Akun ' . $data['nama'] . ' berhasil dikunci.';
    } else {
        $_SESSION['flash_siswa'] = 'Akun siswa tidak ditemukan.';
    }
    header('Location: ' . url_siswa());
    exit;
}

// Hapus akun siswa (hanya lewat POST). Hasil ujian, jawaban, dan log pelanggaran siswa ikut terhapus.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_siswa'])) {
    $id = (int)$_POST['hapus_siswa'];
    $cek = $conn->prepare("SELECT nama FROM tb_siswa WHERE id_siswa = ?");
    $cek->bind_param('i', $id);
    $cek->execute();
    $data = $cek->get_result()->fetch_assoc();
    if ($data) {
        $del = $conn->prepare("DELETE FROM tb_siswa WHERE id_siswa = ?");
        $del->bind_param('i', $id);
        $del->execute();
        $_SESSION['flash_siswa'] = 'Akun ' . $data['nama'] . ' berhasil dihapus beserta seluruh hasil ujiannya.';
    } else {
        $_SESSION['flash_siswa'] = 'Akun siswa tidak ditemukan.';
    }
    header('Location: ' . url_siswa());
    exit;
}

// Ambil data siswa, urut abjad berdasarkan nama
$kondisi = [];
$tipe = '';
$param = [];
if ($filter === 'terkunci') {
    $kondisi[] = "status_akun = 'terkunci'";
}
if ($kelas_pilih !== 'semua') {
    $kondisi[] = "(kelas = ? OR kelas LIKE ?)";
    $tipe .= 'ss';
    $param[] = $kelas_pilih;
    $param[] = $kelas_pilih . ' %';
}
if ($jurusan_pilih !== 'semua') {
    $kondisi[] = "kelas LIKE ?";
    $tipe .= 's';
    $param[] = '% ' . $jurusan_pilih;
}
$sql = "SELECT * FROM tb_siswa" . ($kondisi ? " WHERE " . implode(' AND ', $kondisi) : "") . " ORDER BY nama ASC, username ASC";
$stmt = $conn->prepare($sql);
if ($param) {
    $stmt->bind_param($tipe, ...$param);
}
$stmt->execute();
$hasil = $stmt->get_result();

// Kelompokkan per kelas + jurusan (tidak digabung)
$per_kelas = [];
foreach (daftar_kelas() as $k) { $per_kelas[$k] = []; }
$lainnya = [];   // data lama (hanya "10"/"11"/"12") atau kosong
while ($row = $hasil->fetch_assoc()) {
    if (isset($per_kelas[$row['kelas']])) {
        $per_kelas[$row['kelas']][] = $row;
    } else {
        $lainnya[] = $row;
    }
}
$kelompok = [];
foreach ($per_kelas as $k => $rows) {
    if ($rows) { $kelompok['Kelas ' . $k] = $rows; }
}
if ($lainnya) { $kelompok['Kelas lama / jurusan belum dipilih'] = $lainnya; }

// Jumlah siswa per tingkat (untuk label tombol; mengikuti filter status & jurusan, bukan filter kelas)
$kond2 = [];
$tipe2 = '';
$param2 = [];
if ($filter === 'terkunci') { $kond2[] = "status_akun = 'terkunci'"; }
if ($jurusan_pilih !== 'semua') {
    $kond2[] = "kelas LIKE ?";
    $tipe2 .= 's';
    $param2[] = '% ' . $jurusan_pilih;
}
$st2 = $conn->prepare("SELECT kelas, COUNT(*) AS jml FROM tb_siswa" . ($kond2 ? " WHERE " . implode(' AND ', $kond2) : "") . " GROUP BY kelas");
if ($param2) { $st2->bind_param($tipe2, ...$param2); }
$st2->execute();
$jumlah_per_kelas = array_fill_keys(TINGKAT_LIST, 0);
$jumlah_semua = 0;
$hit = $st2->get_result();
while ($h = $hit->fetch_assoc()) {
    $jumlah_semua += (int)$h['jml'];
    $t = tingkat_kelas($h['kelas']);
    if (isset($jumlah_per_kelas[$t])) { $jumlah_per_kelas[$t] += (int)$h['jml']; }
}

$jumlah_terkunci = $conn->query("SELECT COUNT(*) AS jml FROM tb_siswa WHERE status_akun = 'terkunci'")->fetch_assoc()['jml'];

$judul_halaman = 'Kelola Akun Siswa';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<style>
/* Lebar kolom dikunci supaya semua tabel kelas sejajar */
.tabel-siswa { table-layout: fixed; width: 100%; min-width: 820px; }
.tabel-siswa th, .tabel-siswa td { overflow-wrap: anywhere; }
</style>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Kelola Akun Siswa</h3>
    <?php include '_jam.php'; ?>
  </div>

  <form method="get" class="card card-body py-2 px-3 mb-3 border-0 shadow-sm">
    <div class="row g-2 align-items-center">
      <div class="col-6 col-md-3">
        <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Kelas">
          <option value="semua">Semua kelas (<?= $jumlah_semua ?>)</option>
          <?php foreach (TINGKAT_LIST as $t): ?>
            <option value="<?= $t ?>" <?= $kelas_pilih===$t ? 'selected' : '' ?>>Kelas <?= $t ?> (<?= $jumlah_per_kelas[$t] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <select name="jurusan" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Jurusan">
          <option value="semua">Semua jurusan</option>
          <?php foreach (JURUSAN_LIST as $j): ?>
            <option value="<?= e($j) ?>" <?= $jurusan_pilih===$j ? 'selected' : '' ?>><?= e($j) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Status akun">
          <option value="semua">Semua status</option>
          <option value="terkunci" <?= $filter==='terkunci' ? 'selected' : '' ?>>Terkunci (<?= $jumlah_terkunci ?>)</option>
        </select>
      </div>
      <div class="col-6 col-md-3 text-md-end">
        <?php if ($kelas_pilih !== 'semua' || $jurusan_pilih !== 'semua' || $filter !== 'semua'): ?>
          <a href="siswa.php" class="small">Atur ulang</a>
        <?php else: ?>
          <span class="small text-muted"><?= $jumlah_semua ?> siswa</span>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <?php if ($sukses): ?><div class="alert alert-success"><?= e($sukses) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <?php if ($jumlah_terkunci > 0 && $filter !== 'terkunci'): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle-fill"></i>
      Ada <strong><?= $jumlah_terkunci ?></strong> akun siswa yang terkunci karena pelanggaran saat ujian dan menunggu persetujuan Anda untuk bisa login lagi.
      <a href="<?= e(url_siswa(['filter' => 'terkunci'])) ?>" class="alert-link">Lihat daftarnya</a>.
    </div>
  <?php endif; ?>

  <?php if (!$kelompok): ?>
    <div class="card shadow-sm"><div class="card-body text-center text-muted py-4">Tidak ada data siswa.</div></div>
  <?php endif; ?>

  <?php foreach ($kelompok as $nama_kelas => $rows): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><?= e($nama_kelas) ?></h5>
      <span class="badge bg-primary"><?= count($rows) ?> siswa</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 tabel-siswa">
        <colgroup>
          <col style="width:48px">   <!-- No -->
          <col style="width:19%">    <!-- Nama -->
          <col style="width:14%">    <!-- Username -->
          <col style="width:12%">    <!-- Status Akun -->
          <col style="width:40%">    <!-- Keterangan -->
          <col style="width:15%">    <!-- Aksi -->
        </colgroup>
        <thead class="table-light">
          <tr>
            <th style="width:56px;">No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Status Akun</th>
            <th>Keterangan</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $no => $s): $terkunci = ($s['status_akun'] ?? 'aktif') === 'terkunci'; ?>
          <tr>
            <td><?= $no + 1 ?></td>
            <td><?= e($s['nama']) ?></td>
            <td><?= e($s['username']) ?></td>
            <td>
              <?php if ($terkunci): ?>
                <span class="badge bg-danger"><i class="bi bi-lock-fill"></i> Terkunci</span>
              <?php else: ?>
                <span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Aktif</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($terkunci): ?>
                <small class="text-muted">
                  <?= e($s['alasan_terkunci'] ?: '-') ?><br>
                  <?= $s['terkunci_pada'] ? date('d/m/Y H:i', strtotime($s['terkunci_pada'])) : '' ?>
                  <?php if ($s['terkunci_pada']): ?><br><span class="text-danger" data-sejak-ms="<?= strtotime($s['terkunci_pada']) * 1000 ?>"></span><?php endif; ?>
                </small>
              <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
              <?php if ($terkunci): ?>
                <a href="<?= e(url_siswa(['buka_kunci' => $s['id_siswa']])) ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui dan buka kembali akun <?= e(addslashes($s['nama'])) ?> supaya bisa login lagi?')">
                  <i class="bi bi-unlock"></i> Buka Kunci
                </a>
              <?php else: ?>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-kunci"
                        data-id="<?= (int)$s['id_siswa'] ?>" data-nama="<?= e($s['nama']) ?>">
                  <i class="bi bi-lock"></i> Kunci
                </button>
              <?php endif; ?>
              <button type="button" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus akun" aria-label="Hapus akun <?= e($s['nama']) ?>"
                      data-id="<?= (int)$s['id_siswa'] ?>" data-nama="<?= e($s['nama']) ?>">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<!-- Konfirmasi kunci akun -->
<div class="modal fade" id="modalKunci" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="<?= e(url_siswa()) ?>" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-lock-fill"></i> Kunci akun siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Akun <strong id="namaKunci"></strong> akan dikunci dan siswa tidak bisa login sampai Anda membukanya kembali.</p>
        <label class="form-label" for="alasanKunci">Alasan  </label>
        <input type="text" name="alasan" id="alasanKunci" class="form-control" maxlength="200" placeholder="Contoh: kedapatan membuka aplikasi lain saat ujian">
      </div>
      <div class="modal-footer">
        <input type="hidden" name="kunci_siswa" id="idKunci" value="">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-lock"></i> Kunci akun</button>
      </div>
    </form>
  </div>
</div>
<!-- Konfirmasi hapus akun -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="<?= e(url_siswa()) ?>" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="bi bi-trash"></i> Hapus akun siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Akun <strong id="namaHapus"></strong> akan dihapus permanen.</p>
        <p class="mb-0 text-muted small">Semua nilai, jawaban, dan catatan pelanggaran siswa ini ikut terhapus dan tidak bisa dikembalikan.</p>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="hapus_siswa" id="idHapus" value="">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Hapus akun</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalKunci = new bootstrap.Modal(document.getElementById('modalKunci'));
  document.querySelectorAll('.btn-kunci').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('idKunci').value = btn.dataset.id;
      document.getElementById('namaKunci').textContent = btn.dataset.nama;
      document.getElementById('alasanKunci').value = '';
      modalKunci.show();
    });
  });
  var modal = new bootstrap.Modal(document.getElementById('modalHapus'));
  document.querySelectorAll('.btn-hapus').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('idHapus').value = btn.dataset.id;
      document.getElementById('namaHapus').textContent = btn.dataset.nama;
      modal.show();
    });
  });
});
</script>
<?php require_once '../includes/footer.php'; ?>
