<?php
const GAMBAR_DIR      = __DIR__ . '/../uploads/soal/';
const GAMBAR_MAX_BYTE = 2 * 1024 * 1024; // 2 MB
const GAMBAR_TIPE     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];


function simpan_gambar_soal($field = 'gambar', $index = null) {
    if (empty($_FILES[$field])) {
        return [null, null];
    }
    $f = $_FILES[$field];
    if ($index !== null) { 
        if (!isset($f['error'][$index])) return [null, null];
        $f = ['error' => $f['error'][$index], 'size' => $f['size'][$index], 'tmp_name' => $f['tmp_name'][$index]];
    }
    if ($f['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload gambar gagal (kode ' . (int)$f['error'] . '). Pastikan ukuran file tidak terlalu besar.'];
    }
    if ($f['size'] > GAMBAR_MAX_BYTE) {
        return [null, 'Ukuran gambar maksimal 2 MB.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(GAMBAR_TIPE[$mime]) || @getimagesize($f['tmp_name']) === false) {
        return [null, 'File harus berupa gambar JPG, PNG, GIF, atau WEBP.'];
    }
    if (!is_dir(GAMBAR_DIR) && !mkdir(GAMBAR_DIR, 0755, true)) {
        return [null, 'Folder uploads/soal tidak bisa dibuat. Periksa izin folder.'];
    }
    $nama = bin2hex(random_bytes(12)) . '.' . GAMBAR_TIPE[$mime];
    if (!move_uploaded_file($f['tmp_name'], GAMBAR_DIR . $nama)) {
        return [null, 'Gagal menyimpan gambar. Periksa izin folder uploads/soal.'];
    }
    return [$nama, null];
}

function hapus_file_gambar($nama) {
    if ($nama && preg_match('/^[a-f0-9]{24}\.(jpg|png|gif|webp)$/', $nama)) {
        @unlink(GAMBAR_DIR . $nama);
    }
}

// Tag <img> untuk soal (kosong jika tidak ada gambar). $prefix = path relatif ke folder uploads/soal/
function tag_gambar_soal($nama, $prefix = '../uploads/soal/') {
    if (!$nama) return '';
    return '<img src="' . e($prefix . $nama) . '" alt="Gambar soal" class="img-fluid rounded border mb-3 gambar-soal" draggable="false">';
}
