# Rezky Maskapai

Aplikasi web pemesanan tiket pesawat sederhana (PHP native + MySQL + CSS murni). Mendukung peran **penumpang** dan **administrator**.

---

## Persyaratan

- PHP **8.0+** (disarankan sesuai XAMPP terbaru)
- MySQL / MariaDB
- Ekstensi PHP: `pdo_mysql`, `session`, `fileinfo` (untuk validasi unggahan bukti pembayaran)
- Browser modern (Chrome, Firefox, Edge, dll.)

---

## Instalasi

### 1. Letakkan folder proyek

Salin folder `rezky` ke direktori web server, misalnya:

`c:\xampp\htdocs\rezky`

### 2. Jalankan Apache & MySQL

Dari XAMPP Control Panel, start **Apache** dan **MySQL**.

### 3. Buat / impor database

**Opsi A — phpMyAdmin**

1. Buka `http://localhost/phpmyadmin`
2. Tab **Import** → pilih file `database/rezky_maskapai.sql` → **Go**

**Opsi B — baris perintah**

```bash
mysql -u root -p < database/rezky_maskapai.sql
```

*(Jika root tanpa password, hilangkan `-p` atau tekan Enter saat diminta password.)*

Skrip SQL akan membuat database `rezky_maskapai` beserta tabel dan data contoh.

**Database sudah ada dari versi lama?** Jika tabel `payments` belum punya kolom `payment_method`, jalankan sekali:

`database/migration_add_payment_method.sql` (lewat phpMyAdmin **SQL** atau `mysql -u root rezky_maskapai < database/migration_add_payment_method.sql`).

### 4. Konfigurasi koneksi database

Edit **`config/database.php`**:

| Variabel   | Keterangan |
|-----------|------------|
| `$dbHost` | Biasanya `127.0.0.1` atau `localhost` |
| `$dbName` | `rezky_maskapai` |
| `$dbUser` | User MySQL (default XAMPP: `root`) |
| `$dbPass` | **Kosong** (`''`) jika MySQL tanpa sandi; isi jika Anda memakai password |

Sesuaikan juga URL aplikasi jika folder atau port berbeda:

```php
define('BASE_URL', 'http://localhost/rezky');
define('PUBLIC_PATH', '/rezky');
```

Contoh: jika akses lewat `http://localhost:8080/rezky/`, ubah `BASE_URL` dan pastikan `PUBLIC_PATH` cocok dengan path di server.

### 5. Hak akses folder unggahan

Pastikan server dapat menulis ke folder bukti pembayaran:

- `uploads/proofs/`

Di Windows/XAMPP biasanya sudah bisa ditulis; jika gagal unggah, periksa izin folder.

### 6. Akses aplikasi

Buka di browser:

`http://localhost/rezky/`

 atau langsung halaman utama penumpang:

`http://localhost/rezky/user/index.php`

---

## Akun demo

Setelah impor SQL, akun berikut tersedia (sandi login aplikasi sama untuk semua):

| Peran    | Email              | Sandi |
|----------|--------------------|--------|
| Admin    | `admin@rezky.test` | `password123` |
| Penumpang | `user@rezky.test` | `password123` |
| Penumpang | `siti@rezky.test` | `password123` |

**Login:** satu halaman **Masuk** untuk semua akun. Setelah berhasil, admin diarahkan ke panel admin, penumpang ke beranda pemesanan.

---

# Panduan pengguna

## A. Penumpang

### Mendaftar

1. Klik **Daftar** (atau buka `auth/register.php`).
2. Isi nama, email, nomor HP (opsional), dan kata sandi (minimal 8 karakter).
3. Setelah berhasil, masuk lewat **Masuk**.

### Memesan tiket (jadwal siap)

1. Di **Beranda / Jadwal** (`user/index.php`) tampil daftar penerbangan **aktif** yang waktu berangkatnya masih ke depan (dikelola admin).
2. Pilih **Detail** pada jadwal yang diinginkan.
3. Jika sudah login sebagai penumpang, klik **Lanjut Pesan**.
4. Pilih **metode pembayaran** (Transfer Bank, Virtual Account, QRIS, atau E-Wallet).
5. Tentukan **jumlah kursi** dan isi data **nama** serta **nomor identitas** tiap penumpang.
6. Kirim formulir → sistem membuat **kode booking** dan halaman **invoice / pembayaran** beserta instruksi sesuai metode.

### Pembayaran & bukti transfer

1. Di halaman pembayaran, periksa **total tagihan** dan **metode** yang Anda pilih.
2. Ikuti **instruksi pembayaran** (nomor rekening demo, VA, QRIS, e-wallet — teks dapat Anda sesuaikan di `includes/payment_methods.php`).
3. Selesaikan pembayaran lalu unggah **bukti** (JPG, PNG, atau PDF, maksimal **2 MB**).
4. Status menunggu verifikasi admin sampai disetujui atau ditolak.

### Setelah pembayaran disetujui

- Status pesanan menjadi **confirmed** dan tiket **aktif**.
- Buka **Riwayat** atau dari detail pesanan → **Cetak e-ticket**.
- Gunakan tombol cetak browser; untuk PDF pilih **Simpan sebagai PDF** di dialog cetak (tergantung browser).

### Profil & riwayat

- **Profil:** ubah nama, nomor HP, dan kata sandi.
- **Riwayat:** daftar semua pemesanan dengan status singkat dan tautan ke detail.

---

## B. Administrator

Masuk lewat **Masuk** dengan email `admin@rezky.test` (sistem mengenali peran admin otomatis).

### Dashboard

Ringkasan: jumlah penumpang, penerbangan aktif, pesanan hari ini, antrean verifikasi, dan total nominal pembayaran yang sudah disetujui.

### Master data

- **Bandara:** tambah, ubah, hapus data bandara (kode, nama, kota, negara).
- **Maskapai:** tambah, ubah, hapus maskapai (nama, kode).
- **Penerbangan:** atur maskapai, nomor penerbangan, asal–tujuan, jadwal berangkat/tiba, harga per kursi, kapasitas kursi, status aktif/nonaktif.

### Transaksi

- Lihat semua pemesanan dan status pembayaran.
- Untuk pesanan **menunggu verifikasi**, unggah bukti sudah ada → admin dapat **Setujui** atau **Tolak** (opsional: catatan).
- **Setujui** → pembayaran diterima, booking terkonfirmasi, tiket aktif.
- **Tolak** → pembayaran ditolak, booking ditolak, tiket dibatalkan.

### Tiket

- Halaman khusus untuk melihat tiket dan mengubah status tiket (**inactive** / **active** / **cancelled**) bila diperlukan.

### Pengguna

- Daftar semua pengguna.
- **Nonaktifkan / aktifkan** akun penumpang (akun admin dan akun Anda sendiri tidak dinonaktifkan dari sini).

---

## Struktur folder (ringkas)

```
rezky/
├── admin/           # Panel admin
├── assets/          # CSS & JavaScript
├── auth/            # Login, register, logout
├── config/          # database.php
├── database/        # Skema SQL + migration_add_payment_method.sql (jika upgrade DB lama)
├── includes/        # Header, footer, helper, guard sesi
├── uploads/proofs/  # Bukti pembayaran (ditulis aplikasi)
├── user/            # Halaman penumpang
├── index.php        # Redirect berdasarkan sesi
├── flow-sistem.txt  # Alur bisnis singkat
└── README.md        # Dokumen ini
```

---

## Masalah umum

| Gejala | Yang bisa dicek |
|--------|-------------------|
| Error koneksi database | MySQL jalan, nama DB `rezky_maskapai` ada, user/password di `config/database.php` benar |
| Halaman tanpa gaya (CSS) | `PUBLIC_PATH` dan `BASE_URL` sesuai URL yang dipakai |
| Unggah bukti gagal | Folder `uploads/proofs/` dapat ditulis; ekstensi `fileinfo` aktif; ukuran & format file sesuai aturan |
| Daftar jadwal kosong | Belum ada penerbangan aktif dengan jadwal ≥ waktu sekarang; admin dapat menambah jadwal di menu Penerbangan |

---

## Lisensi & pengembangan

Proyek ini dibuat untuk pembelajaran / demo lokal. Sesuaikan keamanan (HTTPS, password DB, pembatasan upload) sebelum dipakai di produksi.
