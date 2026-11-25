<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/kelas_helper.php';
require_once '../includes/rekap_export.php';

// Batas nilai 
const KKM = 75;

// ---------- Filter ----------
$id_ujian = (int)($_GET['id_ujian'] ?? 0);
$sudah = !empty($_GET['sudah']);  
$kelas_pilih = $_GET['kelas'] ?? 'semua';
if (!in_array($kelas_pilih, array_merge(['semua'], TINGKAT_LIST), true)) { $kelas_pilih = 'semua'; }
$jurusan_pilih = $_GET['jurusan'] ?? 'semua';
if (!in_array($jurusan_pilih, array_merge(['semua'], JURUSAN_LIST), true)) { $jurusan_pilih = 'semua'; }

$kond = [];
$tipe = '';
$param = [];
if ($kelas_pilih !== 'semua') {
    $kond[] = "(s.kelas = ? OR s.kelas LIKE ?)";
    $tipe .= 'ss';
    $param[] = $kelas_pilih;  
    $param[] = $kelas_pilih . ' %';
}
if ($jurusan_pilih !== 'semua') {
    $kond[] = "s.kelas LIKE ?";
    $tipe .= 's';
    $param[] = '% ' . $jurusan_pilih;
}

if ($id_ujian > 0) {
  
    $sql = "SELECT s.id_siswa, s.nama, s.kelas, h.id_hasil, h.jumlah_benar, h.jumlah_soal, h.nilai, h.tanggal, u.judul, u.mata_pelajaran
            FROM tb_ujian u
            JOIN tb_siswa s ON (u.kelas_target = 'semua' OR u.kelas_target = s.kelas OR u.kelas_target = SUBSTRING_INDEX(s.kelas, ' ', 1))
            LEFT JOIN tb_hasil h ON h.id_siswa = s.id_siswa AND h.id_ujian = u.id_ujian
            WHERE u.id_ujian = ?";
    $tipe = 'i' . $tipe;
    array_unshift($param, $id_ujian);
} else {

    $sql = "SELECT s.id_siswa, s.nama, s.kelas, h.id_hasil, h.jumlah_benar, h.jumlah_soal, h.nilai, h.tanggal, u.judul, u.mata_pelajaran
            FROM tb_hasil h
            JOIN tb_siswa s ON s.id_siswa = h.id_siswa
            JOIN tb_ujian u ON u.id_ujian = h.id_ujian
            WHERE 1=1";
}
foreach ($kond as $k) { $sql .= " AND " . $k; }
if ($sudah && $id_ujian > 0) { $sql .= " AND h.id_hasil IS NOT NULL"; }
$sql .= " ORDER BY s.nama ASC, u.judul ASC";

$stmt = $conn->prepare($sql);
if ($param) { $stmt->bind_param($tipe, ...$param); }
$stmt->execute();
$hasil = $stmt->get_result();

$per_kelas = [];
foreach (daftar_kelas() as $k) { $per_kelas[$k] = []; }
$lainnya = [];
while ($row = $hasil->fetch_assoc()) {
    if (isset($per_kelas[$row['kelas']])) { $per_kelas[$row['kelas']][] = $row; }
    else { $lainnya[] = $row; }
}
$kelompok = [];
foreach ($per_kelas as $k => $rows) {
    if ($rows) { $kelompok['Kelas ' . $k] = $rows; }
}
if ($lainnya) { $kelompok['Kelas lama / jurusan belum dipilih'] = $lainnya; }

function statistik($rows) {
    $jml = 0; $total = 0; $maks = null; $min = null; $tuntas = 0; $belum = 0;
    foreach ($rows as $r) {
        if ($r['nilai'] === null) { $belum++; continue; }
        $n = (float)$r['nilai'];
        $jml++;
        $total += $n;
        if ($maks === null || $n > $maks) { $maks = $n; }
        if ($min === null || $n < $min) { $min = $n; }
        if ($n >= KKM) { $tuntas++; }
    }
    return [
        'jml' => $jml, 'belum' => $belum, 'tuntas' => $tuntas,
        'rata' => $jml ? $total / $jml : null, 'maks' => $maks, 'min' => $min,
    ];
}

$ringkasan = [];
foreach ($kelompok as $nama_kelas => $rows) {
    $st = statistik($rows);
    $teks = $st['jml'] . ' sudah mengerjakan';
    if ($st['belum'] > 0) { $teks .= ', ' . $st['belum'] . ' belum'; }
    $teks .= '  |  Rata-rata ' . ($st['rata'] === null ? '-' : number_format($st['rata'], 1))
           . '  |  Tertinggi ' . ($st['maks'] === null ? '-' : number_format($st['maks'], 1))
           . '  |  Terendah ' . ($st['min'] === null ? '-' : number_format($st['min'], 1))
           . '  |  Tuntas ' . $st['tuntas'] . ' (KKM ' . KKM . ')';
    $ringkasan[$nama_kelas] = $teks;
}
$unduh = $_GET['unduh'] ?? '';
if ($unduh === 'pdf') {
    $nama_ujian = 'Semua paket ujian';
    if ($id_ujian > 0) {
        $q = $conn->prepare("SELECT judul FROM tb_ujian WHERE id_ujian = ?");
        $q->bind_param('i', $id_ujian);
        $q->execute();
        $u = $q->get_result()->fetch_assoc();
        if ($u) { $nama_ujian = $u['judul']; }
    }
    $sub = 'Ujian: ' . $nama_ujian
         . '  |  Kelas: ' . ($kelas_pilih === 'semua' ? 'Semua' : $kelas_pilih)
         . '  |  Jurusan: ' . ($jurusan_pilih === 'semua' ? 'Semua' : $jurusan_pilih)
         . '  |  Dicetak: ' . date('d/m/Y H:i');
    $nama_file = 'rekap-nilai-' . date('Ymd-His');
    rekap_pdf($kelompok, $ringkasan, 'REKAP NILAI SISWA', $sub, KKM, $nama_file);
    exit;
}

// ---------- Data untuk dropdown ----------
// Dropdown hanya berisi ujian yang sudah dikerjakan minimal satu siswa
// (ujian yang sedang dipilih tetap ikut ditampilkan)
$daftar_ujian = $conn->query("SELECT u.id_ujian, u.judul, u.mata_pelajaran FROM tb_ujian u
    WHERE EXISTS (SELECT 1 FROM tb_hasil h WHERE h.id_ujian = u.id_ujian) OR u.id_ujian = " . (int)$id_ujian . "
    ORDER BY u.created_at DESC");

$query_dasar = [
    'id_ujian' => $id_ujian,
    'kelas' => $kelas_pilih, 'jurusan' => $jurusan_pilih, 'sudah' => $sudah ? 1 : 0,
];
$query_pdf  = http_build_query($query_dasar + ['unduh' => 'pdf']);

$judul_halaman = 'Rekap Nilai';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<style>
.tabel-rekap { table-layout: fixed; width: 100%; min-width: 860px; }
.tabel-rekap th, .tabel-rekap td { overflow-wrap: anywhere; }
</style>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Rekap Nilai per Kelas</h3>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <?php include '_jam.php'; ?>
      <a href="rekap.php?<?= e($query_pdf) ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Unduh PDF</a>
    </div>
  </div>

  <form method="get" class="card card-body py-2 px-3 mb-3 border-0 shadow-sm">
    <div class="row g-2 align-items-center">
      <div class="col-12 col-md-5">
        <select name="id_ujian" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Paket ujian">
          <option value="0">Semua paket ujian</option>
          <?php while ($u = $daftar_ujian->fetch_assoc()): ?>
            <option value="<?= $u['id_ujian'] ?>" <?= $id_ujian === (int)$u['id_ujian'] ? 'selected' : '' ?>><?= e($u['judul']) ?><?= $u['mata_pelajaran'] ? ' (' . e($u['mata_pelajaran']) . ')' : '' ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Kelas">
          <option value="semua">Semua kelas</option>
          <?php foreach (TINGKAT_LIST as $t): ?>
            <option value="<?= $t ?>" <?= $kelas_pilih === $t ? 'selected' : '' ?>>Kelas <?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="jurusan" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Jurusan">
          <option value="semua">Semua jurusan</option>
          <?php foreach (JURUSAN_LIST as $j): ?>
            <option value="<?= e($j) ?>" <?= $jurusan_pilih === $j ? 'selected' : '' ?>><?= e($j) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3 text-md-end">
        <div class="form-check form-check-inline m-0 me-2">
          <input class="form-check-input" type="checkbox" name="sudah" value="1" id="cbSudah" onchange="this.form.submit()"
                 <?= $sudah ? 'checked' : '' ?> <?= $id_ujian > 0 ? '' : 'disabled' ?>>
        </div>
        <?php if ($id_ujian > 0 || $kelas_pilih !== 'semua' || $jurusan_pilih !== 'semua' || $sudah): ?>
          <a href="rekap.php" class="small">Atur ulang</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <p class="text-muted small mb-3">
    Nilai dianggap tuntas jika &ge; <?= KKM ?>.
    <?= $id_ujian > 0 ? 'Siswa yang belum mengerjakan ujian ini ikut ditampilkan.' : 'Pilih satu paket ujian untuk melihat juga siswa yang belum mengerjakan.' ?>
  </p>

  <?php if (!$kelompok): ?>
    <div class="card shadow-sm"><div class="card-body text-center text-muted py-4">Belum ada data nilai untuk filter ini.</div></div>
  <?php endif; ?>

  <?php foreach ($kelompok as $nama_kelas => $rows): $st = statistik($rows); ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><?= e($nama_kelas) ?></h5>
        <div class="small text-muted">
          <?= $st['jml'] ?> sudah mengerjakan<?php if ($st['belum'] > 0): ?>, <?= $st['belum'] ?> belum<?php endif; ?>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 tabel-rekap">
        <colgroup>
          <col style="width:48px">   <!-- No -->
          <col style="width:20%">    <!-- Nama -->
          <col style="width:22%">    <!-- Ujian -->
          <col style="width:11%">    <!-- Benar / Soal -->
          <col style="width:8%">     <!-- Nilai -->
          <col style="width:13%">    <!-- Status -->
          <col style="width:12%">    <!-- Tanggal -->
          <col style="width:14%">    <!-- Jawaban -->
        </colgroup>
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Ujian</th>
            <th>Benar / Soal</th>
            <th>Nilai</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Jawaban</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): $belum = ($r['nilai'] === null); ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['nama']) ?></td>
            <td>
              <?= e($r['judul']) ?>
              <?php if ($r['mata_pelajaran']): ?><br><small class="text-muted"><?= e($r['mata_pelajaran']) ?></small><?php endif; ?>
            </td>
            <?php if ($belum): ?>
              <td>-</td>
              <td>-</td>
              <td><span class="badge bg-secondary">Belum mengerjakan</span></td>
              <td>-</td>
              <td>-</td>
            <?php else: $n = (float)$r['nilai']; ?>
              <td><?= (int)$r['jumlah_benar'] ?> / <?= (int)$r['jumlah_soal'] ?></td>
              <td><span class="badge bg-<?= $n >= 75 ? 'success' : ($n >= 50 ? 'warning' : 'danger') ?>"><?= number_format($n, 1) ?></span></td>
              <td><?= $n >= KKM ? 'Tuntas' : 'Belum tuntas' ?></td>
              <td><?= date('d M Y H:i', strtotime($r['tanggal'])) ?></td>
              <td>
                <a href="detail_jawaban.php?id_hasil=<?= (int)$r['id_hasil'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-list-check"></i> Lihat benar/salah
                </a>
              </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>