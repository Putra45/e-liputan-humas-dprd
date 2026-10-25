package db

import (
	"database/sql"
	"fmt"
	"log"
	"strings"
	"time"

	_ "modernc.org/sqlite"
	"golang.org/x/crypto/bcrypt"
)

func Open(path string) *sql.DB {
	db, err := sql.Open("sqlite", path+"?_journal=WAL&_timeout=5000&_fk=true")
	if err != nil {
		log.Fatal("db open:", err)
	}
	db.SetMaxOpenConns(1)
	return db
}

func Migrate(db *sql.DB) {
	stmts := []string{
		`CREATE TABLE IF NOT EXISTS users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL,
			email TEXT NOT NULL UNIQUE,
			password TEXT NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		)`,
		`CREATE TABLE IF NOT EXISTS sessions (
			token TEXT PRIMARY KEY,
			user_id INTEGER NOT NULL,
			expires_at DATETIME NOT NULL,
			FOREIGN KEY (user_id) REFERENCES users(id)
		)`,
		`CREATE TABLE IF NOT EXISTS media_orders (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			kode_booking TEXT NOT NULL UNIQUE,
			nama_pemohon TEXT NOT NULL,
			fraksi TEXT DEFAULT '-',
			no_whatsapp TEXT NOT NULL,
			nama_kegiatan TEXT NOT NULL,
			kategori TEXT NOT NULL,
			tanggal_kegiatan DATE NOT NULL,
			waktu_mulai TEXT NOT NULL,
			waktu_disetujui TEXT,
			jenis_publikasi TEXT NOT NULL,
			file_dokumen TEXT,
			status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','approved','rejected')),
			catatan_admin TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
		)`,
		`CREATE TABLE IF NOT EXISTS csrf_tokens (
			token TEXT PRIMARY KEY,
			session_token TEXT NOT NULL,
			expires_at DATETIME NOT NULL
		)`,
	}
	for _, s := range stmts {
		if _, err := db.Exec(s); err != nil {
			log.Fatal("migrate:", err)
		}
	}
}

func Seed(db *sql.DB) {
	var count int
	db.QueryRow("SELECT COUNT(*) FROM users").Scan(&count)
	if count > 0 {
		return
	}

	hash, _ := bcrypt.GenerateFromPassword([]byte("password123"), bcrypt.DefaultCost)
	db.Exec("INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
		"Tim Publikasi Humas DPRD", "admin@dprd-tangerangkota.go.id", string(hash))

	db.QueryRow("SELECT COUNT(*) FROM media_orders").Scan(&count)
	if count > 0 {
		return
	}

	today := time.Now().Format("2006-01-02")
	tomorrow := time.Now().AddDate(0, 0, 1).Format("2006-01-02")
	dayAfter := time.Now().AddDate(0, 0, 2).Format("2006-01-02")
	prefix := fmt.Sprintf("HUMAS-%s-", time.Now().Format("200601"))

	samples := []struct {
		kode, nama, fraksi, wa, kegiatan, kategori, tanggal, waktuM, waktuD, jenis, status, catatan string
	}{
		{prefix + "001", "H. Rusdi Alam, M.Si", "Golkar", "081289123456",
			"Kunjungan Kerja Pimpinan DPRD ke Balai Kota Tangerang", "Pimpinan",
			today, "09:00", "09:00",
			"Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)", "approved",
			"Tim 1 (Kameramen & Penulis Rilis) meluncur pukul 08.30 WIB."},
		{prefix + "002", "Andri S. Permana, S.Sos", "PDIP", "081312345678",
			"Rapat Dengar Pendapat (RDP) Komisi II terkait UMKM", "Komisi II",
			tomorrow, "13:30", "13:30",
			"Dokumentasi (Foto/Video)", "approved",
			"Fotografer standby di Ruang Rapat Komisi II."},
		{prefix + "003", "Tengku Iwan Jayasyah Putra", "PKS", "081598765432",
			"Sosialisasi Perda Kawasan Tanpa Rokok di Ciledug", "Komisi IV",
			dayAfter, "10:00", "",
			"Rilis Website", "pending", ""},
	}

	for _, s := range samples {
		waktuD := sql.NullString{String: s.waktuD, Valid: s.waktuD != ""}
		catatan := sql.NullString{String: s.catatan, Valid: s.catatan != ""}
		db.Exec(`INSERT INTO media_orders (kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, status, catatan_admin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)`,
			s.kode, s.nama, s.fraksi, s.wa, s.kegiatan, s.kategori, s.tanggal, s.waktuM, waktuD, s.jenis, s.status, catatan)
	}
}

func GenerateKodeBooking(db *sql.DB) string {
	prefix := fmt.Sprintf("HUMAS-%s-", time.Now().Format("200601"))
	var lastKode sql.NullString
	db.QueryRow("SELECT kode_booking FROM media_orders WHERE kode_booking LIKE ? ORDER BY id DESC LIMIT 1", prefix+"%").Scan(&lastKode)

	next := 1
	if lastKode.Valid {
		numStr := lastKode.String[len(lastKode.String)-3:]
		for _, c := range numStr {
			next = next*10 + int(c-'0')
		}
		// Reset: parse properly
		next = 0
		for _, c := range numStr {
			next = next*10 + int(c-'0')
		}
		next++
	}
	return fmt.Sprintf("%s%03d", prefix, next)
}

func FormatPhoneNumber(number string) string {
	var clean strings.Builder
	for _, c := range number {
		if c >= '0' && c <= '9' {
			clean.WriteRune(c)
		}
	}
	s := clean.String()
	if strings.HasPrefix(s, "0") {
		s = "62" + s[1:]
	} else if strings.HasPrefix(s, "8") {
		s = "62" + s
	}
	return s
}
