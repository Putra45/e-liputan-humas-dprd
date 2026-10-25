package middleware

import (
	"context"
	"crypto/rand"
	"database/sql"
	"encoding/hex"
	"log"
	"net/http"
	"strings"
	"time"
)

type contextKey string

const (
	UserIDKey    contextKey = "user_id"
	UserNameKey  contextKey = "user_name"
	SessionKey   contextKey = "session_token"
	CSRFTokenKey contextKey = "csrf_token"
)

func Logger(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		next.ServeHTTP(w, r)
		log.Printf("%s %s %s", r.Method, r.URL.Path, time.Since(start).Round(time.Millisecond))
	})
}

func SecurityHeaders(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("X-Content-Type-Options", "nosniff")
		w.Header().Set("X-Frame-Options", "DENY")
		w.Header().Set("X-XSS-Protection", "1; mode=block")
		w.Header().Set("Referrer-Policy", "strict-origin-when-cross-origin")
		w.Header().Set("Content-Security-Policy", "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'")
		w.Header().Set("Permissions-Policy", "camera=(), microphone=(), geolocation=()")
		next.ServeHTTP(w, r)
	})
}

func Session(db *sql.DB, next http.Handler) http.Handler {
	// Cleanup expired sessions periodically
	go func() {
		for {
			db.Exec("DELETE FROM sessions WHERE expires_at < ?", time.Now())
			db.Exec("DELETE FROM csrf_tokens WHERE expires_at < ?", time.Now())
			time.Sleep(15 * time.Minute)
		}
	}()

	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		cookie, err := r.Cookie("session")
		if err == nil && cookie.Value != "" {
			var userID int
			var userName string
			var expiresAt time.Time
			err := db.QueryRow(
				"SELECT s.user_id, u.name, s.expires_at FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.token = ?",
				cookie.Value,
			).Scan(&userID, &userName, &expiresAt)
			if err == nil && time.Now().Before(expiresAt) {
				ctx := context.WithValue(r.Context(), UserIDKey, userID)
				ctx = context.WithValue(ctx, UserNameKey, userName)
				ctx = context.WithValue(ctx, SessionKey, cookie.Value)
				r = r.WithContext(ctx)
			}
		}
		next.ServeHTTP(w, r)
	})
}

func CSRF(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == "POST" || r.Method == "PATCH" || r.Method == "DELETE" {
			token := r.FormValue("_csrf")
			if token == "" {
				token = r.Header.Get("X-CSRF-Token")
			}
			if token == "" {
				http.Error(w, "CSRF token missing", http.StatusForbidden)
				return
			}
			// Validate: we check against session - simple stateless HMAC approach
			// For simplicity, validate token exists and matches session
			sessToken, _ := r.Context().Value(SessionKey).(string)
			if sessToken == "" {
				// For non-authenticated POST (like portal.store), validate token from cookie
				cookie, err := r.Cookie("csrf_token")
				if err != nil || cookie.Value != token {
					http.Error(w, "CSRF validation failed", http.StatusForbidden)
					return
				}
			} else {
				cookie, err := r.Cookie("csrf_token")
				if err != nil || cookie.Value != token {
					http.Error(w, "CSRF validation failed", http.StatusForbidden)
					return
				}
			}
		}

		// Generate CSRF token if not set
		csrfCookie, err := r.Cookie("csrf_token")
		var csrfToken string
		if err != nil || csrfCookie.Value == "" {
			csrfToken = generateToken(32)
			http.SetCookie(w, &http.Cookie{
				Name:     "csrf_token",
				Value:    csrfToken,
				Path:     "/",
				HttpOnly: false, // JS needs to read it for AJAX
				SameSite: http.SameSiteStrictMode,
				Secure:   r.TLS != nil,
				MaxAge:   86400,
			})
		} else {
			csrfToken = csrfCookie.Value
		}

		ctx := context.WithValue(r.Context(), CSRFTokenKey, csrfToken)
		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

func RequireAuth(r *http.Request) (int, bool) {
	uid, ok := r.Context().Value(UserIDKey).(int)
	return uid, ok && uid > 0
}

func generateToken(n int) string {
	b := make([]byte, n)
	rand.Read(b)
	return hex.EncodeToString(b)
}

func GenerateSessionToken() string {
	return generateToken(32)
}

// Rate limiting for login
var loginAttempts = make(map[string][]time.Time)

func CheckLoginRate(ip string) bool {
	now := time.Now()
	// Clean old entries
	var recent []time.Time
	for _, t := range loginAttempts[ip] {
		if now.Sub(t) < 5*time.Minute {
			recent = append(recent, t)
		}
	}
	loginAttempts[ip] = recent
	return len(recent) < 10 // Max 10 attempts per 5 minutes
}

func RecordLoginAttempt(ip string) {
	loginAttempts[ip] = append(loginAttempts[ip], time.Now())
}

func GetClientIP(r *http.Request) string {
	if xff := r.Header.Get("X-Forwarded-For"); xff != "" {
		parts := strings.SplitN(xff, ",", 2)
		return strings.TrimSpace(parts[0])
	}
	// Strip port
	addr := r.RemoteAddr
	if idx := strings.LastIndex(addr, ":"); idx != -1 {
		return addr[:idx]
	}
	return addr
}
