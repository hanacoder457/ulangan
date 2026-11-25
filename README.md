# Website Ulangan Online (PHP + MySQL)

Sistem ulangan/ujian online sederhana dengan 2 peran pengguna:
- **Admin**: membuat paket ujian, menambahkan bank soal (pilihan ganda A–D), dan melihat nilai semua siswa.
- **Siswa**: daftar/login, memilih ujian yang tersedia, mengerjakan soal dengan timer, dan langsung melihat nilai setelah selesai.

## Struktur Folder
```
quiz-app/
├── admin/          -> halaman khusus admin (login, dashboard, kelola ujian & soal, hasil)
├── siswa/          -> halaman khusus siswa (login, daftar, kerjakan ujian, nilai)
├── config/         -> koneksi database (database.php)
├── includes/       -> header, footer, fungsi autentikasi, helper upload gambar
├── uploads/soal/   -> gambar soal yang diunggah admin (folder harus bisa ditulis, chmod 755/775)
├── database.sql    -> SATU file database (semua tabel + semua pembaruan)
├── setup_admin.php -> jalankan 1x untuk membuat akun admin, lalu HAPUS
└── index.php       -> halaman utama (pilih login admin/siswa)
```

## Cara Instalasi di Hosting (cPanel / shared hosting)

1. **Upload semua file**
   - Kompres folder `quiz-app` menjadi ZIP, lalu upload ke `public_html` (atau subfolder) melalui File Manager / FTP, lalu ekstrak.

2. **Buat database MySQL**
   - Di cPanel, buka **MySQL Databases** → buat database baru (contoh: `namauser_ulangan`).
   - Buat user MySQL baru dan **berikan semua hak akses (All Privileges)** ke database tersebut.
   - Buka **phpMyAdmin**, pilih database tadi, lalu menu **Import** → pilih file `database.sql` → klik **Go**.

3. **Atur koneksi database**
   - Buka file `config/database.php`, sesuaikan:
     ```php
     $host   = 'localhost';
     $dbname = 'namauser_ulangan';  // nama database Anda
     $user   = 'namauser_dbuser';   // username MySQL Anda
     $pass   = 'password_anda';     // password MySQL Anda
     ```

4. **Buat akun admin**
   - Buka di browser: `https://namadomainanda.com/setup_admin.php`
   - Akan muncul akun admin default: **username: `admin`**, **password: `admin123`**
   - **Setelah berhasil, segera hapus file `setup_admin.php` dari server** (lewat File Manager) demi keamanan.

5. **Selesai!**
   - Akses `https://namadomainanda.com/` untuk membuka halaman utama.
   - Login sebagai admin untuk mulai membuat paket ujian dan menambahkan soal.
   - Siswa bisa mendaftar akun sendiri lewat menu **Daftar Akun Siswa**.

## Alur Penggunaan

**Admin:**
1. Login di `/admin/login.php`
2. Menu **Kelola Ujian** → tambah paket ujian baru (judul, deskripsi, durasi, status).
3. Klik **Bank Soal** pada paket ujian → tambahkan soal pilihan ganda beserta kunci jawaban.
4. Ubah status paket ujian menjadi **Aktif** agar muncul di halaman siswa.
5. Menu **Rekap Nilai** untuk memantau nilai siswa per kelas (klik "Lihat benar/salah" untuk melihat jawaban tiap soal).

**Siswa:**
1. Daftar akun di `/siswa/register.php`, lalu login.
2. Di dashboard, pilih salah satu ujian **Aktif** yang tersedia → klik **Kerjakan Sekarang**.
3. Jawab semua soal sebelum waktu habis (ada timer otomatis).
4. Klik **Kumpulkan Jawaban** → nilai langsung tampil.
5. Riwayat nilai bisa dilihat di menu **Nilai Saya**. (Siswa hanya bisa mengerjakan tiap paket ujian satu kali.)

## Fitur Import Soal dari PDF / Teks

Di halaman **Bank Soal**, admin bisa klik **"Import dari PDF/Teks"** untuk memasukkan banyak soal sekaligus, tanpa mengetik satu per satu lewat form.

**Cara kerja:**
1. Admin upload file PDF (berbasis teks, bukan hasil scan/foto), **atau** tempel teks soal langsung ke kolom yang disediakan (berguna jika hasil baca PDF berantakan).
2. Sistem membaca teks dan mendeteksi soal berdasarkan pola/template.
3. Semua soal yang terdeteksi ditampilkan di halaman **preview** — admin bisa edit pertanyaan/pilihan/kunci jawaban, atau uncheck soal yang tidak ingin disimpan.
4. Klik **"Simpan Soal Terpilih"** untuk memasukkan ke bank soal.

**Format teks/PDF yang wajib diikuti** (setiap soal dipisah baris kosong):
```
1. Ibukota Indonesia adalah?
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
KUNCI: B
```

**Batasan yang perlu diketahui:**
- Fitur ini **tidak memakai library eksternal** (murni PHP) supaya jalan di hosting mana pun tanpa instalasi tambahan — tapi karena itu, kemampuannya terbatas dibanding tool PDF khusus.
- **Hanya bekerja untuk PDF berbasis teks** (misalnya hasil export dari Word/Google Docs). PDF hasil **scan/foto soal tidak bisa dibaca otomatis** (perlu OCR yang tidak tersedia di fitur ini).
- PDF dengan layout rumit (2 kolom, tabel, atau font tertentu) bisa menghasilkan teks yang urutannya berantakan saat diekstrak.
- Jika hasil upload PDF tidak terbaca dengan baik, gunakan opsi **"Tempel Teks Manual"**: buka PDF di pembaca PDF apa pun, pilih semua teks (Ctrl+A), salin (Ctrl+C), lalu tempel ke kolom teks di halaman import — cara ini jauh lebih andal karena urutan teks tetap terjaga.
- Gambar di dalam PDF tidak ikut terbawa otomatis. Tambahkan gambar lewat kolom **Gambar soal** di halaman preview import, atau nanti lewat tombol edit soal.
- Selalu **cek hasil preview sebelum menyimpan** — parsing otomatis tidak dijamin 100% akurat.

## Pemantauan Ujian
- Saat siswa **berpindah tab / meninggalkan halaman ujian**, sistem memberi peringatan dan mencatat kejadian (jenis + jam) ke tabel `tb_pelanggaran`. Setelah **3 kali** pelanggaran, siswa dikeluarkan otomatis, jawaban terakhirnya tersimpan, dan akunnya terkunci sampai admin membukanya di **Admin > Akun Siswa**.
- Ujian **tidak** mewajibkan mode layar penuh dan **tidak** memakai bunyi alarm.
- Selain pindah tab, sistem mencatat pindah jendela/aplikasi, tombol terlarang (Ctrl+T, Win/Cmd, F12, PrintScreen, dll), copy/paste, dan klik kanan. Rinciannya tidak ditampilkan di halaman Akun Siswa; datanya tersimpan di `tb_pelanggaran`.
- **Teks soal tidak bisa disalin** (Windows, Mac, Android, iPhone): seleksi teks, Ctrl+C/X/A, klik kanan, tekan-lama, drag teks, dan cetak/simpan PDF diblokir di `siswa/kerjakan.php`. Setiap percobaan tetap tercatat di `tb_pelanggaran`.
- **Anti-screenshot (upaya maksimal di web)**: layar otomatis tertutup hitam saat jendela tidak fokus, tab disembunyikan (termasuk tampilan app-switcher HP), atau tombol screenshot terdeteksi (PrintScreen, Win+Shift+S, Cmd+Shift+3/4/5). Ada juga **watermark nama & ID siswa** di seluruh layar, sehingga kalau soal tersebar bisa dilacak. Percobaan screenshot dicatat di `tb_pelanggaran`. **Catatan**: website tidak bisa memblokir screenshot OS secara total (mis. tombol screenshot HP, foto layar dari HP lain).
- **Batasan**: browser tidak bisa melihat situs/aplikasi apa yang dibuka siswa di luar halaman ujian, dan beberapa shortcut (mis. Alt+Tab, Ctrl+T) kadang diambil alih sistem/browser sehingga tidak terdeteksi. Yang pasti tercatat: waktu keluar, lama di luar, dan jenis perpindahannya.
- **Database**: cukup import **satu file** `database.sql` — baik untuk instalasi baru maupun database lama (kolom yang belum ada ditambahkan otomatis, aman dijalankan berulang). File `migration_*.sql` sudah tidak diperlukan.

## Update Terbaru (Perbaikan)
- **Bug penting diperbaiki**: `database.sql` sebelumnya tidak punya kolom `status_akun`, `alasan_terkunci`, `terkunci_pada` di tabel `tb_siswa`, padahal fitur kunci akun otomatis (anti-kecurangan) sudah memakainya — ini menyebabkan **halaman admin error** begitu login. Semua perbaikan itu kini sudah digabung di satu file `database.sql` — cukup import file tersebut (baru maupun lama).
- `config/database.php` sekarang menampilkan pesan error database yang jelas kalau ada kolom/tabel yang belum sesuai, alih-alih error PHP yang membingungkan.
- **Tampilan dirombak total**: font baru, warna gradient ungu-hijau, kartu dan tombol lebih modern, navigasi dengan penanda halaman aktif, dan halaman beranda/login yang lebih menarik — tanpa mengubah cara kerja sistem.

## Catatan Keamanan
- Ganti password admin default setelah login pertama kali (fitur ganti password bisa ditambahkan sesuai kebutuhan).
- Selalu gunakan HTTPS di hosting Anda.
- Jangan lupa hapus `setup_admin.php` setelah digunakan.
- Folder `config/` sudah diproteksi dengan `.htaccess` agar tidak bisa diakses langsung lewat browser (khusus server Apache).

## Kebutuhan Server
- PHP 7.4 atau lebih baru (disarankan PHP 8.x)
- MySQL 5.7+ / MariaDB
- Ekstensi PHP: `mysqli` (biasanya sudah aktif secara default di hosting)

## Gambar pada Soal
- Di **Kelola Ujian → Bank Soal**, form Tambah/Edit Soal punya kolom **Gambar soal** (opsional): JPG, PNG, GIF, atau WEBP, maks. 2 MB.
- Gambar tampil di halaman ujian siswa, di bank soal, dan di Detail Jawaban.
- Saat mengedit soal, centang **Hapus gambar ini** untuk membuang gambar, atau pilih file baru untuk menggantinya.
- File disimpan di `uploads/soal/` (nama diacak). Pastikan folder itu bisa ditulis oleh server.
- **Database lama:** import ulang `database.sql` (aman diulang) supaya kolom `tb_soal.gambar` ditambahkan.
