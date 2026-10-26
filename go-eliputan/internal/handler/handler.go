package handler

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"os"
	"strings"
	"time"

	"eliputan/internal/db"
	"eliputan/internal/middleware"
)

type Handler struct {
	db *sql.DB
}

func New(database *sql.DB) *Handler {
	return &Handler{db: database}
}

type MediaOrder struct {
	ID              int
	KodeBooking     string
	NamaPemohon     string
	Fraksi          string
	NoWhatsapp      string
	NamaKegiatan    string
	Kategori        string
	TanggalKegiatan string
	WaktuMulai      string
	WaktuDisetujui  sql.NullString
	JenisPublikasi  string
	FileDokumen     sql.NullString
	Status          string
	CatatanAdmin    sql.NullString
	CreatedAt       string
	UpdatedAt       string
}

func (o MediaOrder) WaktuFinal() string {
	if o.WaktuDisetujui.Valid && o.WaktuDisetujui.String != "" {
		return truncTime(o.WaktuDisetujui.String)
	}
	return truncTime(o.WaktuMulai)
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

func (o MediaOrder) IsApproved() bool { return o.Status == "approved" }
func (o MediaOrder) HasFile() bool    { return o.FileDokumen.Valid && o.FileDokumen.String != "" }
func (o MediaOrder) FileURL() string  { return "/uploads/" + o.FileDokumen.String }

func (o MediaOrder) TanggalFormatted() string {
	t, err := time.Parse("2006-01-02", o.TanggalKegiatan[:10])
	if err != nil {
		return o.TanggalKegiatan
	}
	return formatIndonesianDate(t)
}

func truncTime(s string) string {
	if len(s) >= 5 {
		return s[:5]
	}
	return s
}

// ==================== PORTAL ====================

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

// ==================== ADMIN ====================

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

// ==================== HELPERS ====================

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
