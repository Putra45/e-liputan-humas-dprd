#!/bin/bash
# ============================================================
# E-LIPUTAN DPRD — Auto Deploy Script untuk aaPanel (Ubuntu)
# Domain: liputan.172.235.246.16.nip.io
# Stack : Go API (8080) + Next.js (3000) di belakang Nginx
# ============================================================
# Jalankan di VPS sebagai root:
#   bash deploy.sh
# ============================================================

set -e

DOMAIN="liputan.172.235.246.16.nip.io"
APP_DIR="/www/wwwroot/${DOMAIN}"
SERVICE_NAME="eliputan"
WEB_SERVICE_NAME="eliputan-web"
REPO="https://github.com/Putra45/e-liputan-humas-dprd.git"
GO_VERSION="1.23.6"
NODE_MAJOR=22
PORT=8080
WEB_PORT=3000

echo "=========================================="
echo " E-Liputan DPRD — Deploy ke aaPanel VPS"
echo "=========================================="

# 1. Go
if ! command -v go &>/dev/null; then
    echo "[1/8] Installing Go ${GO_VERSION}..."
    cd /tmp
    wget -q "https://go.dev/dl/go${GO_VERSION}.linux-amd64.tar.gz"
    rm -rf /usr/local/go
    tar -C /usr/local -xzf "go${GO_VERSION}.linux-amd64.tar.gz"
    rm -f "go${GO_VERSION}.linux-amd64.tar.gz"
    grep -q '/usr/local/go/bin' /etc/profile || echo 'export PATH=$PATH:/usr/local/go/bin' >> /etc/profile
else
    echo "[1/8] Go already installed: $(go version)"
fi
export PATH=$PATH:/usr/local/go/bin

# 2. Node.js (Next.js 16 butuh Node >= 20)
NODE_BIN="$(command -v node || true)"
if [ -z "${NODE_BIN}" ] || [ "$(node -v | sed 's/v\([0-9]*\).*/\1/')" -lt 20 ]; then
    echo "[2/8] Installing Node.js ${NODE_MAJOR}.x..."
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash -
    apt-get install -y nodejs
else
    echo "[2/8] Node already installed: $(node -v)"
fi

# 3. Sync repo
echo "[3/8] Syncing from GitHub..."
if [ -d "${APP_DIR}/.git" ]; then
    cd "${APP_DIR}"
    git fetch origin main
    git reset --hard origin/main
else
    rm -rf "${APP_DIR}"
    git clone "${REPO}" "${APP_DIR}"
fi
cd "${APP_DIR}/go-eliputan"

# 4. .env Go
echo "[4/8] Checking .env config..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  .env created — isi WA_GATEWAY_TOKEN bila perlu"
fi

# 5. Build Go + Next.js
echo "[5/8] Building Go binary..."
CGO_ENABLED=0 go build -ldflags="-s -w" -o /usr/local/bin/eliputan .
chmod +x /usr/local/bin/eliputan

echo "  Building Next.js..."
cd "${APP_DIR}/frontend"
# Next 16 keeps prerendered HTML in .next/cache; without this the old page survives a rebuild.
rm -rf .next
npm ci
NEXT_PUBLIC_API_URL="http://127.0.0.1:${PORT}" npm run build

mkdir -p "${APP_DIR}/go-eliputan/uploads"
chown -R www:www "${APP_DIR}/go-eliputan" "${APP_DIR}/frontend"

# 6. systemd services
echo "[6/8] Writing systemd services..."
cat > /etc/systemd/system/${SERVICE_NAME}.service << SERVICEEOF
[Unit]
Description=E-Liputan DPRD Humas (Go API)
After=network.target
Wants=network-online.target

[Service]
Type=simple
User=www
Group=www
WorkingDirectory=${APP_DIR}/go-eliputan
ExecStart=/usr/local/bin/eliputan
Restart=always
RestartSec=3
StandardOutput=journal
StandardError=journal
SyslogIdentifier=eliputan
NoNewPrivileges=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=${APP_DIR}/go-eliputan
PrivateTmp=true
Environment=PORT=${PORT}
Environment=DB_PATH=${APP_DIR}/go-eliputan/eliputan.db

[Install]
WantedBy=multi-user.target
SERVICEEOF

cat > /etc/systemd/system/${WEB_SERVICE_NAME}.service << SERVICEEOF
[Unit]
Description=E-Liputan DPRD Humas (Next.js)
After=network.target ${SERVICE_NAME}.service
Wants=network-online.target

[Service]
Type=simple
User=www
Group=www
WorkingDirectory=${APP_DIR}/frontend
ExecStart=$(command -v node) node_modules/next/dist/bin/next start -p ${WEB_PORT}
Restart=always
RestartSec=3
StandardOutput=journal
StandardError=journal
SyslogIdentifier=eliputan-web
Environment=NODE_ENV=production
Environment=NEXT_PUBLIC_API_URL=http://127.0.0.1:${PORT}
Environment=HOME=${APP_DIR}/frontend

[Install]
WantedBy=multi-user.target
SERVICEEOF

systemctl daemon-reload
systemctl enable ${SERVICE_NAME} ${WEB_SERVICE_NAME}
systemctl restart ${SERVICE_NAME}
sleep 1
systemctl restart ${WEB_SERVICE_NAME}
sleep 2

for svc in ${SERVICE_NAME} ${WEB_SERVICE_NAME}; do
    if systemctl is-active --quiet $svc; then
        echo "  ✅ ${svc} running"
    else
        echo "  ❌ ${svc} failed. Check: journalctl -u ${svc} -n 30"
        exit 1
    fi
done

# 7. Nginx: Next.js di "/", Go di "/api/" dan file upload
echo "[7/8] Configuring Nginx reverse proxy..."
NGINX_CONF="/www/server/panel/vhost/nginx/${DOMAIN}.conf"
LOCATIONS_CONF="/www/server/panel/vhost/nginx/${DOMAIN}.locations.conf"
CERT_DIR="/www/server/panel/vhost/cert/${DOMAIN}"
[ -f "${NGINX_CONF}" ] && cp "${NGINX_CONF}" "${NGINX_CONF}.bak.$(date +%s)"

# Folder challenge untuk Let's Encrypt (HTTP-01) dan pembaruan otomatisnya.
mkdir -p "${APP_DIR}/acme/.well-known/acme-challenge"

cat > "${LOCATIONS_CONF}" << NGINXEOF
location /uploads/ {
    alias ${APP_DIR}/go-eliputan/uploads/;
    expires 7d;
}

location /.well-known/acme-challenge/ {
    root ${APP_DIR}/acme;
}

location /api/ {
    proxy_cache off;
    proxy_pass http://127.0.0.1:${PORT};
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto \$scheme;
    proxy_connect_timeout 10;
    proxy_read_timeout 30;
}

location / {
    proxy_cache off;
    proxy_no_cache 1;
    proxy_cache_bypass 1;
    proxy_pass http://127.0.0.1:${WEB_PORT};
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto \$scheme;
    proxy_connect_timeout 10;
    proxy_read_timeout 30;
    proxy_http_version 1.1;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
}
NGINXEOF

# Blok HTTPS hanya ditulis kalau sertifikatnya sudah ada, jadi deploy tidak
# menghapus SSL yang sudah terpasang.
SSL_SERVER=""
if [ -f "${CERT_DIR}/fullchain.pem" ] && [ -f "${CERT_DIR}/privkey.pem" ]; then
    SSL_SERVER="server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ${DOMAIN};

    access_log /www/wwwlogs/${DOMAIN}.log;
    error_log /www/wwwlogs/${DOMAIN}.error.log;

    ssl_certificate ${CERT_DIR}/fullchain.pem;
    ssl_certificate_key ${CERT_DIR}/privkey.pem;

    client_max_body_size 10m;

    include ${LOCATIONS_CONF};
}"
    echo "  ✅ Sertifikat SSL ditemukan, blok HTTPS ikut ditulis"
else
    echo "  ℹ️  Belum ada sertifikat SSL untuk ${DOMAIN} (HTTP saja)"
fi

cat > "${NGINX_CONF}" << NGINXEOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    access_log /www/wwwlogs/${DOMAIN}.log;
    error_log /www/wwwlogs/${DOMAIN}.error.log;

    client_max_body_size 10m;

    include ${LOCATIONS_CONF};
}

${SSL_SERVER}
NGINXEOF

if /www/server/nginx/sbin/nginx -t; then
    /www/server/nginx/sbin/nginx -s reload
    echo "  ✅ Nginx reloaded"
else
    echo "  ❌ Config Nginx tidak valid, reload dibatalkan (situs lama tetap jalan)"
fi

# 8. Test
echo "[8/8] Testing..."
sleep 1
API_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:${PORT}/api/portal")
WEB_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:${WEB_PORT}/")
echo "  Go API : ${API_CODE}"
echo "  Next.js: ${WEB_CODE}"

if [ "$API_CODE" = "200" ] && [ "$WEB_CODE" = "200" ]; then
    echo ""
    echo "=========================================="
    echo " ✅ DEPLOY BERHASIL!"
    echo "=========================================="
    echo " Website:  http://${DOMAIN}"
    echo " Admin:    http://${DOMAIN}/login"
    echo " Services: systemctl status ${SERVICE_NAME} ${WEB_SERVICE_NAME}"
    echo " Logs:     journalctl -u ${WEB_SERVICE_NAME} -f"
    echo ""
    echo " ⚠️  Catatan:"
    echo " 1. SSL otomatis dipertahankan selama sertifikat ada di"
    echo "    /www/server/panel/vhost/cert/${DOMAIN}/"
    echo " 2. Ganti password admin default setelah login!"
    echo ""
else
    echo " ⚠️ Ada service yang belum sehat. Cek: journalctl -u ${WEB_SERVICE_NAME} -n 30"
fi
