<?php
// =========================================================
// EKSPOR REKAP NILAI: PDF
// Murni PHP, tanpa library tambahan. Memakai font bawaan PDF (Helvetica).
// =========================================================

// ---------- Data satu baris ----------
function rekap_baris($r, $kkm) {
    if ($r['nilai'] === null) {
        return ['nilai' => null, 'benar_soal' => '-', 'status' => 'Belum mengerjakan', 'tgl' => '-'];
    }
    $n = (float)$r['nilai'];
    return [
        'nilai'      => $n,
        'benar_soal' => (int)$r['jumlah_benar'] . ' / ' . (int)$r['jumlah_soal'],
        'status'     => $n >= $kkm ? 'Tuntas' : 'Belum tuntas',
        'tgl'        => date('d/m/Y H:i', strtotime($r['tanggal'])),
    ];
}

// =========================================================
// PDF (A4 landscape)
// =========================================================
class RekapPdf {
    const W = 842;
    const H = 595;
    const M = 36;
    const WH = [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584];
    const WB = [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584];

    public $pages = [];
    public $cur = null;   // indeks halaman aktif
    public $y = 0;

    function halamanBaru() {
        $this->pages[] = '';
        $this->cur = count($this->pages) - 1;
        $this->y = self::H - self::M;
    }
    function enc($s) {
        $s = (string)$s;
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
        }
        return iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
    }
    function lebar($b, $size, $bold = false) {
        $t = $bold ? self::WB : self::WH;
        $w = 0;
        $n = strlen($b);
        for ($i = 0; $i < $n; $i++) {
            $c = ord($b[$i]);
            $w += ($c >= 32 && $c <= 126) ? $t[$c - 32] : 556;
        }
        return $w * $size / 1000;
    }
    function muat($s, $size, $bold, $maks) {
        $b = $this->enc($s);
        if ($this->lebar($b, $size, $bold) <= $maks) { return $b; }
        while (strlen($b) > 0 && $this->lebar($b . '...', $size, $bold) > $maks) {
            $b = substr($b, 0, -1);
        }
        return $b . '...';
    }
    function esc($b) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $b);
    }
    function f($n) {
        return number_format($n, 2, '.', '');
    }
    function warna($c, $op) {
        return $this->f($c[0] / 255) . ' ' . $this->f($c[1] / 255) . ' ' . $this->f($c[2] / 255) . ' ' . $op;
    }
    function tulis($s) {
        $this->pages[$this->cur] .= $s;
    }
    function kotak($x, $y, $w, $h, $isi = null, $garis = null) {
        if ($isi !== null) {
            $this->tulis($this->warna($isi, 'rg') . ' ' . $this->f($x) . ' ' . $this->f($y) . ' ' . $this->f($w) . ' ' . $this->f($h) . " re f\n");
        }
        if ($garis !== null) {
            $this->tulis($this->warna($garis, 'RG') . ' 0.5 w ' . $this->f($x) . ' ' . $this->f($y) . ' ' . $this->f($w) . ' ' . $this->f($h) . " re S\n");
        }
    }
    // $b = string sudah ter-encode (windows-1252)
    function teksMentah($x, $y, $b, $size, $bold = false, $warna = [0, 0, 0]) {
        $this->tulis('BT ' . $this->warna($warna, 'rg') . ' /' . ($bold ? 'F2' : 'F1') . ' ' . $this->f($size) . ' Tf '
            . $this->f($x) . ' ' . $this->f($y) . ' Td (' . $this->esc($b) . ") Tj ET\n");
    }
    function teks($x, $y, $s, $size, $bold = false, $warna = [0, 0, 0]) {
        $this->teksMentah($x, $y, $this->enc($s), $size, $bold, $warna);
    }
    function sel($x, $ytop, $w, $h, $s, $size = 9, $bold = false, $rata = 'l', $isi = null) {
        $this->kotak($x, $ytop - $h, $w, $h, $isi, [191, 191, 191]);
        $b = $this->muat($s, $size, $bold, $w - 8);
        $tw = $this->lebar($b, $size, $bold);
        if ($rata === 'l') { $tx = $x + 4; }
        elseif ($rata === 'c') { $tx = $x + ($w - $tw) / 2; }
        else { $tx = $x + $w - 4 - $tw; }
        $ty = $ytop - $h + ($h - $size) / 2 + $size * 0.2;
        $this->teksMentah($tx, $ty, $b, $size, $bold);
    }
    function bangun() {
        $n = count($this->pages);
        for ($i = 0; $i < $n; $i++) {
            $this->cur = $i;
            $this->teks(self::M, 18, 'Halaman ' . ($i + 1) . ' dari ' . $n, 8, false, [110, 110, 110]);
        }
        $objs = [];
        $objs[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        for ($i = 0; $i < $n; $i++) { $kids[] = (5 + 2 * $i) . ' 0 R'; }
        $objs[] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        $objs[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        for ($i = 0; $i < $n; $i++) {
            $cid = 6 + 2 * $i;
            $objs[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::W . ' ' . self::H . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $cid . ' 0 R >>';
            $data = $this->pages[$i];
            $objs[] = '<< /Length ' . strlen($data) . " >>\nstream\n" . $data . "\nendstream";
        }
        $out = "%PDF-1.4\n";
        $offs = [];
        foreach ($objs as $i => $o) {
            $offs[] = strlen($out);
            $out .= ($i + 1) . " 0 obj\n" . $o . "\nendobj\n";
        }
        $xr = strlen($out);
        $out .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        foreach ($offs as $o) { $out .= sprintf('%010d', $o) . " 00000 n \n"; }
        $out .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xr . "\n%%EOF";
        return $out;
    }
}

function rekap_pdf($kelompok, $ringkasan, $judul, $sub, $kkm, $nama_file) {
    $kolom = [28, 170, 170, 100, 62, 45, 100, 95];   // total 770 pt
    $nama_kolom = ['No', 'Nama', 'Ujian', 'Mata Pelajaran', 'Benar / Soal', 'Nilai', 'Status', 'Tanggal'];
    $rata = ['c', 'l', 'l', 'l', 'c', 'c', 'c', 'c'];
    $hijau = [11, 110, 58];
    $RH = 16;

    $p = new RekapPdf();
    $p->halamanBaru();
    $p->teks(RekapPdf::M, $p->y - 14, $judul, 15, true, $hijau);
    $p->y -= 22;
    $p->teks(RekapPdf::M, $p->y - 9, $sub, 8.5, false, [90, 90, 90]);
    $p->y -= 22;

    $gambarHeader = function () use ($p, $kolom, $nama_kolom) {
        $x = RekapPdf::M;
        foreach ($kolom as $i => $w) {
            $p->sel($x, $p->y, $w, 18, $nama_kolom[$i], 9, true, 'c', [225, 240, 230]);
            $x += $w;
        }
        $p->y -= 18;
    };

    if (!$kelompok) {
        $p->teks(RekapPdf::M, $p->y - 12, 'Belum ada data nilai untuk filter ini.', 10, false, [90, 90, 90]);
    }

    foreach ($kelompok as $nama_kelas => $baris) {
        if ($p->y - (22 + 16 + 18 + $RH) < 40) { $p->halamanBaru(); }
        $p->kotak(RekapPdf::M, $p->y - 20, array_sum($kolom), 20, $hijau);
        $p->teks(RekapPdf::M + 6, $p->y - 14, $nama_kelas, 11, true, [255, 255, 255]);
        $p->y -= 20;
        $p->teks(RekapPdf::M + 2, $p->y - 12, $ringkasan[$nama_kelas] ?? '', 8.5, false, [70, 70, 70]);
        $p->y -= 16;
        $gambarHeader();

        foreach ($baris as $i => $b) {
            if ($p->y - $RH < 40) {
                $p->halamanBaru();
                $p->teks(RekapPdf::M, $p->y - 10, $nama_kelas . ' (lanjutan)', 9, true, $hijau);
                $p->y -= 16;
                $gambarHeader();
            }
            $d = rekap_baris($b, $kkm);
            if ($d['nilai'] === null) {
                $nilai_teks = '-';
                $isi = [null, null, null, null, null, [231, 230, 230], [231, 230, 230], [231, 230, 230]];
                $tebal_nilai = false;
            } else {
                $nilai_teks = number_format($d['nilai'], 1, '.', '');
                $warna_n = $d['nilai'] >= $kkm ? [198, 224, 207] : ($d['nilai'] >= 50 ? [255, 242, 204] : [248, 215, 218]);
                $isi = [null, null, null, null, null, $warna_n, null, null];
                $tebal_nilai = true;
            }
            $nilai_sel = [
                (string)($i + 1), $b['nama'], $b['judul'], $b['mata_pelajaran'] ?? '',
                $d['benar_soal'], $nilai_teks, $d['status'], $d['tgl'],
            ];
            $x = RekapPdf::M;
            foreach ($kolom as $j => $w) {
                $p->sel($x, $p->y, $w, $RH, $nilai_sel[$j], 9, ($j === 5 && $tebal_nilai), $rata[$j], $isi[$j]);
                $x += $w;
            }
            $p->y -= $RH;
        }
        $p->y -= 12;
    }

    $pdf = $p->bangun();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nama_file . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: no-store');
    echo $pdf;
    return true;
}
