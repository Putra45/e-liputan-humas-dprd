# Sistem Pemesanan Peliputan & Kalender Publikasi (E-Liputan)
### Tim Publikasi Humas & Protokol — Sekretariat DPRD Kota Tangerang

Aplikasi web full-stack untuk manajemen jadwal liputan dan dokumentasi internal khusus Anggota Dewan dan Sekretariat DPRD Kota Tangerang. Menggantikan Google Form konvensional dengan visualisasi kalender interaktif transparan dan integrasi notifikasi otomatis WhatsApp saat pengajuan disetujui (ACC) atau disesuaikan oleh Admin Humas.

---

## 🏛️ Fitur Utama

### 1. Portal Dewan & Staf (Frontend)
- **Banner Aturan Resmi:** Notifikasi kebijakan order maksimal H-1 (order hari H diprioritaskan bagi agenda resmi dewan).
- **Kalender Publikasi Interaktif (FullCalendar):**
  - Menampilkan jadwal liputan berstatus *Approved* (Navy) dan *Pending* (Amber).
  - Format badge jadwal: `[Jam] - [Nama Pemohon] ([Fraksi])`.
  - Pop-up modal rincian kegiatan saat tanggal diklik (Nama, Fraksi, Kategori, Jam, Jenis Publikasi).
  - **Privasi Terjaga:** Nomor WhatsApp dan berkas dokumen surat pemohon disembunyikan di sisi publik/kalender.
- **Formulir Pengajuan Terpadu:**
  - Persis sesuai form resmi (Nama Kegiatan, Kategori Komisi/Pimpinan/AKD, Tanggal, Jam, Jenis Publikasi, Nama, Fraksi, WhatsApp, dan Upload Surat Tugas/Flyer maks 10MB).
  - Peringatan otomatis jika memilih tanggal Hari H.

### 2. Panel Admin (Tim Humas & Protokol)
- **Ringkasan Statistik:** Counter total permohonan, antrean pending, disetujui, ditolak, dan jadwal hari ini.
- **Tabel Manajemen & Filter Lengkap:** Filter per Status, Tanggal, Fraksi, Kategori, serta pencarian kata kunci.
- **Modal Verifikasi & Penyesuaian Jadwal (Time Adjustment):**
  - Preview & download lampiran surat/flyer.
  - Penyesuaian jam liputan final (`waktu_disetujui`) jika jadwal bentrok/padat.
  - Tambah catatan personel tim liputan yang ditugaskan.
  - Tombol **Setujui (ACC)**, **Tolak**, dan **Pending**.
- **Integrasi WhatsApp Gateway:**
  - Otomatis mengirim pesan konfirmasi ke nomor kontak pemohon saat status diubah.
  - **Fallback WhatsApp Manual (`wa.me`):** Tombol klik langsung ke WhatsApp Web dengan draft pesan resmi terisi otomatis jika API sedang offline.

---

## 🛠️ Tech Stack
- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL / SQLite (mendukung keduanya)
- **Frontend & Styling:** Blade Templates, Tailwind CSS
- **Interactive Component:** FullCalendar.js
- **API Client:** Laravel Http Client (WhatsApp Gateway: Fonnte / Wablas compatible)

---

## 🚀 Panduan Instalasi Lokal

### 1. Clone & Masuk Direktori
```bash
git clone https://github.com/Putra45/e-liputan-humas-dprd.git
cd e-liputan-humas-dprd
```

### 2. Install Dependensi PHP & Node.js
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Lingkungan (.env)
Salin `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

#### Opsi A: Menggunakan SQLite (Langsung jalan tanpa konfigurasi database server)
```env
DB_CONNECTION=sqlite
```
Pastikan file database ada:
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

#### Opsi B: Menggunakan MySQL (XAMPP)
Nyalakan Apache & MySQL di XAMPP, buat database `eliputan_humas`, lalu sesuaikan `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eliputan_humas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Akun Default
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Jalankan Aplikasi
```bash
php artisan serve
```
Akses di browser:
- **Portal Pemohon:** [http://localhost:8000](http://localhost:8000)
- **Login Admin:** [http://localhost:8000/login](http://localhost:8000/login)

---

## 🔑 Akun Login Admin Bawaan
- **Email:** `admin@dprd-tangerangkota.go.id`
- **Password:** `password123`

---

## 📲 Konfigurasi WhatsApp Gateway (Opsional)
Untuk mengaktifkan pengiriman otomatis via gateway Fonnte:
1. Dapatkan token di [fonnte.com](https://fonnte.com).
2. Tambahkan di file `.env`:
```env
WA_GATEWAY_PROVIDER=fonnte
WA_GATEWAY_TOKEN=YOUR_FONNTE_TOKEN_HERE
WA_GATEWAY_ENDPOINT=https://api.fonnte.com/send
```
*Catatan: Jika token dikosongkan, sistem tetap berjalan lancar dan menyediakan tombol **Kirim via WhatsApp Manual (wa.me)** secara otomatis.*

---

## 🧪 Menjalankan Pengujian (Automated Tests)
Semua skenario pengujian (portal, kalender event, pengajuan order, login admin, approval penyesuaian jam, service WhatsApp) telah dibuat dan lulus:
```bash
php artisan test
```

---

## 📤 Cara Push ke GitHub (Akun: Putra45)

1. Buat repository baru di GitHub: [https://github.com/new](https://github.com/new)
   - Nama repo: `e-liputan-humas-dprd` (atau nama lain pilihan Anda).
   - Biarkan opsi *Initialize with README* tidak dicentang.

2. Hubungkan remote dan push dari terminal Anda:
```bash
git init
git add .
git commit -m "feat: inisialisasi sistem pemesanan peliputan humas dprd kota tangerang"
git branch -M main
git remote add origin https://github.com/Putra45/e-liputan-humas-dprd.git
git push -u origin main
```
