#!/bin/bash
# ============================================================
# E-LIPUTAN DPRD — Auto Deploy Script untuk aaPanel (Ubuntu)
# Domain: liputan.172.235.246.16.nip.io
# ============================================================
# Jalankan di VPS sebagai root:
#   bash deploy.sh
# ============================================================

set -e

DOMAIN="liputan.172.235.246.16.nip.io"
APP_DIR="/www/wwwroot/${DOMAIN}"
SERVICE_NAME="eliputan"
REPO="https://github.com/Putra45/e-liputan-humas-dprd.git"
GO_VERSION="1.23.6"
PORT=8080

echo "=========================================="
echo " E-Liputan DPRD — Deploy ke aaPanel VPS"
echo "=========================================="

# 1. Install Go jika belum ada
if ! command -v go &>/dev/null; then
    echo "[1/7] Installing Go ${GO_VERSION}..."
    cd /tmp
    wget -q "https://go.dev/dl/go${GO_VERSION}.linux-amd64.tar.gz"
    rm -rf /usr/local/go
    tar -C /usr/local -xzf "go${GO_VERSION}.linux-amd64.tar.gz"
    rm -f "go${GO_VERSION}.linux-amd64.tar.gz"

    # Add to PATH permanently
    if ! grep -q '/usr/local/go/bin' /etc/profile; then
        echo 'export PATH=$PATH:/usr/local/go/bin' >> /etc/profile
    fi
    export PATH=$PATH:/usr/local/go/bin
    echo "  Go $(go version) installed."
else
    echo "[1/7] Go already installed: $(go version)"
fi

export PATH=$PATH:/usr/local/go/bin

# 2. Clone atau pull repo
echo "[2/7] Syncing from GitHub..."
if [ -d "${APP_DIR}/.git" ]; then
    cd "${APP_DIR}"
    git fetch origin main
    git reset --hard origin/main
else
    rm -rf "${APP_DIR}"
    git clone "${REPO}" "${APP_DIR}"
fi
cd "${APP_DIR}/go-eliputan"

# 3. Setup .env jika belum ada
echo "[3/7] Checking .env config..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  .env created from .env.example"
    echo "  PENTING: Edit .env untuk set WA_GATEWAY_TOKEN jika diperlukan"
fi

# 4. Build binary Go
echo "[4/7] Building Go binary..."
CGO_ENABLED=0 go build -ldflags="-s -w" -o /usr/local/bin/eliputan .
chmod +x /usr/local/bin/eliputan
echo "  Binary built: $(ls -lh /usr/local/bin/eliputan | awk '{print $5}')"

# 5. Create systemd service
echo "[5/7] Creating systemd service..."
cat > /etc/systemd/system/${SERVICE_NAME}.service << 'SERVICEEOF'
[Unit]
Description=E-Liputan DPRD Humas (Go)
After=network.target
Wants=network-online.target

[Service]
Type=simple
User=www
Group=www
WorkingDirectory=/www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan
ExecStart=/usr/local/bin/eliputan
Restart=always
RestartSec=3
StandardOutput=journal
StandardError=journal
SyslogIdentifier=eliputan

# Security hardening
NoNewPrivileges=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan
PrivateTmp=true

# Environment
Environment=PORT=8080
Environment=DB_PATH=/www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan/eliputan.db

[Install]
WantedBy=multi-user.target
SERVICEEOF

# Ensure uploads dir exists and owned by www
mkdir -p "${APP_DIR}/go-eliputan/uploads"
chown -R www:www "${APP_DIR}/go-eliputan"

systemctl daemon-reload
systemctl enable ${SERVICE_NAME}
systemctl restart ${SERVICE_NAME}
sleep 2

if systemctl is-active --quiet ${SERVICE_NAME}; then
    echo "  ✅ Service ${SERVICE_NAME} running!"
else
    echo "  ❌ Service failed. Check: journalctl -u ${SERVICE_NAME} -n 20"
    exit 1
fi

# 6. Configure Nginx reverse proxy di aaPanel
echo "[6/7] Configuring Nginx reverse proxy..."

NGINX_CONF="/www/server/panel/vhost/nginx/${DOMAIN}.conf"

# Backup existing config
if [ -f "${NGINX_CONF}" ]; then
    cp "${NGINX_CONF}" "${NGINX_CONF}.bak.$(date +%s)"
fi

cat > "${NGINX_CONF}" << 'NGINXEOF'
server {
    listen 80;
    listen [::]:80;
    server_name liputan.172.235.246.16.nip.io;

    # aaPanel logs
    access_log /www/wwwlogs/liputan.172.235.246.16.nip.io.log;
    error_log /www/wwwlogs/liputan.172.235.246.16.nip.io.error.log;

    # Security headers handled by Go app — not duplicated here

    # Max upload 10MB
    client_max_body_size 10m;

    # Static files served by Nginx directly (faster)
    location /static/ {
        alias /www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan/static/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /uploads/ {
        alias /www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan/uploads/;
        expires 7d;
    }

    # Proxy to Go app
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_connect_timeout 10;
        proxy_read_timeout 30;
        proxy_send_timeout 30;

        # WebSocket support (optional)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
NGINXEOF

# Reload Nginx
/www/server/nginx/sbin/nginx -t && /www/server/nginx/sbin/nginx -s reload
echo "  ✅ Nginx configured and reloaded!"

# 7. Test
echo "[7/7] Testing..."
sleep 1
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8080/ 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    echo ""
    echo "=========================================="
    echo " ✅ DEPLOY BERHASIL!"
    echo "=========================================="
    echo ""
    echo " Website:  http://${DOMAIN}"
    echo " Admin:    http://${DOMAIN}/login"
    echo " Service:  systemctl status ${SERVICE_NAME}"
    echo " Logs:     journalctl -u ${SERVICE_NAME} -f"
    echo ""
    echo " ⚠️  PENTING:"
    echo " 1. Login ke aaPanel → Website → ${DOMAIN} → SSL"
    echo "    → Apply Let's Encrypt untuk HTTPS"
    echo " 2. Ganti password admin default setelah login!"
    echo ""
else
    echo ""
    echo " ⚠️  HTTP test returned ${HTTP_CODE}"
    echo " Check: journalctl -u ${SERVICE_NAME} -n 30"
    echo ""
fi
