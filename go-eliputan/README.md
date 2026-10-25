# Sistem Pemesanan Peliputan & Kalender Publikasi (E-Liputan) — Go Edition
### Tim Publikasi Humas & Protokol — Sekretariat DPRD Kota Tangerang

Aplikasi web full-stack untuk manajemen jadwal liputan dan dokumentasi internal khusus Anggota Dewan dan Sekretariat DPRD Kota Tangerang. **Versi 2.0** — ditulis ulang dalam **Go (Golang)** dari Laravel/PHP untuk keamanan dan performa maksimal.

---

## 🔒 Kenapa Pindah dari PHP ke Go?

| Aspek | PHP/Laravel (Lama) | Go (Baru) |
|-------|-------------------|-----------|
| **Vulnerability** | Banyak dependency (Composer), sering kena CVE | Compiled binary, minimal dependency, zero `node_modules` |
| **Runtime** | Butuh PHP, Composer, Apache/Nginx | Single binary executable, langsung jalan |
| **Performa** | Interpreted | Compiled, ~10-50x lebih cepat |
| **Memory** | ~100-200MB | ~10-20MB |
| **Deploy** | Install PHP, Composer, copy vendor/ | Copy 1 file `.exe` saja |
| **Security Headers** | Manual middleware | Built-in: CSP, X-Frame-Options, CSRF, rate limiting |

---

## 🛠️ Tech Stack
- **Bahasa:** Go 1.27 (compiled, type-safe)
- **Database:** SQLite (embedded, WAL mode, zero config)
- **Template:** Go `html/template` (auto-escaped, XSS-proof)
- **Frontend:** Tailwind CSS (CDN), FullCalendar 6.1.15
- **Auth:** bcrypt password hashing, secure session cookies
- **CSRF:** Cookie-based double-submit token
- **WhatsApp:** Fonnte API Gateway + fallback wa.me manual

---

## 🔒 Fitur Keamanan (Built-in)

1. **CSRF Protection** — Double-submit cookie pattern pada semua POST
2. **Bcrypt Password** — Password hash dengan cost default
3. **Security Headers** — CSP, X-Frame-Options DENY, X-Content-Type-Options nosniff
4. **Login Rate Limiting** — Max 10 attempts per 5 menit per IP
5. **Input Sanitization** — HTML entity encoding pada semua input
6. **File Upload Validation** — Whitelist extension + max 10MB
7. **Session Security** — HttpOnly, SameSite=Strict, 24h expiry
8. **SQL Injection Proof** — Parameterized queries (Go `database/sql`)
9. **Path Traversal Proof** — Filename sanitization pada upload
10. **No Default Credentials Exposed** — Credential hint dihapus dari login page

---

## 🚀 Cara Install & Jalankan

### Opsi A: Build dari Source
```bash
# 1. Install Go (https://go.dev/dl/)
# 2. Clone & build
git clone https://github.com/Putra45/e-liputan-humas-dprd.git
cd e-liputan-humas-dprd/go-eliputan
go build -o eliputan.exe .

# 3. Jalankan
./eliputan.exe
```

### Opsi B: Langsung Jalankan Binary
Download binary dari Release, lalu jalankan. Tidak perlu install apapun.

### Akses
- **Portal Pemohon:** http://localhost:8000
- **Login Admin:** http://localhost:8000/login

---

## 🔑 Akun Admin Default
- **Email:** `admin@dprd-tangerangkota.go.id`
- **Password:** `password123`

> ⚠️ **SEGERA GANTI PASSWORD** setelah deploy pertama kali!

---

## 📲 Konfigurasi WhatsApp Gateway (Opsional)
Edit file `.env`:
```env
WA_GATEWAY_PROVIDER=fonnte
WA_GATEWAY_TOKEN=YOUR_FONNTE_TOKEN_HERE
WA_GATEWAY_ENDPOINT=https://api.fonnte.com/send
```

---

## 📁 Struktur Project

```
go-eliputan/
├── main.go                     # Entry point, routing, .env loader
├── internal/
│   ├── db/db.go               # Database: open, migrate, seed, helpers
│   ├── handler/handler.go     # HTTP handlers (portal, auth, admin)
│   └── middleware/middleware.go # Logger, CSRF, session, security headers, rate limit
├── templates/
│   ├── layout.html            # Base layout (header, footer, dark mode)
│   ├── portal.html            # Public portal + calendar + form
│   ├── login.html             # Admin login
│   └── admin.html             # Admin dashboard + approval modal
├── static/
│   └── style.css              # Placeholder
├── uploads/                   # User uploaded files (gitignored)
├── .env                       # Configuration (gitignored)
├── .env.example               # Template
├── .gitignore
├── go.mod
└── go.sum
```

---

## 🏛️ Fitur Lengkap

### Portal Dewan & Staf
- Kalender Publikasi Interaktif (FullCalendar)
- Formulir Pengajuan 3-step (kegiatan → pemohon → dokumen)
- Klik tanggal di kalender → otomatis isi form
- Quick date & time presets
- H-1 warning indicator
- Daftar Agenda Tulisan Besar (aksesibel untuk senior)

### Panel Admin
- Dashboard statistik (total/pending/approved/rejected/hari ini)
- Filter & pencarian lengkap
- Modal verifikasi & penyesuaian jam (time adjustment)
- Notifikasi WhatsApp otomatis + fallback manual wa.me
- Dark mode toggle

---

© Sub Bagian Humas dan Protokol — Sekretariat DPRD Kota Tangerang
