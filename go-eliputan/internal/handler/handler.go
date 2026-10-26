package handler

import (
	"database/sql"
	"embed"
	"encoding/json"
	"fmt"
	"html/template"
	"io"
	"log"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"eliputan/internal/db"
	"eliputan/internal/middleware"

	"golang.org/x/crypto/bcrypt"
)

type Handler struct {
	db    *sql.DB
	tmpls map[string]*template.Template
}

type MediaOrder struct {
	ID               int
	KodeBooking      string
	NamaPemohon      string
	Fraksi           string
	NoWhatsapp       string
	NamaKegiatan     string
	Kategori         string
	TanggalKegiatan  string
	WaktuMulai       string
	WaktuDisetujui   sql.NullString
	JenisPublikasi   string
	FileDokumen      sql.NullString
	Status           string
	CatatanAdmin     sql.NullString
	CreatedAt        string
	UpdatedAt        string
}

func (o MediaOrder) WaktuFinal() string {
	if o.WaktuDisetujui.Valid && o.WaktuDisetujui.String != "" {
		return truncTime(o.WaktuDisetujui.String)
	}
	return truncTime(o.WaktuMulai)
}

func (o MediaOrder) StatusBadgeBg() string {
	switch o.Status {
	case "approved":
		return "bg-emerald-100 text-emerald-800 border-emerald-300"
	case "rejected":
		return "bg-rose-100 text-rose-800 border-rose-300"
	default:
		return "bg-amber-100 text-amber-800 border-amber-300"
	}
}

func (o MediaOrder) StatusLabel() string {
	switch o.Status {
	case "approved":
		return "Disetujui"
	case "rejected":
		return "Ditolak"
	default:
		return "Pending"
	}
}

func (o MediaOrder) IsApproved() bool  { return o.Status == "approved" }
func (o MediaOrder) HasFile() bool     { return o.FileDokumen.Valid && o.FileDokumen.String != "" }
func (o MediaOrder) FileURL() string   { return "/uploads/" + o.FileDokumen.String }

func (o MediaOrder) TanggalFormatted() string {
	t, err := time.Parse("2006-01-02", o.TanggalKegiatan[:10])
	if err != nil {
		return o.TanggalKegiatan
	}
	return formatIndonesianDate(t)
}

func (o MediaOrder) TanggalShort() string {
	t, err := time.Parse("2006-01-02", o.TanggalKegiatan[:10])
	if err != nil {
		return o.TanggalKegiatan
	}
	return fmt.Sprintf("%d %s %d", t.Day(), shortMonth(t.Month()), t.Year())
}

func (o MediaOrder) TanggalDay() string {
	t, _ := time.Parse("2006-01-02", o.TanggalKegiatan[:10])
	return fmt.Sprintf("%02d", t.Day())
}

func (o MediaOrder) TanggalMonthShort() string {
	t, _ := time.Parse("2006-01-02", o.TanggalKegiatan[:10])
	return strings.ToUpper(shortMonth(t.Month()))
}

func truncTime(s string) string {
	if len(s) >= 5 {
		return s[:5]
	}
	return s
}

func New(database *sql.DB, fs embed.FS) *Handler {
	funcMap := template.FuncMap{
		"safeHTML": func(s string) template.HTML { return template.HTML(s) },
		"now":      func() time.Time { return time.Now() },
		"today":    func() string { return time.Now().Format("2006-01-02") },
		"year":     func() int { return time.Now().Year() },
		"seq": func(n int) []int {
			s := make([]int, n)
			for i := range s {
				s[i] = i
			}
			return s
		},
		"eq":        func(a, b string) bool { return a == b },
		"ne":        func(a, b string) bool { return a != b },
		"add":       func(a, b int) int { return a + b },
		"split":     func(s, sep string) []string { return strings.Split(s, sep) },
		"truncTime": truncTime,
		"slice": func(s string, start, end int) string {
			if end > len(s) {
				end = len(s)
			}
			if start > len(s) {
				return ""
			}
			return s[start:end]
		},
		"hasKey": func(m map[string]string, key string) bool {
			v, ok := m[key]
			return ok && v != ""
		},
		"getFlash": func(m map[string]string, key string) string {
			if m == nil {
				return ""
			}
			return m[key]
		},
	}

	// Parse each page template separately with the layout to avoid block conflicts
	layoutBytes, err := templateFS.ReadFile("templates/layout.html")
	if err != nil {
		log.Fatal("read layout:", err)
	}
	layoutStr := string(layoutBytes)

	pages := map[string]string{
		"portal.html": "templates/portal.html",
		"login.html":  "templates/login.html",
		"admin.html":  "templates/admin.html",
	}

	tmpls := make(map[string]*template.Template)
	for name, path := range pages {
		pageBytes, err := fs.ReadFile(path)
		if err != nil {
			log.Fatalf("read %s: %v", path, err)
		}
		t := template.Must(
			template.New("layout").Funcs(funcMap).Parse(layoutStr),
		)
		template.Must(t.New(name).Parse(string(pageBytes)))
		tmpls[name] = t
	}

	return &Handler{db: database, tmpls: tmpls}
}

// templateFS reference for New() - set from main
var templateFS embed.FS

func SetTemplateFS(fs embed.FS) {
	templateFS = fs
}

func (h *Handler) render(w http.ResponseWriter, name string, data map[string]any, r *http.Request) {
	if data == nil {
		data = map[string]any{}
	}
	// Inject common data
	data["CSRFToken"], _ = r.Context().Value(middleware.CSRFTokenKey).(string)
	_, data["IsAuthenticated"] = r.Context().Value(middleware.UserIDKey).(int)
	if uid, ok := r.Context().Value(middleware.UserIDKey).(int); ok && uid > 0 {
		data["IsAuthenticated"] = true
	}
	data["CurrentPath"] = r.URL.Path
	data["Year"] = time.Now().Year()

	// Ensure Flash always exists
	if _, ok := data["Flash"]; !ok {
		data["Flash"] = map[string]string{}
	}

	t, ok := h.tmpls[name]
	if !ok {
		log.Printf("template %s not found", name)
		http.Error(w, "Internal Server Error", 500)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	if err := t.ExecuteTemplate(w, "layout", data); err != nil {
		log.Printf("render %s: %v", name, err)
		http.Error(w, "Internal Server Error", 500)
	}
}

// ==================== PORTAL ====================

func (h *Handler) PortalIndex(w http.ResponseWriter, r *http.Request) {
	kategoriList := []string{"Pimpinan", "Komisi I", "Komisi II", "Komisi III", "Komisi IV"}
	jenisPublikasiList := []string{
		"Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)",
		"Dokumentasi (Foto/Video)",
		"Rilis Website",
		"Konten Media Sosial (Kegiatan pribadi)",
	}

	var todayApproved int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE tanggal_kegiatan = ? AND status = 'approved'",
		time.Now().Format("2006-01-02")).Scan(&todayApproved)

	rows, _ := h.db.Query(`SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at 
		FROM media_orders WHERE status IN ('approved','pending') AND tanggal_kegiatan >= ? ORDER BY tanggal_kegiatan ASC, waktu_mulai ASC LIMIT 25`,
		time.Now().Format("2006-01-02"))
	defer rows.Close()
	var upcoming []MediaOrder
	for rows.Next() {
		var o MediaOrder
		rows.Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt)
		upcoming = append(upcoming, o)
	}

	// Flash messages from session
	flash := getFlash(w, r)

	h.render(w, "portal.html", map[string]any{
		"KategoriList":       kategoriList,
		"JenisPublikasiList": jenisPublikasiList,
		"TodayApprovedCount": todayApproved,
		"UpcomingOrders":     upcoming,
		"Flash":              flash,
		"Today":              time.Now().Format("2006-01-02"),
	}, r)
}

func (h *Handler) PortalEvents(w http.ResponseWriter, r *http.Request) {
	rows, _ := h.db.Query(`SELECT id, kode_booking, nama_pemohon, kategori, nama_kegiatan, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, status FROM media_orders WHERE status IN ('approved','pending')`)
	defer rows.Close()

	var events []map[string]any
	for rows.Next() {
		var id int
		var kode, nama, kategori, kegiatan, tanggal, waktuM, status string
		var waktuD sql.NullString
		var jenis string
		rows.Scan(&id, &kode, &nama, &kategori, &kegiatan, &tanggal, &waktuM, &waktuD, &jenis, &status)

		waktu := truncTime(waktuM)
		if waktuD.Valid && waktuD.String != "" {
			waktu = truncTime(waktuD.String)
		}
		isApproved := status == "approved"

		bgColor := "#d97706"
		borderColor := "#b45309"
		statusLabel := "Pending"
		if isApproved {
			bgColor = "#059669"
			borderColor = "#047857"
			statusLabel = "Disetujui"
		}

		events = append(events, map[string]any{
			"id":              id,
			"title":           fmt.Sprintf("[%s] %s - %s", waktu, kegiatan, nama),
			"start":           tanggal[:10] + "T" + waktu + ":00",
			"backgroundColor": bgColor,
			"borderColor":     borderColor,
			"textColor":       "#ffffff",
			"extendedProps": map[string]any{
				"kode_booking":    kode,
				"nama_pemohon":    nama,
				"kategori":        kategori,
				"nama_kegiatan":   kegiatan,
				"waktu":           waktu + " WIB",
				"jenis_publikasi": jenis,
				"status":          status,
				"status_label":    statusLabel,
			},
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(events)
}

func (h *Handler) PortalStore(w http.ResponseWriter, r *http.Request) {
	r.ParseMultipartForm(10 << 20) // 10MB max

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

	// Validation
	var errors []string
	if namaKegiatan == "" {
		errors = append(errors, "Nama kegiatan wajib diisi.")
	}
	if kategori == "" {
		errors = append(errors, "Kategori kegiatan wajib dipilih.")
	}
	if tanggal == "" {
		errors = append(errors, "Tanggal kegiatan wajib diisi.")
	}
	if waktuMulai == "" {
		errors = append(errors, "Waktu kegiatan wajib diisi.")
	}
	if jenisPublikasi == "" {
		errors = append(errors, "Jenis publikasi wajib dipilih.")
	}
	if namaPemohon == "" {
		errors = append(errors, "Nama pemohon wajib diisi.")
	}
	if noWhatsapp == "" || len(noWhatsapp) < 9 {
		errors = append(errors, "Nomor WhatsApp wajib diisi (minimal 9 digit).")
	}
	if len(noWhatsapp) > 20 {
		errors = append(errors, "Nomor WhatsApp terlalu panjang.")
	}
	// Sanitize inputs against XSS
	namaKegiatan = sanitize(namaKegiatan)
	namaPemohon = sanitize(namaPemohon)
	noWhatsapp = sanitizePhone(noWhatsapp)
	kategori = sanitize(kategori)
	fraksi = sanitize(fraksi)

	if len(errors) > 0 {
		setFlash(w, "errors", strings.Join(errors, "|"))
		http.Redirect(w, r, "/", http.StatusSeeOther)
		return
	}

	// Handle file upload
	var filePath string
	file, header, err := r.FormFile("file_dokumen")
	if err == nil {
		defer file.Close()
		// Validate file type
		ext := strings.ToLower(filepath.Ext(header.Filename))
		allowed := map[string]bool{".pdf": true, ".doc": true, ".docx": true, ".jpg": true, ".jpeg": true, ".png": true}
		if !allowed[ext] {
			setFlash(w, "errors", "Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, JPG, atau PNG.")
			http.Redirect(w, r, "/", http.StatusSeeOther)
			return
		}
		if header.Size > 10<<20 {
			setFlash(w, "errors", "Ukuran dokumen maksimal 10 MB.")
			http.Redirect(w, r, "/", http.StatusSeeOther)
			return
		}
		// Save file
		filename := fmt.Sprintf("%d_%s%s", time.Now().UnixNano(), sanitizeFilename(header.Filename[:len(header.Filename)-len(ext)]), ext)
		os.MkdirAll("uploads", 0755)
		dst, err := os.Create(filepath.Join("uploads", filename))
		if err != nil {
			log.Printf("file create error: %v", err)
			setFlash(w, "errors", "Gagal menyimpan file.")
			http.Redirect(w, r, "/", http.StatusSeeOther)
			return
		}
		defer dst.Close()
		io.Copy(dst, file)
		filePath = filename
	}

	kode := db.GenerateKodeBooking(h.db)
	fileDoc := sql.NullString{String: filePath, Valid: filePath != ""}

	_, err = h.db.Exec(`INSERT INTO media_orders (kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, jenis_publikasi, file_dokumen, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)`,
		kode, namaPemohon, fraksi, noWhatsapp, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, fileDoc, "pending")
	if err != nil {
		log.Printf("insert error: %v", err)
		setFlash(w, "errors", "Gagal menyimpan data.")
		http.Redirect(w, r, "/", http.StatusSeeOther)
		return
	}

	// Build WA link
	waLink := buildManualWALink(kode, namaPemohon, fraksi, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, noWhatsapp, "pending", "")
	setFlash(w, "success_kode", kode)
	setFlash(w, "success_kegiatan", namaKegiatan)
	setFlash(w, "success_tanggal", tanggal)
	setFlash(w, "success_waktu", waktuMulai)
	setFlash(w, "wa_manual_link", waLink)

	// Send WA via gateway if configured
	sendWhatsAppGateway(noWhatsapp, buildWAMessage(kode, namaPemohon, fraksi, namaKegiatan, kategori, tanggal, waktuMulai, jenisPublikasi, "pending", ""))

	http.Redirect(w, r, "/", http.StatusSeeOther)
}

// ==================== AUTH ====================

func (h *Handler) LoginPage(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); ok {
		http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
		return
	}
	flash := getFlash(w, r)
	h.render(w, "login.html", map[string]any{"Flash": flash}, r)
}

func (h *Handler) LoginPost(w http.ResponseWriter, r *http.Request) {
	r.ParseForm()
	email := strings.TrimSpace(r.FormValue("email"))
	password := r.FormValue("password")

	ip := middleware.GetClientIP(r)
	if !middleware.CheckLoginRate(ip) {
		setFlash(w, "error", "Terlalu banyak percobaan login. Coba lagi dalam 5 menit.")
		http.Redirect(w, r, "/login", http.StatusSeeOther)
		return
	}
	middleware.RecordLoginAttempt(ip)

	var id int
	var hash string
	err := h.db.QueryRow("SELECT id, password FROM users WHERE email = ?", email).Scan(&id, &hash)
	if err != nil || bcrypt.CompareHashAndPassword([]byte(hash), []byte(password)) != nil {
		setFlash(w, "error", "Email atau password yang Anda masukkan salah.")
		http.Redirect(w, r, "/login", http.StatusSeeOther)
		return
	}

	token := middleware.GenerateSessionToken()
	expires := time.Now().Add(24 * time.Hour)
	h.db.Exec("INSERT INTO sessions (token, user_id, expires_at) VALUES (?, ?, ?)", token, id, expires)

	http.SetCookie(w, &http.Cookie{
		Name:     "session",
		Value:    token,
		Path:     "/",
		HttpOnly: true,
		SameSite: http.SameSiteStrictMode,
		Secure:   r.TLS != nil,
		MaxAge:   86400,
	})

	http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
}

func (h *Handler) Logout(w http.ResponseWriter, r *http.Request) {
	if sessToken, ok := r.Context().Value(middleware.SessionKey).(string); ok {
		h.db.Exec("DELETE FROM sessions WHERE token = ?", sessToken)
	}
	http.SetCookie(w, &http.Cookie{
		Name:   "session",
		Value:  "",
		Path:   "/",
		MaxAge: -1,
	})
	setFlash(w, "success", "Anda telah berhasil keluar.")
	http.Redirect(w, r, "/login", http.StatusSeeOther)
}

// ==================== ADMIN ====================

func (h *Handler) AdminRedirect(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		http.Redirect(w, r, "/login", http.StatusSeeOther)
		return
	}
	http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
}

func (h *Handler) AdminOrders(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		http.Redirect(w, r, "/login", http.StatusSeeOther)
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
	if f := q.Get("fraksi"); f != "" && f != "all" {
		where = append(where, "fraksi = ?")
		args = append(args, f)
	}
	if k := q.Get("kategori"); k != "" && k != "all" {
		where = append(where, "kategori = ?")
		args = append(args, k)
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

	queryArgs := append(args, limit, offset)
	rows, _ := h.db.Query("SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at FROM media_orders WHERE "+whereStr+" ORDER BY tanggal_kegiatan DESC, id DESC LIMIT ? OFFSET ?", queryArgs...)
	defer rows.Close()

	var orders []MediaOrder
	for rows.Next() {
		var o MediaOrder
		rows.Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt)
		orders = append(orders, o)
	}

	// Stats
	var totalAll, pending, approved, rejected, todayCount int
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders").Scan(&totalAll)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='pending'").Scan(&pending)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='approved'").Scan(&approved)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE status='rejected'").Scan(&rejected)
	h.db.QueryRow("SELECT COUNT(*) FROM media_orders WHERE tanggal_kegiatan=? AND status='approved'", time.Now().Format("2006-01-02")).Scan(&todayCount)

	totalPages := (total + limit - 1) / limit
	flash := getFlash(w, r)

	h.render(w, "admin.html", map[string]any{
		"Orders":     orders,
		"Stats":      map[string]int{"total": totalAll, "pending": pending, "approved": approved, "rejected": rejected, "today": todayCount},
		"Page":       page,
		"TotalPages": totalPages,
		"HasPrev":    page > 1,
		"HasNext":    page < totalPages,
		"PrevPage":   page - 1,
		"NextPage":   page + 1,
		"Query":      q,
		"Flash":      flash,
	}, r)
}

func (h *Handler) AdminOrderShow(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		http.Error(w, "Unauthorized", 401)
		return
	}

	id := r.PathValue("id")
	var o MediaOrder
	err := h.db.QueryRow("SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, file_dokumen, status, catatan_admin, created_at, updated_at FROM media_orders WHERE id = ?", id).
		Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.FileDokumen, &o.Status, &o.CatatanAdmin, &o.CreatedAt, &o.UpdatedAt)
	if err != nil {
		http.Error(w, "Not found", 404)
		return
	}

	waktuD := truncTime(o.WaktuMulai)
	if o.WaktuDisetujui.Valid && o.WaktuDisetujui.String != "" {
		waktuD = truncTime(o.WaktuDisetujui.String)
	}

	var fileURL *string
	if o.HasFile() {
		u := o.FileURL()
		fileURL = &u
	}

	waLink := buildManualWALink(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, o.WaktuFinal(), o.JenisPublikasi, o.NoWhatsapp, o.Status, o.CatatanAdmin.String)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]any{
		"order":                     o,
		"tanggal_formatted":         o.TanggalFormatted(),
		"waktu_mulai_formatted":     truncTime(o.WaktuMulai),
		"waktu_disetujui_formatted": waktuD,
		"file_url":                  fileURL,
		"manual_wa_link":            waLink,
	})
}

func (h *Handler) AdminOrderStatus(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		http.Redirect(w, r, "/login", http.StatusSeeOther)
		return
	}

	r.ParseForm()
	id := r.PathValue("id")
	status := r.FormValue("status")
	waktuDisetujui := r.FormValue("waktu_disetujui")
	catatanAdmin := strings.TrimSpace(r.FormValue("catatan_admin"))

	if status != "approved" && status != "rejected" && status != "pending" {
		setFlash(w, "error", "Status tidak valid.")
		http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
		return
	}

	// Sanitize
	catatanAdmin = sanitize(catatanAdmin)

	var waktuD sql.NullString
	if status == "approved" && waktuDisetujui != "" {
		waktuD = sql.NullString{String: waktuDisetujui, Valid: true}
	}
	var catatan sql.NullString
	if catatanAdmin != "" {
		catatan = sql.NullString{String: catatanAdmin, Valid: true}
	}

	if status == "pending" {
		h.db.Exec("UPDATE media_orders SET status=?, waktu_disetujui=NULL, catatan_admin=?, updated_at=CURRENT_TIMESTAMP WHERE id=?", status, catatan, id)
	} else {
		h.db.Exec("UPDATE media_orders SET status=?, waktu_disetujui=?, catatan_admin=?, updated_at=CURRENT_TIMESTAMP WHERE id=?", status, waktuD, catatan, id)
	}

	// Get order for WA
	var o MediaOrder
	h.db.QueryRow("SELECT kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, status, catatan_admin FROM media_orders WHERE id=?", id).
		Scan(&o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.Status, &o.CatatanAdmin)

	statusText := "diubah ke pending"
	if status == "approved" {
		statusText = "disetujui"
	} else if status == "rejected" {
		statusText = "ditolak"
	}

	gatewaySent := sendWhatsAppGateway(o.NoWhatsapp, buildWAMessage(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, o.WaktuFinal(), o.JenisPublikasi, o.Status, o.CatatanAdmin.String))
	waNotif := ""
	if gatewaySent {
		waNotif = " Notifikasi WhatsApp otomatis terkirim!"
	}

	waLink := buildManualWALink(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, o.WaktuFinal(), o.JenisPublikasi, o.NoWhatsapp, o.Status, o.CatatanAdmin.String)

	setFlash(w, "success", fmt.Sprintf("Pemesanan %s berhasil %s.%s", o.KodeBooking, statusText, waNotif))
	setFlash(w, "wa_manual_link", waLink)
	if !gatewaySent {
		setFlash(w, "warning", "Token WhatsApp Gateway belum diisi. Gunakan tombol Kirim WA Manual.")
	}

	http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
}

func (h *Handler) AdminOrderDelete(w http.ResponseWriter, r *http.Request) {
	if _, ok := middleware.RequireAuth(r); !ok {
		http.Redirect(w, r, "/login", http.StatusSeeOther)
		return
	}

	id := r.PathValue("id")
	var filePath sql.NullString
	h.db.QueryRow("SELECT file_dokumen FROM media_orders WHERE id=?", id).Scan(&filePath)
	if filePath.Valid && filePath.String != "" {
		os.Remove(filepath.Join("uploads", filePath.String))
	}
	h.db.Exec("DELETE FROM media_orders WHERE id=?", id)

	setFlash(w, "success", "Data pesanan berhasil dihapus.")
	http.Redirect(w, r, "/admin/orders", http.StatusSeeOther)
}

// ==================== HELPERS ====================

func setFlash(w http.ResponseWriter, key, value string) {
	http.SetCookie(w, &http.Cookie{
		Name:     "flash_" + key,
		Value:    url.QueryEscape(value),
		Path:     "/",
		MaxAge:   10,
		HttpOnly: true,
		SameSite: http.SameSiteLaxMode,
	})
}

func getFlash(w http.ResponseWriter, r *http.Request) map[string]string {
	flash := map[string]string{}
	for _, c := range r.Cookies() {
		if strings.HasPrefix(c.Name, "flash_") {
			key := strings.TrimPrefix(c.Name, "flash_")
			val, _ := url.QueryUnescape(c.Value)
			flash[key] = val
			// Clear the cookie
			http.SetCookie(w, &http.Cookie{Name: c.Name, Value: "", Path: "/", MaxAge: -1})
		}
	}
	return flash
}

func sanitize(s string) string {
	s = strings.ReplaceAll(s, "<", "&lt;")
	s = strings.ReplaceAll(s, ">", "&gt;")
	s = strings.ReplaceAll(s, "\"", "&quot;")
	return s
}

func sanitizePhone(s string) string {
	var clean strings.Builder
	for _, c := range s {
		if (c >= '0' && c <= '9') || c == '+' || c == '-' || c == ' ' {
			clean.WriteRune(c)
		}
	}
	return clean.String()
}

func sanitizeFilename(s string) string {
	var clean strings.Builder
	for _, c := range s {
		if (c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') || (c >= '0' && c <= '9') || c == '-' || c == '_' {
			clean.WriteRune(c)
		}
	}
	return clean.String()
}

func formatIndonesianDate(t time.Time) string {
	days := []string{"Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"}
	months := []string{"", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"}
	return fmt.Sprintf("%s, %d %s %d", days[t.Weekday()], t.Day(), months[t.Month()], t.Year())
}

func shortMonth(m time.Month) string {
	short := []string{"", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"}
	return short[m]
}

func buildWAMessage(kode, nama, fraksi, kegiatan, kategori, tanggal, waktu, jenis, status, catatan string) string {
	t, _ := time.Parse("2006-01-02", tanggal[:10])
	tanggalF := formatIndonesianDate(t)

	sapaan := fmt.Sprintf("Yth. *%s*", nama)
	if fraksi != "" && fraksi != "-" {
		sapaan = fmt.Sprintf("Yth. *%s* (%s)", nama, fraksi)
	}

	var msg strings.Builder
	msg.WriteString("🏛 *TIM PUBLIKASI HUMAS DPRD KOTA TANGERANG*\n")
	msg.WriteString("----------------------------------------\n")
	msg.WriteString(sapaan + "\n\n")

	switch status {
	case "approved":
		msg.WriteString("Permohonan peliputan Anda telah *DISETUJUI (ACC)*.\n\n")
		msg.WriteString(fmt.Sprintf("📋 *Kode Booking:* %s\n", kode))
		msg.WriteString(fmt.Sprintf("📌 *Kegiatan:* %s\n", kegiatan))
		msg.WriteString(fmt.Sprintf("🏛 *Kategori:* %s\n", kategori))
		msg.WriteString(fmt.Sprintf("🗓 *Hari/Tanggal:* %s\n", tanggalF))
		msg.WriteString(fmt.Sprintf("⏰ *Waktu Liputan:* %s WIB\n", waktu))
		msg.WriteString(fmt.Sprintf("🎬 *Jenis Publikasi:* %s\n", jenis))
		if catatan != "" {
			msg.WriteString(fmt.Sprintf("📝 *Catatan Tim Humas:* %s\n", catatan))
		}
		msg.WriteString("\nTim Liputan/Dokumentasi Humas DPRD Kota Tangerang akan hadir sesuai jadwal di atas.\nTerima kasih.")
	case "rejected":
		msg.WriteString("Mohon maaf, permohonan peliputan Anda *BELUM DAPAT DISETUJUI*.\n\n")
		msg.WriteString(fmt.Sprintf("📋 *Kode Booking:* %s\n", kode))
		msg.WriteString(fmt.Sprintf("📌 *Kegiatan:* %s\n", kegiatan))
		msg.WriteString(fmt.Sprintf("🗓 *Tanggal:* %s\n", tanggalF))
		if catatan != "" {
			msg.WriteString(fmt.Sprintf("📝 *Alasan / Keterangan:* %s\n", catatan))
		}
		msg.WriteString("\nSilakan berkoordinasi langsung dengan Tim Humas & Protokol Sekretariat DPRD Kota Tangerang.\nTerima kasih.")
	default:
		msg.WriteString("Permohonan peliputan Anda telah kami terima dengan status *MENUNGGU KONFIRMASI (PENDING)*.\n\n")
		msg.WriteString(fmt.Sprintf("📋 *Kode Booking:* %s\n", kode))
		msg.WriteString(fmt.Sprintf("📌 *Kegiatan:* %s\n", kegiatan))
		msg.WriteString(fmt.Sprintf("🗓 *Tanggal:* %s (%s WIB)\n", tanggalF, waktu))
		msg.WriteString("\nTim Humas akan segera memverifikasi ketersediaan jadwal. Terima kasih.")
	}
	return msg.String()
}

func buildManualWALink(kode, nama, fraksi, kegiatan, kategori, tanggal, waktu, jenis, noWA, status, catatan string) string {
	target := db.FormatPhoneNumber(noWA)
	message := buildWAMessage(kode, nama, fraksi, kegiatan, kategori, tanggal, waktu, jenis, status, catatan)
	return "https://wa.me/" + target + "?text=" + url.QueryEscape(message)
}

func sendWhatsAppGateway(noWA, message string) bool {
	token := os.Getenv("WA_GATEWAY_TOKEN")
	if token == "" {
		return false
	}
	endpoint := os.Getenv("WA_GATEWAY_ENDPOINT")
	if endpoint == "" {
		endpoint = "https://api.fonnte.com/send"
	}
	target := db.FormatPhoneNumber(noWA)

	client := &http.Client{Timeout: 6 * time.Second}
	form := url.Values{}
	form.Set("target", target)
	form.Set("message", message)
	form.Set("countryCode", "62")

	req, err := http.NewRequest("POST", endpoint, strings.NewReader(form.Encode()))
	if err != nil {
		return false
	}
	req.Header.Set("Authorization", token)
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	resp, err := client.Do(req)
	if err != nil {
		return false
	}
	defer resp.Body.Close()
	return resp.StatusCode >= 200 && resp.StatusCode < 300
}
