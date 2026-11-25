<?php
require_once '../includes/auth.php';
cek_login_siswa();
require_once '../config/database.php';

$id_siswa = $_SESSION['siswa_id'];
$selesai_id = (int)($_GET['selesai'] ?? 0);

$stmt = $conn->prepare("SELECT h.*, u.judul FROM tb_hasil h JOIN tb_ujian u ON u.id_ujian = h.id_ujian WHERE h.id_siswa = ? ORDER BY h.tanggal DESC");
$stmt->bind_param('i', $id_siswa);
$stmt->execute();
$riwayat = $stmt->get_result();

$judul_halaman = 'Nilai Saya';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <?php if ($selesai_id): ?>
    <?php
      $s = $conn->query("SELECT * FROM tb_hasil WHERE id_hasil = $selesai_id AND id_siswa = $id_siswa")->fetch_assoc();
      if ($s):
    ?>
    <div class="alert alert-success text-center py-4 mb-4 shadow-sm">
      <i class="bi bi-check-circle-fill fs-1 text-success"></i>
      <h3 class="mt-2">Ujian Selesai!</h3>
      <p class="mb-1">Jawaban benar: <?= $s['jumlah_benar'] ?> dari <?= $s['jumlah_soal'] ?> soal</p>
      <h2 class="fw-bold">Nilai: <?= number_format($s['nilai'],1) ?></h2>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <h3 class="mb-3">Riwayat Nilai</h3>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Paket Ujian</th>
            <th>Benar</th>
            <th>Nilai</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($riwayat->num_rows === 0): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Anda belum pernah mengerjakan ujian.</td></tr>
          <?php endif; ?>
          <?php while ($r = $riwayat->fetch_assoc()): ?>
          <tr>
            <td><?= e($r['judul']) ?></td>
            <td><?= $r['jumlah_benar'] ?> / <?= $r['jumlah_soal'] ?></td>
            <td><span class="badge bg-<?= $r['nilai']>=75?'success':($r['nilai']>=50?'warning':'danger') ?>"><?= number_format($r['nilai'],1) ?></span></td>
            <td><?= date('d M Y H:i', strtotime($r['tanggal'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
