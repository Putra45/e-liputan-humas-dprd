package handler

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"eliputan/internal/db"
	"eliputan/internal/middleware"

	"golang.org/x/crypto/bcrypt"
)

// JSON API for the Next.js frontend. Mirrors the HTML handlers' logic.

func writeJSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	json.NewEncoder(w).Encode(v)
}

func jsonError(w http.ResponseWriter, status int, msg string) {
	writeJSON(w, status, map[string]any{"error": msg})
}

var kategoriList = []string{"Pimpinan", "Komisi I", "Komisi II", "Komisi III", "Komisi IV"}

// orderDTO adds the computed fields the HTML templates used to derive.
type orderDTO struct {
	MediaOrder
	HasFile          bool   `json:"HasFile"`
	FileURL          string `json:"FileURL"`
	TanggalFormatted string `json:"TanggalFormatted"`
	WaktuFinal       string `json:"WaktuFinal"`
	StatusLabel      string `json:"StatusLabel"`
	IsApproved       bool   `json:"IsApproved"`
}

func toDTO(o MediaOrder) orderDTO {
	d := orderDTO{
		MediaOrder:       o,
		HasFile:          o.HasFile(),
		TanggalFormatted: o.TanggalFormatted(),
		WaktuFinal:       o.WaktuFinal(),
		StatusLabel:      o.StatusLabel(),
		IsApproved:       o.IsApproved(),
	}
	if o.HasFile() {
		d.FileURL = o.FileURL()
	}
	return d
}

var jenisPublikasiList = []string{
	"Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)",
	"Dokumentasi (Foto/Video)",
	"Rilis Website",
	"Konten Media Sosial (Kegiatan pribadi)",
}

// GET /api/portal
func (h *Handler) APIPortal(w http.ResponseWriter, r *http.Request) {
	var todayApproved int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE tanggal_kegiatan = ? AND status = 'approved'",
		time.Now().Format("2006-01-02")).Scan(&todayApproved)

	rows, err := h.db.Query(`SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at
		FROM media_orders WHERE status IN ('approved','pending') AND tanggal_kegiatan >= ? ORDER BY tanggal_kegiatan ASC, waktu_mulai ASC LIMIT 25`,
		time.Now().Format("2006-01-02"))
	if err != nil {
		jsonError(w, 500, "Gagal memuat data")
		return
	}
	defer rows.Close()

	upcoming := []orderDTO{}
	for rows.Next() {
		var o MediaOrder
		rows.Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt)
		upcoming = append(upcoming, toDTO(o))
	}

	writeJSON(w, 200, map[string]any{
		"kategoriList":       kategoriList,
		"jenisPublikasiList": jenisPublikasiList,
		"todayApprovedCount": todayApproved,
		"upcomingOrders":     upcoming,
		"today":              time.Now().Format("2006-01-02"),
	})
}

// POST /api/orders  (multipart)
func (h *Handler) APIOrderCreate(w http.ResponseWriter, r *http.Request) {
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		jsonError(w, 400, "Form tidak valid")
		return
	}

	namaKegiatan := strings.TrimSpace(r.FormValue("nama_kegiatan"))
	kategori := strings.TrimSpace(r.FormValue("kategori"))
	tanggal := strings.TrimSpace(r.FormValue("tanggal_kegiatan"))
	waktuMulai := strings.TrimSpace(r.FormValue("waktu_mulai"))
	jenisPublikasi := strings.TrimSpace(r.FormValue("jenis_publikasi"))
	namaPemohon := strings.TrimSpace(r.FormValue("nama_pemohon"))
	noWhatsapp := strings.TrimSpace(r.FormValue("no_whatsapp"))
	fraksi := strings.TrimSpace(r.FormValue("fraksi"))
	if fraksi == "" {
		fraksi = "-"
	}

	var errs []string
	if namaKegiatan == "" {
		errs = append(errs, "Nama kegiatan wajib diisi.")
	}
	if kategori == "" {
		errs = append(errs, "Kategori kegiatan wajib dipilih.")
	}
	if tanggal == "" {
		errs = append(errs, "Tanggal kegiatan wajib diisi.")
	}
	if waktuMulai == "" {
		errs = append(errs, "Waktu kegiatan wajib diisi.")
	}
	if jenisPublikasi == "" {
		errs = append(errs, "Jenis publikasi wajib dipilih.")
	}
	if namaPemohon == "" {
		errs = append(errs, "Nama pemohon wajib diisi.")
	}
	if len(noWhatsapp) < 9 {
		errs = append(errs, "Nomor WhatsApp wajib diisi (minimal 9 digit).")
	}
	if len(noWhatsapp) > 20 {
		errs = append(errs, "Nomor WhatsApp terlalu panjang.")
	}
	if len(errs) > 0 {
		writeJSON(w, 422, map[string]any{"errors": errs})
		return
	}

	// One active booking per date: pending and approved both hold the slot.
	var taken int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE date(tanggal_kegiatan) = date(?) AND status IN ('pending','approved')", tanggal).Scan(&taken)
	if taken > 0 {
		jsonError(w, 422, "Tanggal tersebut sudah dibooking agenda lain. Silakan pilih tanggal lain.")
		return
	}

	namaKegiatan = sanitize(namaKegiatan)
	namaPemohon = sanitize(namaPemohon)
	noWhatsapp = sanitizePhone(noWhatsapp)
	kategori = sanitize(kategori)
	fraksi = sanitize(fraksi)

	var filePath string
	if file, header, err := r.FormFile("file_dokumen"); err == nil {
		defer file.Close()
		ext := strings.ToLower(filepath.Ext(header.Filename))
		allowed := map[string]bool{".pdf": true, ".doc": true, ".docx": true, ".jpg": true, ".jpeg": true, ".png": true}
		if !allowed[ext] {
			jsonError(w, 422, "Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, JPG, atau PNG.")
			return
		}
		if header.Size > 10<<20 {
			jsonError(w, 422, "Ukuran dokumen maksimal 10 MB.")
			return
		}
		base := strings.TrimSuffix(header.Filename, ext)
		filename := fmt.Sprintf("%d_%s%s", time.Now().UnixNano(), sanitizeFilename(base), ext)
		os.MkdirAll("uploads", 0755)
		dst, err := os.Create(filepath.Join("uploads", filename))
		if err != nil {
			log.Printf("file create error: %v", err)
			jsonError(w, 500, "Gagal menyimpan file.")
			return
		}
		defer dst.Close()
		io.Copy(dst, file)
		filePath = filename
	}

	kode := db.GenerateKodeBooking(h.db)
	fileDoc := sql.NullString{String: filePath, Valid: filePath != ""}

	if _, err := h.db.Exec(`INSERT INTO media_orders (kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, jenis_publikasi, file_dokumen, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)`,
		kode, namaPemohon, fraksi, noWhatsapp, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, fileDoc, "pending"); err != nil {
		log.Printf("insert error: %v", err)
		jsonError(w, 500, "Gagal menyimpan data.")
		return
	}

	waLink := buildManualWALink(kode, namaPemohon, fraksi, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, noWhatsapp, "pending", "")
	sendWhatsAppGateway(noWhatsapp, buildWAMessage(kode, namaPemohon, fraksi, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, "pending", ""))

	writeJSON(w, 201, map[string]any{
		"kode":     kode,
		"kegiatan": namaKegiatan,
		"tanggal":  tanggal,
		"waktu":    waktuMulai,
		"waLink":   waLink,
	})
}

// POST /api/auth/login
func (h *Handler) APILogin(w http.ResponseWriter, r *http.Request) {
	var payload struct {
		Email    string `json:"email"`
		Password string `json:"password"`
	}
	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		jsonError(w, 400, "Permintaan tidak valid")
		return
	}
	email := strings.TrimSpace(payload.Email)

	ip := middleware.GetClientIP(r)
	if !middleware.CheckLoginRate(ip) {
		jsonError(w, 429, "Terlalu banyak percobaan login. Coba lagi dalam 5 menit.")
		return
	}
	middleware.RecordLoginAttempt(ip)

	var id int
	var hash string
	err := h.db.QueryRow("SELECT id, password FROM users WHERE email = ?", email).Scan(&id, &hash)
	if err != nil || bcrypt.CompareHashAndPassword([]byte(hash), []byte(payload.Password)) != nil {
		jsonError(w, 401, "Email atau password yang Anda masukkan salah.")
		return
	}

	token := middleware.GenerateSessionToken()
	h.db.Exec("INSERT INTO sessions (token, user_id, expires_at) VALUES (?, ?, ?)", token, id, time.Now().Add(24*time.Hour))
	http.SetCookie(w, &http.Cookie{
		Name: "session", Value: token, Path: "/", HttpOnly: true,
		SameSite: http.SameSiteStrictMode, Secure: r.TLS != nil, MaxAge: 86400,
	})
	writeJSON(w, 200, map[string]any{"ok": true})
}

// POST /api/auth/logout
func (h *Handler) APILogout(w http.ResponseWriter, r *http.Request) {
	if sessToken, ok := r.Context().Value(middleware.SessionKey).(string); ok {
		h.db.Exec("DELETE FROM sessions WHERE token = ?", sessToken)
	}
	http.SetCookie(w, &http.Cookie{Name: "session", Value: "", Path: "/", MaxAge: -1})
	writeJSON(w, 200, map[string]any{"ok": true})
}

// GET /api/auth/me
func (h *Handler) APIMe(w http.ResponseWriter, r *http.Request) {
	uid, ok := middleware.RequireAuth(r)
	if !ok {
		writeJSON(w, 200, map[string]any{"authenticated": false})
		return
	}
	name, _ := r.Context().Value(middleware.UserNameKey).(string)
	writeJSON(w, 200, map[string]any{"authenticated": true, "id": uid, "name": name})
}

// GET /api/admin/orders
func (h *Handler) APIAdminOrders(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		jsonError(w, 401, "Unauthorized")
		return
	}

	q := r.URL.Query()
	where := []string{"1=1"}
	var args []any
	if s := q.Get("status"); s != "" && s != "all" {
		where = append(where, "status = ?")
		args = append(args, s)
	}
	if t := q.Get("tanggal"); t != "" {
		where = append(where, "tanggal_kegiatan = ?")
		args = append(args, t)
	}
	if s := q.Get("search"); s != "" {
		where = append(where, "(nama_pemohon LIKE ? OR nama_kegiatan LIKE ? OR kode_booking LIKE ? OR no_whatsapp LIKE ?)")
		like := "%" + s + "%"
		args = append(args, like, like, like, like)
	}

	page, _ := strconv.Atoi(q.Get("page"))
	if page < 1 {
		page = 1
	}
	limit := 15
	offset := (page - 1) * limit
	whereStr := strings.Join(where, " AND ")

	var total int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE "+whereStr, args...).Scan(&total)

	rows, err := h.db.Query("SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at FROM media_orders WHERE "+whereStr+" ORDER BY tanggal_kegiatan DESC, id DESC LIMIT ? OFFSET ?",
		append(args, limit, offset)...)
	if err != nil {
		jsonError(w, 500, "Gagal memuat data")
		return
	}
	defer rows.Close()

	orders := []orderDTO{}
	for rows.Next() {
		var o MediaOrder
		rows.Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt)
		orders = append(orders, toDTO(o))
	}

	var totalAll, pending, approved, rejected, todayCount int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders").Scan(&totalAll)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='pending'").Scan(&pending)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='approved'").Scan(&approved)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='rejected'").Scan(&rejected)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE tanggal_kegiatan=? AND status='approved'", time.Now().Format("2006-01-02")).Scan(&todayCount)

	totalPages := (total + limit - 1) / limit
	writeJSON(w, 200, map[string]any{
		"orders": orders,
		"stats": map[string]int{
			"total": totalAll, "pending": pending, "approved": approved,
			"rejected": rejected, "today": todayCount,
		},
		"page": page, "totalPages": totalPages,
		"hasPrev": page > 1, "hasNext": page < totalPages,
		"prevPage": page - 1, "nextPage": page + 1,
	})
}

// POST /api/admin/orders/{id}/status
func (h *Handler) APIAdminOrderStatus(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		jsonError(w, 401, "Unauthorized")
		return
	}
	r.ParseForm()
	id := r.PathValue("id")
	status := r.FormValue("status")
	waktuDisetujui := r.FormValue("waktu_disetujui")
	catatanAdmin := sanitize(strings.TrimSpace(r.FormValue("catatan_admin")))

	if status != "approved" && status != "rejected" && status != "pending" {
		jsonError(w, 422, "Status tidak valid.")
		return
	}

	var o MediaOrder
	if err := h.db.QueryRow("SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at FROM media_orders WHERE id = ?", id).
		Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt); err != nil {
		jsonError(w, 404, "Permohonan tidak ditemukan")
		return
	}

	waktuVal := sql.NullString{}
	if status == "approved" && waktuDisetujui != "" {
		waktuVal = sql.NullString{String: waktuDisetujui, Valid: true}
	}

	if status != "rejected" {
		var taken int
		h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE date(tanggal_kegiatan) = date(?) AND status IN ('pending','approved') AND id <> ?",
			o.TanggalKegiatan, o.ID).Scan(&taken)
		if taken > 0 {
			jsonError(w, 422, "Tanggal tersebut sudah dipakai permohonan lain yang aktif.")
			return
		}
	}

	if _, err := h.db.Exec("UPDATE media_orders SET status = ?, waktu_disetujui = ?, catatan_admin = ?, updated_at = ? WHERE id = ?",
		status, waktuVal, catatanAdmin, time.Now(), id); err != nil {
		jsonError(w, 500, "Gagal menyimpan perubahan")
		return
	}

	waktuFinal := truncTime(o.WaktuMulai)
	if waktuVal.Valid {
		waktuFinal = truncTime(waktuVal.String)
	}
	waLink := buildManualWALink(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, waktuFinal, o.JenisPublikasi, o.NoWhatsapp, status, catatanAdmin)
	sendWhatsAppGateway(o.NoWhatsapp, buildWAMessage(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, waktuFinal, o.JenisPublikasi, status, catatanAdmin))

	writeJSON(w, 200, map[string]any{"ok": true, "waLink": waLink})
}

// POST /api/admin/orders/{id}/delete
func (h *Handler) APIAdminOrderDelete(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		jsonError(w, 401, "Unauthorized")
		return
	}
	id := r.PathValue("id")

	var file sql.NullString
	h.db.QueryRow("SELECT file_dokumen FROM media_orders WHERE id = ?", id).Scan(&file)
	if file.Valid && file.String != "" {
		os.Remove(filepath.Join("uploads", file.String))
	}
	if _, err := h.db.Exec("DELETE FROM media_orders WHERE id = ?", id); err != nil {
		jsonError(w, 500, "Gagal menghapus data")
		return
	}
	writeJSON(w, 200, map[string]any{"ok": true})
}
