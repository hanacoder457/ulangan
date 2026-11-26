# Website Ulangan Online (PHP + MySQL)

Sistem ulangan/ujian online sederhana dengan 2 peran pengguna:
- **Admin**: membuat paket ujian, menambahkan bank soal (pilihan ganda A–D), dan melihat nilai semua siswa.
- **Siswa**: daftar/login, memilih ujian yang tersedia, mengerjakan soal dengan timer, dan langsung melihat nilai setelah selesai

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

