<?php
function extractTextFromPdf($filePath) {
    $data = @file_get_contents($filePath);
    if ($data === false) return '';

    $teksHasil = '';

    // Ambil semua blok stream...endstream (isi konten halaman PDF)
    if (!preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $data, $streams)) {
        return '';
    }

    foreach ($streams[1] as $stream) {
        // Coba decompress (kebanyakan PDF modern pakai FlateDecode / zlib)
        $decoded = @gzuncompress($stream);
        if ($decoded === false) {
            // Bukan stream terkompresi (mungkin gambar/font biner) -> lewati kalau bukan teks murni
            $decoded = $stream;
        }

        // Cari operator penampil teks: "[ ... ] TJ" atau "( ... ) Tj" / "( ... ) '" / "( ... ) \""
        if (preg_match_all('/\[((?:[^\[\]\\\\]|\\\\.)*)\]\s*TJ|\(((?:[^()\\\\]|\\\\.)*)\)\s*(?:Tj|\'|")/s', $decoded, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!empty($m[1])) {
                    // Bentuk array kerning: [(Hello) -250 (World)] TJ
                    preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/', $m[1], $inner);
                    $baris = '';
                    foreach ($inner[1] as $potong) {
                        $baris .= decodePdfString($potong);
                    }
                    if (trim($baris) !== '') $teksHasil .= $baris . "\n";
                } elseif (isset($m[2])) {
                    $baris = decodePdfString($m[2]);
                    if (trim($baris) !== '') $teksHasil .= $baris . "\n";
                }
            }
        }
    }

    return $teksHasil;
}

function decodePdfString($str) {
    // Escape karakter khusus PDF string
    $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($m) {
        return chr(octdec($m[1]));
    }, $str);
    $str = str_replace(['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t'], ['(', ')', '\\', "\n", "\r", "\t"], $str);
    return $str;
}
