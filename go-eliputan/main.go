package main

import (
	"log"
	"net/http"
	"os"

	"eliputan/internal/db"
	"eliputan/internal/handler"
	"eliputan/internal/middleware"
)

func main() {
	port := envOr("PORT", "8000")

	database := db.Open(envOr("DB_PATH", "eliputan.db"))
	defer database.Close()
	db.Migrate(database)
	db.Seed(database)

	h := handler.New(database)

	mux := http.NewServeMux()

	// Uploaded files
	os.MkdirAll("uploads", 0755)
	mux.Handle("GET /uploads/", http.StripPrefix("/uploads/", http.FileServer(http.Dir("uploads"))))

	// JSON API for the Next.js frontend
	mux.HandleFunc("GET /api/portal", h.APIPortal)
	mux.HandleFunc("POST /api/orders", h.APIOrderCreate)
	mux.HandleFunc("POST /api/auth/login", h.APILogin)
	mux.HandleFunc("POST /api/auth/logout", h.APILogout)
	mux.HandleFunc("GET /api/auth/me", h.APIMe)
	mux.HandleFunc("GET /api/events", h.PortalEvents)
	mux.HandleFunc("GET /api/admin/orders", h.APIAdminOrders)
	mux.HandleFunc("GET /api/admin/orders/{id}", h.AdminOrderShow)
	mux.HandleFunc("POST /api/admin/orders/{id}/status", h.APIAdminOrderStatus)
	mux.HandleFunc("POST /api/admin/orders/{id}/delete", h.APIAdminOrderDelete)

	wrapped := middleware.Logger(
		middleware.Session(database,
			middleware.CSRF(
				middleware.SecurityHeaders(mux),
			),
		),
	)

	log.Printf("E-Liputan DPRD running on http://localhost:%s", port)
	log.Fatal(http.ListenAndServe(":"+port, wrapped))
}

func envOr(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func init() {
	data, err := os.ReadFile(".env")
	if err != nil {
		return
	}
	for _, line := range splitLines(string(data)) {
		if len(line) == 0 || line[0] == '#' {
			continue
		}
		for i := 0; i < len(line); i++ {
			if line[i] == '=' {
				key := line[:i]
				val := line[i+1:]
				if os.Getenv(key) == "" {
					os.Setenv(key, val)
				}
				break
			}
		}
	}
}

func splitLines(s string) []string {
	var lines []string
	start := 0
	for i := 0; i < len(s); i++ {
		if s[i] == '\n' {
			line := s[start:i]
			if len(line) > 0 && line[len(line)-1] == '\r' {
				line = line[:len(line)-1]
			}
			lines = append(lines, line)
			start = i + 1
		}
	}
	if start < len(s) {
		lines = append(lines, s[start:])
	}
	return lines
}
