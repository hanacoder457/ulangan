<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/gambar_helper.php';

$id_ujian = (int)($_GET['id_ujian'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM tb_ujian WHERE id_ujian = ?");
$stmt->bind_param('i', $id_ujian);
$stmt->execute();
$ujian = $stmt->get_result()->fetch_assoc();

if (!$ujian) {
    header('Location: ujian.php');
    exit;
}

$sukses = $error = '';

// Ambil & validasi field soal dari form (dipakai tambah & edit)
function ambil_input_soal() {
    $d = [];
    foreach (['pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d'] as $k) {
        $d[$k] = trim($_POST[$k] ?? '');
    }
    $d['kunci'] = in_array($_POST['kunci_jawaban'] ?? '', ['A', 'B', 'C', 'D'], true) ? $_POST['kunci_jawaban'] : 'A';
    return $d;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['tambah']) || isset($_POST['edit']))) {
    $d = ambil_input_soal();

    if (in_array('', [$d['pertanyaan'], $d['pilihan_a'], $d['pilihan_b'], $d['pilihan_c'], $d['pilihan_d']], true)) {
        $error = 'Semua field soal wajib diisi.';
    } else {
        [$gambar_baru, $err_gambar] = simpan_gambar_soal('gambar');
        if ($err_gambar) {
            $error = $err_gambar;
        } elseif (isset($_POST['tambah'])) {
            $stmt = $conn->prepare("INSERT INTO tb_soal (id_ujian, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban, gambar) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param('isssssss', $id_ujian, $d['pertanyaan'], $d['pilihan_a'], $d['pilihan_b'], $d['pilihan_c'], $d['pilihan_d'], $d['kunci'], $gambar_baru);
            $stmt->execute();
            $sukses = 'Soal berhasil ditambahkan.';
        } else {
            $id_soal = (int)$_POST['id_soal'];
            $stmt = $conn->prepare("SELECT gambar FROM tb_soal WHERE id_soal = ? AND id_ujian = ?");
            $stmt->bind_param('ii', $id_soal, $id_ujian);
            $stmt->execute();
            $lama = $stmt->get_result()->fetch_assoc();

            if ($lama) {
                $gambar = $lama['gambar'];
                if ($gambar_baru) {
                    hapus_file_gambar($gambar);
                    $gambar = $gambar_baru;
                } elseif (isset($_POST['hapus_gambar'])) {
                    hapus_file_gambar($gambar);
                    $gambar = null;
                }
                $stmt = $conn->prepare("UPDATE tb_soal SET pertanyaan=?, pilihan_a=?, pilihan_b=?, pilihan_c=?, pilihan_d=?, kunci_jawaban=?, gambar=? WHERE id_soal=? AND id_ujian=?");
                $stmt->bind_param('sssssssii', $d['pertanyaan'], $d['pilihan_a'], $d['pilihan_b'], $d['pilihan_c'], $d['pilihan_d'], $d['kunci'], $gambar, $id_soal, $id_ujian);
                $stmt->execute();
                $sukses = 'Soal berhasil diperbarui.';
            }
        }
    }
}

// Hapus soal (beserta file gambarnya)
if (isset($_GET['hapus'])) {
    $id_soal = (int)$_GET['hapus'];
    $stmt = $conn->prepare("SELECT gambar FROM tb_soal WHERE id_soal = ? AND id_ujian = ?");
    $stmt->bind_param('ii', $id_soal, $id_ujian);
    $stmt->execute();
    if ($lama = $stmt->get_result()->fetch_assoc()) {
        hapus_file_gambar($lama['gambar']);
        $stmt = $conn->prepare("DELETE FROM tb_soal WHERE id_soal = ? AND id_ujian = ?");
        $stmt->bind_param('ii', $id_soal, $id_ujian);
        $stmt->execute();
        $sukses = 'Soal dihapus.';
    }
}

if (isset($_GET['import_sukses'])) {
    $jml = (int)$_GET['import_sukses'];
    $sukses = $jml > 0 ? "$jml soal berhasil diimport ke bank soal." : 'Tidak ada soal yang tersimpan (cek kembali data yang dicentang).';
    $gagal = (int)($_GET['gambar_gagal'] ?? 0);
    if ($gagal > 0) {
        $error = "$gagal gambar tidak tersimpan (format bukan JPG/PNG/GIF/WEBP, lebih dari 2 MB, atau gagal diunggah). Soalnya tetap tersimpan; tambahkan gambarnya lewat tombol edit.";
    }
}

$stmt = $conn->prepare("SELECT * FROM tb_soal WHERE id_ujian = ? ORDER BY id_soal ASC");
$stmt->bind_param('i', $id_ujian);
$stmt->execute();
$soal_list = $stmt->get_result();

// Field form soal, dipakai bersama oleh modal Tambah dan modal Edit
function form_soal_fields($s = null) {
    $v = fn($k) => e($s[$k] ?? '');
    ?>
    <div class="mb-3">
      <label class="form-label">Pertanyaan</label>
      <textarea name="pertanyaan" class="form-control" rows="2" required><?= $v('pertanyaan') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Gambar soal <span class="text-muted small">(opsional, JPG/PNG/GIF/WEBP, maks. 2 MB)</span></label>
      <?php if (!empty($s['gambar'])): ?>
        <div class="mb-2">
          <img src="../uploads/soal/<?= $v('gambar') ?>" alt="Gambar soal" class="img-thumbnail" style="max-height:160px;">
          <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="hapus_gambar" id="hapusGambar<?= (int)$s['id_soal'] ?>">
            <label class="form-check-label text-danger" for="hapusGambar<?= (int)$s['id_soal'] ?>">Hapus gambar ini</label>
          </div>
        </div>
      <?php endif; ?>
      <input type="file" name="gambar" class="form-control input-gambar" accept="image/jpeg,image/png,image/gif,image/webp">
      <img class="preview-gambar img-thumbnail mt-2 d-none" alt="Pratinjau" style="max-height:160px;">
    </div>
    <div class="row g-2">
      <?php foreach (['a', 'b', 'c', 'd'] as $h): ?>
        <div class="col-md-6">
          <label class="form-label">Pilihan <?= strtoupper($h) ?></label>
          <input type="text" name="pilihan_<?= $h ?>" class="form-control" value="<?= $v('pilihan_' . $h) ?>" required>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-3 mt-2">
      <label class="form-label">Kunci Jawaban</label>
      <select name="kunci_jawaban" class="form-select">
        <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
          <option value="<?= $opt ?>" <?= ($s['kunci_jawaban'] ?? 'A') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php
}

$judul_halaman = 'Bank Soal - ' . $ujian['judul'];
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="ujian.php">Kelola Ujian</a></li>
      <li class="breadcrumb-item active"><?= e($ujian['judul']) ?></li>
    </ol>
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Bank Soal: <?= e($ujian['judul']) ?></h3>
    <div class="d-flex flex-wrap gap-2">
      <a href="import_pdf.php?id_ujian=<?= $id_ujian ?>" class="btn btn-outline-primary">
        <i class="bi bi-file-earmark-pdf"></i> Import dari PDF/Teks
      </a>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
        <i class="bi bi-plus-lg"></i> Tambah Soal
      </button>
    </div>
  </div>

  <?php if ($sukses): ?><div class="alert alert-success"><?= e($sukses) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <?php $no = 1; while ($s = $soal_list->fetch_assoc()): ?>
  <div class="card card-soal shadow-sm mb-3">
    <div class="card-body">
      <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
        <p class="fw-semibold mb-2"><?= $no++ ?>. <?= nl2br(e($s['pertanyaan'])) ?></p>
        <div class="text-nowrap">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $s['id_soal'] ?>"><i class="bi bi-pencil"></i></button>
          <a href="?id_ujian=<?= $id_ujian ?>&hapus=<?= $s['id_soal'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus soal ini?')"><i class="bi bi-trash"></i></a>
        </div>
      </div>
      <?= tag_gambar_soal($s['gambar']) ?>
      <ul class="list-unstyled mb-0">
        <?php foreach (['A', 'B', 'C', 'D'] as $h): $benar = $s['kunci_jawaban'] === $h; ?>
          <li class="<?= $benar ? 'text-success fw-bold' : '' ?>"><?= $h ?>. <?= e($s['pilihan_' . strtolower($h)]) ?> <?= $benar ? '<i class="bi bi-check-circle-fill"></i>' : '' ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Modal Edit -->
  <div class="modal fade" id="modalEdit<?= $s['id_soal'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id_soal" value="<?= $s['id_soal'] ?>">
          <div class="modal-header">
            <h5 class="modal-title">Edit Soal</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body"><?php form_soal_fields($s); ?></div>
          <div class="modal-footer">
            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endwhile; ?>

  <?php if ($no === 1): ?>
    <div class="text-center text-muted py-5">Belum ada soal pada paket ujian ini.</div>
     <div class="text-center text-muted py-6">(Tambah soal manual jika ingin menambahkan gambar)</div>
  <?php endif; ?>
</div>

<!-- Modal Tambah Soal -->
<div class="modal fade" id="modalTambahSoal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Soal Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><?php form_soal_fields(); ?></div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan Soal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Pratinjau gambar + validasi ukuran (maks. 2 MB) sebelum dikirim
document.querySelectorAll('.input-gambar').forEach(function (input) {
  input.addEventListener('change', function () {
    const prev = input.parentElement.querySelector('.preview-gambar');
    const file = input.files[0];
    if (!file) { prev.classList.add('d-none'); return; }
    if (file.size > 2 * 1024 * 1024) {
      alert('Ukuran gambar maksimal 2 MB.');
      input.value = ''; prev.classList.add('d-none'); return;
    }
    prev.src = URL.createObjectURL(file);
    prev.classList.remove('d-none');
  });
});
</script>
<?php require_once '../includes/footer.php'; ?>
