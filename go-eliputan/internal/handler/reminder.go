package handler

import (
	"database/sql"
	"fmt"
	"log"
	"time"
)

func StartReminderWorker(database *sql.DB) {
	ticker := time.NewTicker(1 * time.Minute)
	go func() {
		// Jalankan sekali saat startup
		checkAndSendReminders(database)
		for range ticker.C {
			checkAndSendReminders(database)
		}
	}()
	log.Printf("[Auto Reminder] Background scheduler started (checks every 1m).")
}

func checkAndSendReminders(database *sql.DB) {
	rows, err := database.Query(`
		SELECT id, kode_booking, nama_pemohon, fraksi, no_whatsapp, nama_kegiatan, kategori, 
		       tanggal_kegiatan, waktu_mulai, waktu_disetujui, jenis_publikasi, reminder_hours
		FROM media_orders
		WHERE status = 'approved' AND reminder_sent = 0 AND reminder_hours > 0
	`)
	if err != nil {
		return
	}

	var targets []MediaOrder
	for rows.Next() {
		var o MediaOrder
		if err := rows.Scan(&o.ID, &o.KodeBooking, &o.NamaPemohon, &o.Fraksi, &o.NoWhatsapp, &o.NamaKegiatan, &o.Kategori, &o.TanggalKegiatan, &o.WaktuMulai, &o.WaktuDisetujui, &o.JenisPublikasi, &o.ReminderHours); err == nil {
			targets = append(targets, o)
		}
	}
	rows.Close() // Lepaskan koneksi database segera agar tidak deadlock!

	loc := time.Local
	now := time.Now()

	for _, o := range targets {
		timeStr := o.WaktuFinal()
		if len(timeStr) == 5 {
			timeStr += ":00"
		}
		targetDateStr := o.TanggalKegiatan
		if len(targetDateStr) > 10 {
			targetDateStr = targetDateStr[:10]
		}
		dtStr := fmt.Sprintf("%s %s", targetDateStr, timeStr)
		eventTime, err := time.ParseInLocation("2006-01-02 15:04:05", dtStr, loc)
		if err != nil {
			eventTime, err = time.ParseInLocation("2006-01-02 15:04", fmt.Sprintf("%s %s", targetDateStr, o.WaktuFinal()), loc)
			if err != nil {
				continue
			}
		}

		reminderTriggerTime := eventTime.Add(-time.Duration(o.ReminderHours) * time.Hour)

		if now.After(reminderTriggerTime) && now.Before(eventTime.Add(12*time.Hour)) {
			log.Printf("[Auto Reminder] Triggering reminder for booking %s (Event at %s, %dh reminder)", o.KodeBooking, dtStr, o.ReminderHours)
			msg := buildReminderWAMessage(o.KodeBooking, o.NamaPemohon, o.Fraksi, o.NamaKegiatan, o.Kategori, o.TanggalKegiatan, o.WaktuFinal(), o.ReminderHours)
			if sendWhatsAppGateway(o.NoWhatsapp, msg) {
				database.Exec("UPDATE media_orders SET reminder_sent = 1, updated_at = ? WHERE id = ?", time.Now(), o.ID)
				log.Printf("[Auto Reminder] Success sending reminder for booking %s to %s", o.KodeBooking, o.NoWhatsapp)
			}
		}
	}
}
