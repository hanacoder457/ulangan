-- =========================================================
-- DATABASE TUNGGAL APLIKASI ULANGAN ONLINE (quiz-app)
-- =========================================================
-- SATU file ini sudah mencakup SEMUA tabel dan SEMUA perubahan
-- (menggantikan migration_aktivitas.sql, migration_mata_pelajaran.sql,
--  migration_pelanggaran.sql, dan migration_status_akun.sql).
--
-- CARA PAKAI (instalasi baru ATAU database lama, caranya sama):
--   1. Buat / pilih SATU database di phpMyAdmin (nama bebas,
--      samakan dengan $dbname di config/database.php).
--   2. Klik database tersebut -> tab Import -> pilih file ini -> Go.
--
-- File ini AMAN dijalankan berulang kali: tabel yang sudah ada tidak
-- dihapus/ditimpa, dan kolom yang belum ada akan ditambahkan otomatis.
-- Tidak ada CREATE DATABASE / USE di sini, supaya cocok dengan hosting
-- yang nama databasenya diawali prefix (mis. if0_xxxx_db_ulangan).
-- =========================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------
-- 1. TABEL
-- ---------------------------------------------------------

-- Admin
CREATE TABLE IF NOT EXISTS tb_admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Siswa (status_akun dipakai fitur kunci akun otomatis saat melanggar aturan ujian)
CREATE TABLE IF NOT EXISTS tb_siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    kelas VARCHAR(50) DEFAULT NULL,
    status_akun ENUM('aktif','terkunci') NOT NULL DEFAULT 'aktif',
    alasan_terkunci TEXT DEFAULT NULL,
    terkunci_pada TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_akun (status_akun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ujian (paket ulangan)
CREATE TABLE IF NOT EXISTS tb_ujian (
    id_ujian INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    mata_pelajaran VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT,
    durasi INT NOT NULL DEFAULT 30 COMMENT 'durasi dalam menit',
    kelas_target VARCHAR(20) NOT NULL DEFAULT 'semua',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank soal
CREATE TABLE IF NOT EXISTS tb_soal (
    id_soal INT AUTO_INCREMENT PRIMARY KEY,
    id_ujian INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    pilihan_a VARCHAR(255) NOT NULL,
    pilihan_b VARCHAR(255) NOT NULL,
    pilihan_c VARCHAR(255) NOT NULL,
    pilihan_d VARCHAR(255) NOT NULL,
    kunci_jawaban ENUM('A','B','C','D') NOT NULL,
    gambar VARCHAR(100) DEFAULT NULL COMMENT 'nama file di uploads/soal/',
    FOREIGN KEY (id_ujian) REFERENCES tb_ujian(id_ujian) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hasil ujian siswa
CREATE TABLE IF NOT EXISTS tb_hasil (
    id_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    id_ujian INT NOT NULL,
    jumlah_benar INT NOT NULL DEFAULT 0,
    jumlah_soal INT NOT NULL DEFAULT 0,
    nilai DECIMAL(5,2) NOT NULL DEFAULT 0,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES tb_siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_ujian) REFERENCES tb_ujian(id_ujian) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detail jawaban siswa (untuk review)
CREATE TABLE IF NOT EXISTS tb_jawaban (
    id_jawaban INT AUTO_INCREMENT PRIMARY KEY,
    id_hasil INT NOT NULL,
    id_soal INT NOT NULL,
    jawaban_siswa ENUM('A','B','C','D','-') DEFAULT '-',
    benar TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_hasil) REFERENCES tb_hasil(id_hasil) ON DELETE CASCADE,
    FOREIGN KEY (id_soal) REFERENCES tb_soal(id_soal) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log pelanggaran ujian (keluar tab / layar penuh, dll) -> kolom "Keterangan" di admin/hasil.php
CREATE TABLE IF NOT EXISTS tb_pelanggaran (
    id_pelanggaran INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    id_ujian INT NOT NULL,
    jenis ENUM('keluar_tab','keluar_fullscreen','dikeluarkan_paksa','pindah_jendela','kembali','shortcut','salin_tempel','klik_kanan') NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    durasi_detik INT DEFAULT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_siswa_ujian (id_siswa, id_ujian),
    FOREIGN KEY (id_siswa) REFERENCES tb_siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_ujian) REFERENCES tb_ujian(id_ujian) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. PENYESUAIAN UNTUK DATABASE LAMA (otomatis, dilewati bila sudah ada)
--    Menggantikan semua file migration_*.sql
-- ---------------------------------------------------------

-- tb_ujian.mata_pelajaran
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_ujian ADD COLUMN mata_pelajaran VARCHAR(100) DEFAULT NULL AFTER judul',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_ujian' AND COLUMN_NAME = 'mata_pelajaran');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_ujian.kelas_target
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_ujian ADD COLUMN kelas_target VARCHAR(20) NOT NULL DEFAULT ''semua'' AFTER durasi',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_ujian' AND COLUMN_NAME = 'kelas_target');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_soal.gambar
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_soal ADD COLUMN gambar VARCHAR(100) DEFAULT NULL AFTER kunci_jawaban',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_soal' AND COLUMN_NAME = 'gambar');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_siswa.status_akun
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_siswa ADD COLUMN status_akun ENUM(''aktif'',''terkunci'') NOT NULL DEFAULT ''aktif'' AFTER kelas',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_siswa' AND COLUMN_NAME = 'status_akun');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_siswa.alasan_terkunci
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_siswa ADD COLUMN alasan_terkunci TEXT DEFAULT NULL AFTER status_akun',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_siswa' AND COLUMN_NAME = 'alasan_terkunci');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_siswa.terkunci_pada
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_siswa ADD COLUMN terkunci_pada TIMESTAMP NULL DEFAULT NULL AFTER alasan_terkunci',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_siswa' AND COLUMN_NAME = 'terkunci_pada');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_siswa index idx_status_akun
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_siswa ADD INDEX idx_status_akun (status_akun)',
    'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_siswa' AND INDEX_NAME = 'idx_status_akun');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_pelanggaran.keterangan
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_pelanggaran ADD COLUMN keterangan VARCHAR(255) DEFAULT NULL AFTER jenis',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_pelanggaran' AND COLUMN_NAME = 'keterangan');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_pelanggaran.durasi_detik
SET @s = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_pelanggaran ADD COLUMN durasi_detik INT DEFAULT NULL AFTER keterangan',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_pelanggaran' AND COLUMN_NAME = 'durasi_detik');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- tb_pelanggaran.jenis: pastikan daftar jenis lengkap (aman dijalankan berulang)
ALTER TABLE tb_pelanggaran
    MODIFY jenis ENUM('keluar_tab','keluar_fullscreen','dikeluarkan_paksa','pindah_jendela','kembali','shortcut','salin_tempel','klik_kanan') NOT NULL;

-- ---------------------------------------------------------
-- Akun admin default TIDAK dibuat di sini.
-- Setelah import, buka setup_admin.php lewat browser SATU kali
-- (username: admin | password: admin123), lalu HAPUS file
-- setup_admin.php (dan admin/setup_admin.php) dari server.
-- ---------------------------------------------------------
