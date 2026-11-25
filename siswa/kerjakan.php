<?php
require_once '../includes/auth.php';
cek_login_siswa();
require_once '../config/database.php';
require_once '../includes/gambar_helper.php';

$id_siswa = $_SESSION['siswa_id'];
$id_ujian = (int)($_GET['id_ujian'] ?? 0);

$stmtSiswa = $conn->prepare("SELECT kelas FROM tb_siswa WHERE id_siswa = ?");
$stmtSiswa->bind_param('i', $id_siswa);
$stmtSiswa->execute();
$kelas_siswa = $stmtSiswa->get_result()->fetch_assoc()['kelas'];

$stmt = $conn->prepare("SELECT * FROM tb_ujian WHERE id_ujian = ? AND status = 'aktif' AND (kelas_target = 'semua' OR kelas_target = ? OR kelas_target = SUBSTRING_INDEX(?, ' ', 1))");
$stmt->bind_param('iss', $id_ujian, $kelas_siswa, $kelas_siswa);
$stmt->execute();
$ujian = $stmt->get_result()->fetch_assoc();

if (!$ujian) {
    header('Location: dashboard.php');
    exit;
}

// Cegah mengerjakan ulang jika sudah pernah mengerjakan
$cek = $conn->prepare("SELECT id_hasil FROM tb_hasil WHERE id_siswa = ? AND id_ujian = ?");
$cek->bind_param('ii', $id_siswa, $id_ujian);
$cek->execute();
if ($cek->get_result()->num_rows > 0) {
    header('Location: nilai.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tb_soal WHERE id_ujian = ? ORDER BY id_soal ASC");
$stmt->bind_param('i', $id_ujian);
$stmt->execute();
$soal_list = $stmt->get_result();

if ($soal_list->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}

$jumlah_soal = $soal_list->num_rows;
$durasi_detik = (int)$ujian['durasi'] * 60;

$judul_halaman = 'Kerjakan: ' . $ujian['judul'];
require_once '../includes/header.php';
?>
<div class="container py-4">

  <!-- Layar mulai ujian: siswa klik "Mulai" dulu sebelum soal dan timer tampil -->
  <div id="layarMulai" class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height:75vh;">
    <div class="card shadow-sm p-4" style="max-width:480px;width:100%;">
      <i class="bi bi-shield-lock display-4 text-primary mb-3"></i>
      <h4><?= e($ujian['judul']) ?></h4>
      <p class="text-muted mb-3"><?= $jumlah_soal ?> soal &bull; <?= (int)$ujian['durasi'] ?> menit</p>
      <div class="alert alert-warning text-start small mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Jangan berpindah tab/aplikasi selama mengerjakan. Perpindahan yang terdeteksi akan <strong>dicatat dan diberi peringatan</strong>; jika melebihi batas, Anda dikeluarkan otomatis.
      </div>
      <button type="button" id="btnMulaiUjian" class="btn btn-primary btn-lg">
        <i class="bi bi-play-fill"></i> Mulai Ujian Sekarang
      </button>
    </div>
  </div>

  <!-- Konten ujian: disembunyikan sampai tombol "Mulai Ujian" ditekan -->
  <div id="kontenUjian" style="display:none;">
    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm mb-3 sticky-top" style="top:10px;z-index:10;">
      <div class="me-2" style="min-width:0;">
        <h5 class="mb-0 text-truncate"><?= e($ujian['judul']) ?></h5>
        <small class="text-muted">Soal <span id="noSoalAktif">1</span> dari <?= $jumlah_soal ?></small>
      </div>
      <div class="text-end flex-shrink-0">
        <div class="text-muted small">Sisa Waktu</div>
        <div id="timer" class="text-danger">--:--</div>
      </div>
    </div>

    <!-- Navigator nomor soal -->
    <div class="bg-white p-3 rounded shadow-sm mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3 small">
          <span><span class="legend-dot bg-primary"></span> Terjawab</span>
          <span><span class="legend-dot bg-warning"></span> Ragu-ragu</span>
          <span><span class="legend-dot border"></span> Belum dijawab</span>
        </div>
        <button type="button" id="btnKumpulkanAwal" class="btn btn-sm btn-outline-success">
          <i class="bi bi-send-check"></i> Kumpulkan Jawaban
        </button>
      </div>
      <div id="navigatorSoal" class="d-flex flex-wrap gap-2"></div>
    </div>

    <form method="post" action="proses_jawaban.php" id="formUjian">
      <input type="hidden" name="id_ujian" value="<?= $id_ujian ?>">
      <input type="hidden" name="paksa_logout" id="inputPaksaLogout" value="0">

      <div id="soalWrapper">
        <?php $no = 1; while ($s = $soal_list->fetch_assoc()): ?>
          <div class="card card-soal shadow-sm mb-3 soal-item" data-id-soal="<?= $s['id_soal'] ?>" style="<?= $no > 1 ? 'display:none;' : '' ?>">
            <div class="card-body">
              <p class="fw-semibold"><?= $no ?>. <?= nl2br(e($s['pertanyaan'])) ?></p>
              <?= tag_gambar_soal($s['gambar']) ?>
              <div class="opsi-jawaban">
                <?php foreach (['A'=>'pilihan_a','B'=>'pilihan_b','C'=>'pilihan_c','D'=>'pilihan_d'] as $kode => $field): ?>
                  <label>
                    <input type="radio" name="jawaban[<?= $s['id_soal'] ?>]" value="<?= $kode ?>">
                    <span><?= $kode ?>. <?= e($s[$field]) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <hr>
              <div class="form-check">
                <input class="form-check-input chk-ragu" type="checkbox" id="ragu<?= $s['id_soal'] ?>">
                <label class="form-check-label" for="ragu<?= $s['id_soal'] ?>">
                  <i class="bi bi-flag-fill text-warning"></i> Tandai ragu-ragu, ingin diperiksa lagi nanti
                </label>
              </div>
            </div>
          </div>
        <?php $no++; endwhile; ?>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-5 gap-2 flex-wrap">
        <button type="button" id="btnKembali" class="btn btn-outline-secondary px-4" disabled>
          <i class="bi bi-arrow-left"></i> Kembali
        </button>
        <button type="button" id="btnLanjut" class="btn btn-primary px-4">
          Lanjut <i class="bi bi-arrow-right"></i>
        </button>
        <button type="button" id="btnKumpulkan" class="btn btn-success btn-lg px-4" style="display:none;">
          <i class="bi bi-send-check"></i> Kumpulkan Jawaban
        </button>
      </div>
    </form>
  </div>

</div>

<!-- Modal Peringatan: masih ada soal ragu-ragu / belum dijawab -->
<div class="modal fade" id="modalPeringatan" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Periksa Kembali Jawaban Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="pesanRaguRagu" class="mb-2" style="display:none;">
          <strong>Soal ditandai ragu-ragu:</strong>
          <p id="daftarRaguRagu" class="mb-0 text-warning fw-semibold"></p>
        </div>
        <div id="pesanBelumJawab" style="display:none;">
          <strong>Soal belum dijawab:</strong>
          <p id="daftarBelumJawab" class="mb-0 text-danger fw-semibold"></p>
        </div>
        <p class="mt-3 mb-0 text-muted">Anda masih bisa memeriksa kembali soal-soal tersebut sebelum mengumpulkan jawaban.</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnPeriksaKembali" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-return-left"></i> Periksa Kembali
        </button>
        <button type="button" id="btnTetapKumpulkan" class="btn btn-warning">
          Tetap Kumpulkan <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Selesai: konfirmasi akhir pengumpulan jawaban -->
<div class="modal fade" id="modalSelesai" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content text-center">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100"><i class="bi bi-check-circle-fill text-success"></i> Selesai Mengerjakan?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Anda telah menjawab <strong id="ringkasTerjawab">0</strong> dari <strong><?= $jumlah_soal ?></strong> soal.</p>
        <p class="text-muted mb-0">Jawaban tidak dapat diubah lagi setelah dikumpulkan. Yakin ingin mengumpulkan jawaban sekarang?</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnSubmitFinal" class="btn btn-success px-4">
          <i class="bi bi-send-check"></i> Ya, Kumpulkan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Pelanggaran: berpindah tab / meninggalkan halaman ujian -->
<div class="modal fade" id="modalPelanggaran" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title"><i class="bi bi-exclamation-octagon-fill"></i> Peringatan!</h5>
      </div>
      <div class="modal-body">
        <p id="pesanPelanggaran" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnAksiPelanggaran" class="btn btn-danger w-100"></button>
      </div>
    </div>
  </div>
</div>


<style>
.legend-dot { display:inline-block; width:12px; height:12px; border-radius:3px; margin-right:4px; vertical-align:middle; }
.nav-soal-btn { width:40px; height:40px; padding:0; }
.nav-soal-btn.active-soal { outline:2px solid #0d6efd; outline-offset:2px; }

/* Anti-copy: teks soal tidak bisa diblok/disalin (Windows, Mac, Android, iPhone) */
body {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    -webkit-touch-callout: none;          /* iOS: matikan menu tahan-lama (Copy/Share) */
    -webkit-tap-highlight-color: transparent;
}
img { -webkit-user-drag: none; user-drag: none; }
/* Cegah salin lewat Cetak/Simpan PDF */
@media print { body { display: none !important; } }
</style>

<script>
// Semua logika dijalankan setelah seluruh halaman (termasuk Bootstrap JS
// yang dimuat di footer) selesai di-parse, supaya bootstrap.Modal tersedia.
document.addEventListener('DOMContentLoaded', function () {

// Jumlah pelanggaran maksimum sebelum siswa otomatis dikeluarkan
const BATAS_PELANGGARAN = 2;

const soalItems = Array.from(document.querySelectorAll('.soal-item'));
const totalSoal = soalItems.length;
let currentIndex = 0;
let examStarted = false;
let examFinished = false;

const navigatorEl = document.getElementById('navigatorSoal');
const navButtons = [];
soalItems.forEach((item, i) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-sm btn-outline-secondary nav-soal-btn';
    btn.textContent = i + 1;
    btn.addEventListener('click', () => showSoal(i));
    navigatorEl.appendChild(btn);
    navButtons.push(btn);
});

function getStatus(idx) {
    const item = soalItems[idx];
    const checked = item.querySelector('input[type=radio]:checked');
    const ragu = item.querySelector('.chk-ragu').checked;
    return { answered: !!checked, ragu: ragu };
}

function updateNavigatorUI() {
    navButtons.forEach((btn, i) => {
        const st = getStatus(i);
        btn.classList.remove('btn-primary', 'btn-warning', 'btn-outline-secondary', 'active-soal', 'text-white');
        if (st.ragu) {
            btn.classList.add('btn-warning');
        } else if (st.answered) {
            btn.classList.add('btn-primary', 'text-white');
        } else {
            btn.classList.add('btn-outline-secondary');
        }
        if (i === currentIndex) btn.classList.add('active-soal');
    });
}

function updateNavButtons() {
    document.getElementById('btnKembali').disabled = currentIndex === 0;
    const isLast = currentIndex === totalSoal - 1;
    document.getElementById('btnLanjut').style.display = isLast ? 'none' : '';
    document.getElementById('btnKumpulkan').style.display = isLast ? '' : 'none';
}

function showSoal(idx) {
    if (idx < 0 || idx >= totalSoal) return;
    soalItems[currentIndex].style.display = 'none';
    soalItems[idx].style.display = '';
    currentIndex = idx;
    document.getElementById('noSoalAktif').textContent = idx + 1;
    updateNavButtons();
    updateNavigatorUI();
    document.getElementById('soalWrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

soalItems.forEach((item) => {
    item.querySelectorAll('input[type=radio]').forEach(r => r.addEventListener('change', updateNavigatorUI));
    item.querySelector('.chk-ragu').addEventListener('change', updateNavigatorUI);
});

document.getElementById('btnKembali').addEventListener('click', () => showSoal(currentIndex - 1));
document.getElementById('btnLanjut').addEventListener('click', () => showSoal(currentIndex + 1));

const modalPeringatan = new bootstrap.Modal(document.getElementById('modalPeringatan'));
const modalSelesai = new bootstrap.Modal(document.getElementById('modalSelesai'));
const modalPelanggaran = new bootstrap.Modal(document.getElementById('modalPelanggaran'));
let firstFlaggedIndex = null;

function cekSebelumKumpulkan() {
    const belumJawab = [];
    const raguRagu = [];
    firstFlaggedIndex = null;
    soalItems.forEach((item, i) => {
        const st = getStatus(i);
        if (st.ragu) {
            raguRagu.push(i + 1);
            if (firstFlaggedIndex === null) firstFlaggedIndex = i;
        } else if (!st.answered) {
            belumJawab.push(i + 1);
            if (firstFlaggedIndex === null) firstFlaggedIndex = i;
        }
    });

    if (raguRagu.length > 0 || belumJawab.length > 0) {
        document.getElementById('pesanRaguRagu').style.display = raguRagu.length ? '' : 'none';
        document.getElementById('daftarRaguRagu').textContent = raguRagu.length ? 'Nomor ' + raguRagu.join(', ') : '';
        document.getElementById('pesanBelumJawab').style.display = belumJawab.length ? '' : 'none';
        document.getElementById('daftarBelumJawab').textContent = belumJawab.length ? 'Nomor ' + belumJawab.join(', ') : '';
        modalPeringatan.show();
    } else {
        tampilkanRingkasanSelesai();
    }
}

function tampilkanRingkasanSelesai() {
    const terjawab = soalItems.filter((item, i) => getStatus(i).answered).length;
    document.getElementById('ringkasTerjawab').textContent = terjawab;
    modalSelesai.show();
}

document.getElementById('btnKumpulkan').addEventListener('click', cekSebelumKumpulkan);
document.getElementById('btnKumpulkanAwal').addEventListener('click', cekSebelumKumpulkan);

document.getElementById('btnPeriksaKembali').addEventListener('click', () => {
    modalPeringatan.hide();
    if (firstFlaggedIndex !== null) showSoal(firstFlaggedIndex);
});

document.getElementById('btnTetapKumpulkan').addEventListener('click', () => {
    modalPeringatan.hide();
    tampilkanRingkasanSelesai();
});

document.getElementById('btnSubmitFinal').addEventListener('click', function() {
    selesaikanUjian();
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
    document.getElementById('formUjian').submit();
});

updateNavButtons();
updateNavigatorUI();

// =========================================================
// DETEKSI PELANGGARAN (berpindah tab / meninggalkan halaman ujian)
// =========================================================
function selesaikanUjian() {
    examFinished = true;
    keluarLayarPenuh();
}

// ---------------------------------------------------------
// MODE LAYAR PENUH: otomatis aktif saat ujian dimulai.
// (Browser mewajibkan aksi pengguna, jadi dipanggil dari klik tombol "Mulai".
//  iPhone/Safari yang tidak mendukung layar penuh dilewati tanpa error.)
// ---------------------------------------------------------
function sedangLayarPenuh() {
    return !!(document.fullscreenElement || document.webkitFullscreenElement);
}
function masukLayarPenuh() {
    if (sedangLayarPenuh()) return;
    try {
        const el = document.documentElement;
        const req = el.requestFullscreen || el.webkitRequestFullscreen;
        if (req) {
            const p = req.call(el);
            if (p && p.catch) p.catch(() => {});
        }
    } catch (e) { /* tidak didukung: lanjutkan ujian */ }
}
function keluarLayarPenuh() {
    try {
        if (!sedangLayarPenuh()) return;
        const keluar = document.exitFullscreen || document.webkitExitFullscreen;
        if (keluar) {
            const p = keluar.call(document);
            if (p && p.catch) p.catch(() => {});
        }
    } catch (e) {}
}
// Pelanggaran yang dihitung menuju batas pengeluaran otomatis
const JENIS_DIHITUNG = ['keluar_fullscreen', 'keluar_tab', 'pindah_jendela'];
let jumlahPelanggaran = 0;
let peringatanTabTertunda = false;
let menungguLayarPenuh = false;   // true selama pop up "keluar layar penuh" tampil
let mulaiKeluarFs = 0;            // waktu (ms) siswa keluar dari layar penuh

// Menyimpan aktivitas mencurigakan terakhir (dipakai sebagai keterangan log)
let aktivitasTerakhir = '', aktivitasWaktu = 0;
function ingatAktivitas(teks) { aktivitasTerakhir = teks; aktivitasWaktu = Date.now(); }
function penyebabTerakhir() {
    return (Date.now() - aktivitasWaktu < 3000) ? aktivitasTerakhir : '';
}

// Kirim catatan ke server (tidak boleh menghentikan ujian kalau gagal) lalu
// hitung pelanggaran. Mengembalikan jumlah pelanggaran terhitung saat ini.
function catatPelanggaran(jenis, keterangan, durasi) {
    try {
        const data = new URLSearchParams();
        data.append('jenis', jenis);
        data.append('id_ujian', '<?= $id_ujian ?>');
        data.append('keterangan', keterangan || '');
        if (typeof durasi === 'number') data.append('durasi', String(durasi));
        fetch('log_pelanggaran.php', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {});
    } catch (e) { /* abaikan: pop up peringatan tetap harus tampil */ }

    if (JENIS_DIHITUNG.indexOf(jenis) !== -1) jumlahPelanggaran++;
    return jumlahPelanggaran;
}

function saatLayarPenuhBerubah() {
    if (!examStarted || examFinished) return;

    // Siswa berhasil kembali ke layar penuh: tutup pop up & catat durasinya
    if (sedangLayarPenuh()) {
        if (menungguLayarPenuh) {
            menungguLayarPenuh = false;
            const durasi = mulaiKeluarFs ? Math.round((Date.now() - mulaiKeluarFs) / 1000) : 0;
            mulaiKeluarFs = 0;
            catatPelanggaran('kembali', 'Kembali ke mode layar penuh', durasi);
            modalPelanggaran.hide();
        }
        return;
    }

    // Siswa KELUAR dari layar penuh
    if (menungguLayarPenuh) return; // pop up sudah tampil, jangan dihitung dobel
    mulaiKeluarFs = Date.now();
    const jumlah = catatPelanggaran('keluar_fullscreen', 'Keluar dari mode layar penuh');

    if (jumlah > BATAS_PELANGGARAN) {
        keluarkanPaksa('Anda keluar dari mode layar penuh dan telah melebihi batas pelanggaran (' + BATAS_PELANGGARAN + 'x). Jawaban Anda dikumpulkan dan akun dikunci. Hubungi pengawas/admin untuk membukanya kembali.');
        return;
    }

    menungguLayarPenuh = true;
    const sisa = BATAS_PELANGGARAN - jumlah + 1;
    tampilkanPelanggaran(
        'Anda keluar dari mode layar penuh. Ujian harus dikerjakan dalam mode layar penuh. ' +
        'Pelanggaran ke-' + jumlah + '. Sisa kesempatan: ' + sisa + 'x lagi sebelum Anda dikeluarkan otomatis dan akun dikunci.',
        'Kembali ke Layar Penuh',
        function () {
            // Pop up baru ditutup otomatis saat layar penuh benar-benar aktif
            // (lihat bagian "Siswa berhasil kembali" di atas).
            masukLayarPenuh();
            setTimeout(function () {
                if (menungguLayarPenuh && !sedangLayarPenuh()) {
                    document.getElementById('pesanPelanggaran').textContent =
                        'Gagal masuk layar penuh. Klik tombol sekali lagi, atau tekan F11 jika browser memblokirnya.';
                }
            }, 800);
        }
    );
}
document.addEventListener('fullscreenchange', saatLayarPenuhBerubah);
document.addEventListener('webkitfullscreenchange', saatLayarPenuhBerubah);

// Menampilkan modal peringatan dengan satu tombol aksi.
function tampilkanPelanggaran(pesan, teksTombol, aksi) {
    document.getElementById('pesanPelanggaran').textContent = pesan;
    const btn = document.getElementById('btnAksiPelanggaran');
    btn.textContent = teksTombol;
    btn.disabled = false;
    btn.onclick = aksi;
    modalPelanggaran.show();
}

// Dipanggil saat siswa melebihi batas pelanggaran: kunci jawaban yang ada
// sekarang lalu logout otomatis, siswa tidak bisa melanjutkan ujian ini lagi.
function keluarkanPaksa(pesan) {
    if (examFinished) return; // sudah diproses, jangan submit dobel
    examFinished = true; // hentikan pemantauan pelanggaran lainnya
    keluarLayarPenuh();

    document.getElementById('pesanPelanggaran').textContent = pesan;
    const btn = document.getElementById('btnAksiPelanggaran');
    btn.textContent = 'Mengeluarkan Anda secara otomatis...';
    btn.disabled = true;
    btn.onclick = null;
    modalPelanggaran.show();

    document.getElementById('inputPaksaLogout').value = '1';

    // Beri sedikit jeda supaya siswa sempat membaca pesan sebelum dikeluarkan.
    setTimeout(() => {
        document.getElementById('formUjian').submit();
    }, 2500);
}

// Blokir copy/cut/seleksi teks (berlaku di Windows & HP). Paste hanya dicatat.
['copy', 'cut'].forEach(function (aksi) {
    document.addEventListener(aksi, function (e) {
        e.preventDefault();
        if (e.clipboardData) e.clipboardData.setData('text/plain', '');
        if (!examStarted || examFinished) return;
        const nama = aksi === 'copy' ? 'Mencoba menyalin teks soal (diblokir)' : 'Mencoba memotong teks (diblokir)';
        ingatAktivitas(nama);
        catatPelanggaran('salin_tempel', nama);
    });
});
document.addEventListener('paste', function () {
    if (!examStarted || examFinished) return;
    ingatAktivitas('Menempel teks (Paste)');
    catatPelanggaran('salin_tempel', 'Menempel teks (Paste)');
});

document.getElementById('btnMulaiUjian').addEventListener('click', function () {
    const layarMulai = document.getElementById('layarMulai');
    // Class Bootstrap "d-flex" memakai !important, jadi mengubah
    // style.display lewat JS saja tidak akan berpengaruh — class-nya
    // harus dilepas dulu supaya benar-benar hilang.
    layarMulai.classList.remove('d-flex');
    layarMulai.classList.add('d-none');
    document.getElementById('kontenUjian').style.display = '';
    examStarted = true;
    masukLayarPenuh();
    updateTimer();
});

// =========================================================
// TIMER
// =========================================================
let sisaDetik = <?= $durasi_detik ?>;
const timerEl = document.getElementById('timer');
const form = document.getElementById('formUjian');

function updateTimer() {
    if (sisaDetik <= 0) {
        timerEl.textContent = '00:00';
        alert('Waktu habis! Jawaban akan otomatis dikumpulkan.');
        selesaikanUjian();
        form.submit();
        return;
    }
    const menit = Math.floor(sisaDetik / 60);
    const detik = sisaDetik % 60;
    timerEl.textContent = String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');
    sisaDetik--;
    setTimeout(updateTimer, 1000);
}
// Timer baru mulai berjalan setelah tombol "Mulai Ujian Sekarang" ditekan.

}); // DOMContentLoaded
</script>
<?php require_once '../includes/footer.php'; ?>
