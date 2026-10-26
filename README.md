# E-Liputan — Humas & Protokol DPRD Kota Tangerang

Sistem pemesanan peliputan, kalender publikasi, dan verifikasi permohonan untuk Tim Publikasi Humas & Protokol Sekretariat DPRD Kota Tangerang.

## Fitur

**Portal publik**
- Kalender jadwal liputan (FullCalendar): hijau untuk yang sudah ACC, amber untuk yang masih pending.
- Klik tanggal di kalender untuk langsung membuka formulir pengajuan.
- Formulir lengkap: kegiatan, kategori, tanggal, jam, jenis publikasi, data pemohon, dan unggah dokumen (maks 10 MB).
- Nomor WhatsApp dan dokumen pemohon tidak pernah ditampilkan di sisi publik.

**Panel admin** (`/login`)
- Statistik: total, pending, disetujui, ditolak, agenda hari ini.
- Filter status/tanggal dan pencarian, dengan pagination.
- Modal verifikasi: setujui, tolak, pending, ubah jam liputan final, catatan untuk pemohon, hapus permohonan.
- Notifikasi WhatsApp otomatis saat status berubah, dengan fallback tautan `wa.me`.

## Stack

| Bagian | Teknologi |
| --- | --- |
| API & database | Go 1.23 (net/http), SQLite, bcrypt, CSRF token, rate limit login |
| Frontend | Next.js 16 + TypeScript + Tailwind CSS v4 |
| Kalender | FullCalendar v7 |
| Web server | Nginx (aaPanel) |

## Struktur

```
go-eliputan/   API Go + template HTML lama (legacy)
frontend/      Next.js App Router (portal, login, admin)
```

## Menjalankan lokal

Butuh Go 1.23+ dan Node 20+.

```bash
# Terminal 1 — API di port 8080
cd go-eliputan
PORT=8080 go run .

# Terminal 2 — frontend di port 3000
cd frontend
npm install
npm run dev
```

Buka http://localhost:3000. Frontend mem-proxy `/api/*` dan `/uploads/*` ke `http://127.0.0.1:8080` (atur lewat `NEXT_PUBLIC_API_URL` bila beda).

## Akun admin bawaan

- Email: `admin@dprd-tangerangkota.go.id`
- Password: `password123` — **ganti setelah login pertama**.

## Konfigurasi WhatsApp gateway (opsional)

Isi `go-eliputan/.env`:

```env
WA_GATEWAY_PROVIDER=fonnte
WA_GATEWAY_TOKEN=token_anda
WA_GATEWAY_ENDPOINT=https://api.fonnte.com/send
```

Tanpa token, sistem tetap berjalan dan menyediakan tombol kirim manual via `wa.me`.

## Deploy

Di VPS (Ubuntu + aaPanel, sebagai root):

```bash
bash /www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan/deploy.sh
```

Script memasang Go/Node bila perlu, build API + frontend, membuat dua systemd service (`eliputan` di 8080, `eliputan-web` di 3000), lalu menulis konfigurasi Nginx: `/` ke Next.js, `/api/` dan `/uploads/` ke Go.

Auto-deploy GitHub Actions (`.github/workflows/deploy.yml`) memakai secret `VPS_HOST` dan `VPS_PASSWORD`.

## Keamanan

- Password disimpan sebagai bcrypt hash.
- Semua query memakai parameter terikat (anti SQL injection).
- CSRF token per sesi (`X-CSRF-Token` atau field `_csrf`).
- Cookie sesi `HttpOnly` + `SameSite=Strict`, masa aktif 24 jam.
- Rate limit percobaan login per IP.
- Unggahan dibatasi tipe file (PDF/DOC/DOCX/JPG/PNG) dan ukuran 10 MB.
