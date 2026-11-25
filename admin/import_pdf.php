<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/pdf_helper.php';
require_once '../includes/soal_parser.php';
require_once '../includes/gambar_helper.php';

$id_ujian = (int)($_GET['id_ujian'] ?? $_POST['id_ujian'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM tb_ujian WHERE id_ujian = ?");
$stmt->bind_param('i', $id_ujian);
$stmt->execute();
$ujian = $stmt->get_result()->fetch_assoc();
if (!$ujian) { header('Location: ujian.php'); exit; }

$error = '';
$hasil_parse = [];
$tahap = 'upload'; // upload -> preview -> selesai

// TAHAP 1: proses upload PDF / tempel teks -> parsing -> tampilkan preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_upload'])) {
    $teks_mentah = '';

    if (!empty($_FILES['file_pdf']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $error = 'File harus berformat PDF.';
        } else {
            $teks_mentah = extractTextFromPdf($_FILES['file_pdf']['tmp_name']);
            if (trim($teks_mentah) === '') {
                $error = 'Gagal membaca teks dari PDF ini (kemungkinan hasil scan/gambar atau format PDF tidak didukung). Silakan gunakan opsi "Tempel Teks Manual" di bawah.';
            }
        }
    } elseif (!empty(trim($_POST['teks_manual'] ?? ''))) {
        $teks_mentah = $_POST['teks_manual'];
    } else {
        $error = 'Silakan upload file PDF atau tempel teks soal terlebih dahulu.';
    }

    if ($teks_mentah !== '' && $error === '') {
        $hasil_parse = parseTeksSoal($teks_mentah);
        if (empty($hasil_parse)) {
            $error = 'Tidak ada soal yang berhasil terdeteksi. Pastikan format teks sesuai template (nomor soal, pilihan A-D, baris KUNCI).';
        } else {
            $tahap = 'preview';
        }
    }
}

// TAHAP 2: simpan soal yang sudah dikonfirmasi/diedit admin ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_semua'])) {
    $pertanyaan_arr = $_POST['pertanyaan'] ?? [];
    $a_arr = $_POST['a'] ?? [];
    $b_arr = $_POST['b'] ?? [];
    $c_arr = $_POST['c'] ?? [];
    $d_arr = $_POST['d'] ?? [];
    $kunci_arr = $_POST['kunci'] ?? [];
    $sertakan = $_POST['sertakan'] ?? []; // index yang dicentang untuk disimpan

    $jumlah_simpan = 0;
    $gambar_gagal = 0;
    $stmt = $conn->prepare("INSERT INTO tb_soal (id_ujian, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban, gambar) VALUES (?,?,?,?,?,?,?,?)");

    foreach ($sertakan as $idx) {
        $pertanyaan = trim($pertanyaan_arr[$idx] ?? '');
        $a = trim($a_arr[$idx] ?? '');
        $b = trim($b_arr[$idx] ?? '');
        $c = trim($c_arr[$idx] ?? '');
        $d = trim($d_arr[$idx] ?? '');
        $kunci = strtoupper(trim($kunci_arr[$idx] ?? ''));

        if ($pertanyaan === '' || $a === '' || $b === '' || $c === '' || $d === '' || !in_array($kunci, ['A','B','C','D'])) {
            continue; // lewati baris yang tidak valid
        }
        // Gambar diproses setelah soal dinyatakan valid, supaya tidak ada file yatim.
        // Jika gambarnya bermasalah, soal tetap disimpan tanpa gambar.
        [$gambar, $err_gambar] = simpan_gambar_soal('gambar', $idx);
        if ($err_gambar) { $gambar_gagal++; }

        $stmt->bind_param('isssssss', $id_ujian, $pertanyaan, $a, $b, $c, $d, $kunci, $gambar);
        $stmt->execute();
        $jumlah_simpan++;
    }

    header('Location: soal.php?id_ujian=' . $id_ujian . '&import_sukses=' . $jumlah_simpan . '&gambar_gagal=' . $gambar_gagal);
    exit;
}

$judul_halaman = 'Import Soal - ' . $ujian['judul'];
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="ujian.php">Kelola Ujian</a></li>
      <li class="breadcrumb-item"><a href="soal.php?id_ujian=<?= $id_ujian ?>"><?= e($ujian['judul']) ?></a></li>
      <li class="breadcrumb-item active">Import Soal</li>
    </ol>
  </nav>

  <h3 class="mb-3">Import Soal dari PDF / Teks</h3>

  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <?php if ($tahap === 'upload'): ?>
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_ujian" value="<?= $id_ujian ?>">

            <h5><i class="bi bi-file-earmark-pdf"></i> Opsi 1: Upload File PDF</h5>
            <p class="text-muted small">PDF harus berbasis teks (bukan hasil scan/foto) dan mengikuti format template di samping.</p>
            <div class="mb-3">
              <input type="file" name="file_pdf" accept="application/pdf" class="form-control">
            </div>

            <hr class="my-4">

            <h5><i class="bi bi-clipboard-plus"></i> Opsi 2: Tempel Teks Manual</h5>
            <p class="text-muted small">Gunakan ini jika hasil upload PDF berantakan. Salin teks soal dari PDF (Ctrl+A, Ctrl+C di pembaca PDF) lalu tempel di sini.</p>
            <div class="mb-3">
              <textarea name="teks_manual" class="form-control" rows="8" placeholder="1. Pertanyaan...&#10;A. ...&#10;B. ...&#10;C. ...&#10;D. ...&#10;KUNCI: A"></textarea>
            </div>

            <button type="submit" name="proses_upload" class="btn btn-primary">
              <i class="bi bi-arrow-right-circle"></i> Proses & Preview Soal
            </button>
            <a href="soal.php?id_ujian=<?= $id_ujian ?>" class="btn btn-outline-secondary">Batal</a>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm bg-light">
        <div class="card-body">
          <h6><i class="bi bi-info-circle"></i> Format Template Wajib</h6>
          <pre class="small bg-white p-3 rounded border">1. Ibukota Indonesia adalah?
A. Jakarta
B. Bandung
C. Surabaya
D. Medan
KUNCI: A

2. Hasil dari 5 x 6 adalah?
A. 11
B. 30
C. 56
D. 25
KUNCI: B</pre>
          <ul class="small text-muted mb-0">
            <li>Setiap soal dipisahkan baris kosong.</li>
            <li>Nomor soal diikuti titik ( <code>1.</code> ) di awal baris.</li>
            <li>4 pilihan wajib berurutan A, B, C, D.</li>
            <li>Baris terakhir wajib <code>KUNCI: [huruf]</code>.</li>
            <li>Setelah diproses, Anda tetap bisa mengedit / mencentang soal mana saja yang mau disimpan.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <?php elseif ($tahap === 'preview'): ?>

  <div class="alert alert-info">
    <i class="bi bi-check-circle"></i> <?= count($hasil_parse) ?> soal berhasil terdeteksi. Periksa dan edit jika perlu, lalu centang soal yang ingin disimpan.
  </div>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id_ujian" value="<?= $id_ujian ?>">

    <?php foreach ($hasil_parse as $i => $s): ?>
    <div class="card card-soal shadow-sm mb-3">
      <div class="card-body">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="sertakan[]" value="<?= $i ?>" id="chk<?= $i ?>" checked>
          <label class="form-check-label fw-semibold" for="chk<?= $i ?>">Simpan soal #<?= $i + 1 ?> ini</label>
        </div>
        <div class="mb-2">
          <label class="form-label small">Pertanyaan</label>
          <textarea name="pertanyaan[<?= $i ?>]" class="form-control" rows="2"><?= e($s['pertanyaan']) ?></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label small">Gambar soal <span class="text-muted">(opsional, JPG/PNG/GIF/WEBP, maks. 2 MB)</span></label>
          <input type="file" name="gambar[<?= $i ?>]" class="form-control input-gambar" accept="image/jpeg,image/png,image/gif,image/webp">
          <img class="preview-gambar img-thumbnail mt-2 d-none" alt="Pratinjau" style="max-height:160px;">
        </div>
        <div class="row g-2">
          <div class="col-md-6"><label class="form-label small">Pilihan A</label><input type="text" name="a[<?= $i ?>]" class="form-control" value="<?= e($s['a']) ?>"></div>
          <div class="col-md-6"><label class="form-label small">Pilihan B</label><input type="text" name="b[<?= $i ?>]" class="form-control" value="<?= e($s['b']) ?>"></div>
          <div class="col-md-6"><label class="form-label small">Pilihan C</label><input type="text" name="c[<?= $i ?>]" class="form-control" value="<?= e($s['c']) ?>"></div>
          <div class="col-md-6"><label class="form-label small">Pilihan D</label><input type="text" name="d[<?= $i ?>]" class="form-control" value="<?= e($s['d']) ?>"></div>
        </div>
        <div class="mt-2" style="max-width:150px;">
          <label class="form-label small">Kunci Jawaban</label>
          <select name="kunci[<?= $i ?>]" class="form-select">
            <?php foreach (['A','B','C','D'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $s['kunci']==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="text-center mb-5">
      <button type="submit" name="simpan_semua" class="btn btn-success btn-lg px-5">
        <i class="bi bi-save"></i> Simpan Soal Terpilih ke Bank Soal
      </button>
      <a href="import_pdf.php?id_ujian=<?= $id_ujian ?>" class="btn btn-outline-secondary btn-lg">Ulangi Upload</a>
    </div>
  </form>

  <?php endif; ?>
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
