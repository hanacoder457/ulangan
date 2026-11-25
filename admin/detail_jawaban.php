<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/gambar_helper.php';

$id_hasil = (int)($_GET['id_hasil'] ?? 0);

// Data hasil + siswa + ujian
$stmt = $conn->prepare("SELECT h.*, s.nama AS nama_siswa, s.kelas, u.judul AS judul_ujian, u.mata_pelajaran
                        FROM tb_hasil h
                        JOIN tb_siswa s ON s.id_siswa = h.id_siswa
                        JOIN tb_ujian u ON u.id_ujian = h.id_ujian
                        WHERE h.id_hasil = ?");
$stmt->bind_param('i', $id_hasil);
$stmt->execute();
$hasil = $stmt->get_result()->fetch_assoc();
if (!$hasil) {
    header('Location: rekap.php');
    exit;
}

// Jawaban per soal
$stmt = $conn->prepare("SELECT j.jawaban_siswa, j.benar, so.id_soal, so.pertanyaan,
                               so.pilihan_a, so.pilihan_b, so.pilihan_c, so.pilihan_d, so.kunci_jawaban, so.gambar
                        FROM tb_jawaban j
                        JOIN tb_soal so ON so.id_soal = j.id_soal
                        WHERE j.id_hasil = ?
                        ORDER BY so.id_soal ASC");
$stmt->bind_param('i', $id_hasil);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$jml_benar = 0; $jml_salah = 0; $jml_kosong = 0;
foreach ($daftar as $d) {
    if ($d['jawaban_siswa'] === '-') { $jml_kosong++; }
    elseif ((int)$d['benar'] === 1) { $jml_benar++; }
    else { $jml_salah++; }
}

$filter = $_GET['tampil'] ?? 'semua';
if (!in_array($filter, ['semua', 'benar', 'salah'], true)) { $filter = 'semua'; }

$judul_halaman = 'Detail Jawaban';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<style>
.opsi { padding:.35rem .6rem; border-radius:.4rem; margin-bottom:.3rem; border:1px solid #e5e7eb; }
.opsi.kunci { background:#d1e7dd; border-color:#a3cfbb; }
.opsi.salah { background:#f8d7da; border-color:#f1aeb5; }
</style>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Detail Jawaban Siswa</h3>
    <div class="d-flex gap-2">
      <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-4"><div class="text-muted small">Nama</div><strong><?= e($hasil['nama_siswa']) ?></strong></div>
        <div class="col-md-2"><div class="text-muted small">Kelas</div><strong><?= e($hasil['kelas'] ?: '-') ?></strong></div>
        <div class="col-md-4"><div class="text-muted small">Ujian</div><strong><?= e($hasil['judul_ujian']) ?></strong><?= $hasil['mata_pelajaran'] ? ' <span class="text-muted">(' . e($hasil['mata_pelajaran']) . ')</span>' : '' ?></div>
        <div class="col-md-2"><div class="text-muted small">Nilai</div>
          <span class="badge bg-<?= $hasil['nilai']>=75?'success':($hasil['nilai']>=50?'warning':'danger') ?> fs-6"><?= number_format($hasil['nilai'],1) ?></span></div>
      </div>
      <hr>
      <span class="badge bg-success">Benar: <?= $jml_benar ?></span>
      <span class="badge bg-danger">Salah: <?= $jml_salah ?></span>
      <span class="badge bg-secondary">Tidak dijawab: <?= $jml_kosong ?></span>
      <span class="text-muted small ms-2">dari <?= count($daftar) ?> soal &middot; dikerjakan <?= date('d M Y H:i', strtotime($hasil['tanggal'])) ?></span>
    </div>
  </div>

  <div class="btn-group btn-group-sm mb-3" role="group">
    <?php foreach (['semua' => 'Semua soal', 'benar' => 'Yang benar', 'salah' => 'Yang salah / kosong'] as $k => $label): ?>
      <a href="detail_jawaban.php?id_hasil=<?= $id_hasil ?>&tampil=<?= $k ?>" class="btn btn-outline-primary <?= $filter === $k ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (!$daftar): ?>
    <div class="card shadow-sm"><div class="card-body text-center text-muted py-4">Detail jawaban untuk hasil ini tidak tersedia.</div></div>
  <?php endif; ?>

  <?php
  $nomor = 0;
  foreach ($daftar as $d):
      $nomor++;
      $kosong = ($d['jawaban_siswa'] === '-');
      $benar = ((int)$d['benar'] === 1);
      if ($filter === 'benar' && !$benar) continue;
      if ($filter === 'salah' && $benar) continue;
  ?>
  <div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <strong>Soal <?= $nomor ?></strong>
      <?php if ($benar): ?>
        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Benar</span>
      <?php elseif ($kosong): ?>
        <span class="badge bg-secondary">Tidak dijawab</span>
      <?php else: ?>
        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Salah</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <p class="mb-3"><?= nl2br(e($d['pertanyaan'])) ?></p>
      <?= tag_gambar_soal($d['gambar']) ?>
      <?php foreach (['A', 'B', 'C', 'D'] as $huruf):
          $teks = $d['pilihan_' . strtolower($huruf)];
          $is_kunci = ($huruf === $d['kunci_jawaban']);
          $is_jawab = ($huruf === $d['jawaban_siswa']);
          $kelas_css = $is_kunci ? 'kunci' : ($is_jawab ? 'salah' : '');
      ?>
        <div class="opsi <?= $kelas_css ?>">
          <strong><?= $huruf ?>.</strong> <?= e($teks) ?>
          <?php if ($is_kunci): ?><span class="badge bg-success float-end">Kunci jawaban</span><?php endif; ?>
          <?php if ($is_jawab): ?><span class="badge bg-<?= $is_kunci ? 'primary' : 'danger' ?> float-end me-1">Jawaban siswa</span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
