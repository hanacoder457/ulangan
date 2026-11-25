<?php
function parseTeksSoal($teks) {
    $teks = str_replace("\r\n", "\n", $teks);
    $teks = str_replace("\r", "\n", $teks);

    $pola = '/(?:^|\n)\s*\d{1,3}[\.\)]\s*(.+?)\n'
          . '\s*[Aa][\.\)]\s*(.+?)\n'
          . '\s*[Bb][\.\)]\s*(.+?)\n'
          . '\s*[Cc][\.\)]\s*(.+?)\n'
          . '\s*[Dd][\.\)]\s*(.+?)\n'
          . '\s*(?:KUNCI(?:\s*JAWABAN)?|JAWABAN)\s*[:\-]?\s*([A-Da-d])\b/s';

    preg_match_all($pola, $teks, $hasil, PREG_SET_ORDER);

    $soal = [];
    foreach ($hasil as $h) {
        $soal[] = [
            'pertanyaan' => trim($h[1]),
            'a'          => trim($h[2]),
            'b'          => trim($h[3]),
            'c'          => trim($h[4]),
            'd'          => trim($h[5]),
            'kunci'      => strtoupper(trim($h[6])),
        ];
    }
    return $soal;
}
