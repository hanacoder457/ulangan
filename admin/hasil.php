<?php
require_once '../includes/auth.php';
cek_login_admin();
require_once '../config/database.php';
require_once '../includes/kelas_helper.php';

$filter_ujian = (int)($_GET['id_ujian'] ?? 0);
$filter_mapel = trim($_GET['mata_pelajaran'] ?? '');
$kelas_pilih = $_GET['kelas'] ?? 'semua';
if (!in_array($kelas_pilih, array_merge(['semua'], TINGKAT_LIST), true)) { $kelas_pilih = 'semua'; }
$jurusan_pilih = $_GET['jurusan'] ?? 'semua';
if (!in_array($jurusan_pilih, array_merge(['semua'], JURUSAN_LIST), true)) { $jurusan_pilih = 'semua'; }

$sql = "SELECT h.*, s.nama AS nama_siswa, s.kelas, u.judul AS judul_ujian, u.mata_pelajaran
        FROM tb_hasil h
        JOIN tb_siswa s ON s.id_siswa = h.id_siswa
        JOIN tb_ujian u ON u.id_ujian = h.id_ujian";
$where = [];
if ($filter_ujian > 0) {
    $where[] = "h.id_ujian = $filter_ujian";
}
if ($filter_mapel !== '') {
    $where[] = "u.mata_pelajaran = '" . $conn->real_escape_string($filter_mapel) . "'";
}
// Nilai $kelas_pilih / $jurusan_pilih sudah divalidasi dari daftar tetap, aman dipakai langsung
if ($kelas_pilih !== 'semua') {
    $kp = $conn->real_escape_string($kelas_pilih);
    $where[] = "(s.kelas = '$kp' OR s.kelas LIKE '$kp %')";
}
if ($jurusan_pilih !== 'semua') {
    $where[] = "s.kelas LIKE '% " . $conn->real_escape_string($jurusan_pilih) . "'";
}
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
// Urutan sesuai abjad
$sql .= " ORDER BY s.nama ASC, u.judul ASC";
$hasil = $conn->query($sql);

// Kelompokkan per kelas 
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

// Log aktivitas/pelanggaran, dikelompokkan per siswa + ujian
$log = [];
$q = $conn->query("SELECT id_siswa, id_ujian, jenis, keterangan, durasi_detik, waktu FROM tb_pelanggaran ORDER BY waktu ASC");
while ($r = $q->fetch_assoc()) {
    $log[$r['id_siswa'] . '-' . $r['id_ujian']][] = $r;
}

function fmt_durasi($d) {
    $d = (int)$d;
    if ($d < 60) return $d . ' detik';
    return floor($d / 60) . ' menit ' . ($d % 60) . ' detik';
}
$label_jenis = [
    'keluar_tab'        => ['Keluar/pindah tab', 'warning text-dark'],
    'keluar_fullscreen' => ['Keluar layar penuh', 'warning text-dark'],
    'pindah_jendela'    => ['Pindah jendela/aplikasi', 'warning text-dark'],
    'shortcut'          => ['Tombol terlarang', 'warning text-dark'],
    'salin_tempel'      => ['Copy/Paste', 'info text-dark'],
    'klik_kanan'        => ['Klik kanan', 'secondary'],
    'dikeluarkan_paksa' => ['Dikeluarkan otomatis & akun terkunci', 'danger'],
    'kembali'           => ['Kembali ke ujian', 'success'],
];

$daftar_ujian = $conn->query("SELECT id_ujian, judul FROM tb_ujian ORDER BY judul");
$daftar_mapel = $conn->query("SELECT DISTINCT mata_pelajaran FROM tb_ujian WHERE mata_pelajaran IS NOT NULL AND mata_pelajaran <> '' ORDER BY mata_pelajaran");

$judul_halaman = 'Hasil Siswa';
require_once '../includes/header.php';
require_once '_nav.php';
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Hasil / Nilai Siswa</h3>
    <form method="get" class="d-flex gap-2 flex-wrap">  
      <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="semua">Semua Kelas</option>
        <?php foreach (TINGKAT_LIST as $t): ?>
          <option value="<?= $t ?>" <?= $kelas_pilih===$t?'selected':'' ?>>Kelas <?= $t ?></option>
        <?php endforeach; ?>
      </select>
      <select name="jurusan" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="semua">Semua Jurusan</option>
        <?php foreach (JURUSAN_LIST as $j): ?>
          <option value="<?= e($j) ?>" <?= $jurusan_pilih===$j?'selected':'' ?>><?= e($j) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if ($filter_ujian || $filter_mapel !== '' || $kelas_pilih !== 'semua' || $jurusan_pilih !== 'semua'): ?>
        <a href="hasil.php" class="btn btn-sm btn-link">Atur ulang</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$kelompok): ?>
    <div class="card shadow-sm"><div class="card-body text-center text-muted py-4">Belum ada data hasil ujian.</div></div>
  <?php endif; ?>

  <?php foreach ($kelompok as $nama_kelas => $rows):
      $jml = count($rows); $tot = 0;
      foreach ($rows as $r) { $tot += (float)$r['nilai']; }
  ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><?= e($nama_kelas) ?></h5>
      <span class="small text-muted"><?= $jml ?> hasil &nbsp;|&nbsp; Rata-rata <strong><?= number_format($tot / $jml, 1) ?></strong></span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:56px;">No</th>
            <th>Nama Siswa</th>
            <th>Mata Pelajaran</th>
            <th>Paket Ujian</th>
            <th>Benar</th>
            <th>Nilai</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $no => $row): ?>
          <tr>
            <td><?= $no + 1 ?></td>
            <td><?= e($row['nama_siswa']) ?></td>
            <td><?= $row['mata_pelajaran'] ? e($row['mata_pelajaran']) : '<span class="text-muted">-</span>' ?></td>
            <td><?= e($row['judul_ujian']) ?></td>
            <td><?= $row['jumlah_benar'] ?> / <?= $row['jumlah_soal'] ?>
              <br><a href="detail_jawaban.php?id_hasil=<?= (int)$row['id_hasil'] ?>" class="small"><i class="bi bi-list-check"></i> Detail jawaban</a></td>
            <td><span class="badge bg-<?= $row['nilai']>=75?'success':($row['nilai']>=50?'warning':'danger') ?>"><?= number_format($row['nilai'],1) ?></span></td>
            <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
            <td style="min-width:260px">
              <?php
                $events = $log[$row['id_siswa'] . '-' . $row['id_ujian']] ?? [];
                $hitung = []; $total_luar = 0;
                foreach ($events as $ev) {
                    if ($ev['jenis'] === 'kembali') { $total_luar += (int)$ev['durasi_detik']; continue; }
                    $hitung[$ev['jenis']] = ($hitung[$ev['jenis']] ?? 0) + 1;
                }
              ?>
              <?php if (!$hitung): ?>
                <span class="badge bg-success">Tidak ada pelanggaran</span>
              <?php else: ?>
                <?php foreach ($hitung as $jenis => $n): ?>
                  <span class="badge bg-<?= $label_jenis[$jenis][1] ?>"><?= e($label_jenis[$jenis][0]) ?><?= $jenis === 'dikeluarkan_paksa' ? '' : ' ' . $n . 'x' ?></span>
                <?php endforeach; ?>
                <?php if ($total_luar > 0): ?>
                  <div class="small text-muted mt-1">Total di luar halaman ujian: <?= fmt_durasi($total_luar) ?></div>
                <?php endif; ?>
                <details class="small mt-1">
                  <summary>Lihat rincian aktivitas (<?= count($events) ?>)</summary>
                  <ul class="mb-0 ps-3 mt-1">
                    <?php foreach ($events as $ev): ?>
                      <li>
                        <strong><?= date('H:i:s', strtotime($ev['waktu'])) ?></strong> —
                        <?= e($ev['keterangan'] ?: $label_jenis[$ev['jenis']][0]) ?>
                        <?php if ($ev['jenis'] === 'kembali' && $ev['durasi_detik'] !== null): ?>
                          (pergi selama <?= fmt_durasi($ev['durasi_detik']) ?>)
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </details>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
