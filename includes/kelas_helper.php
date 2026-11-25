<?php
// DAFTAR KELAS & JURUSAN
const TINGKAT_LIST = ['10', '11', '12'];
const JURUSAN_LIST = ['AKL', 'MPLB 1', 'MPLB 2', 'RPL'];

function daftar_kelas() {
    $hasil = [];
    foreach (TINGKAT_LIST as $t) {
        foreach (JURUSAN_LIST as $j) {
            $hasil[] = $t . ' ' . $j;
        }
    }
    return $hasil;
}

function tingkat_kelas($kelas) {
    $bagian = explode(' ', trim((string)$kelas), 2);
    return $bagian[0];
}

function daftar_target_ujian() {
    return array_merge(['semua'], TINGKAT_LIST, daftar_kelas());
}
