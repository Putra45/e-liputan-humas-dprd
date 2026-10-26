#!/bin/bash
# ============================================================
# E-Liputan DPRD — Auto Update Script
# Taruh di cron atau panggil manual untuk update dari GitHub
# ============================================================
# Usage:
#   bash update.sh
# Cron (setiap 5 menit cek update):
#   */5 * * * * /www/wwwroot/liputan.172.235.246.16.nip.io/go-eliputan/update.sh >> /var/log/eliputan-update.log 2>&1
# ============================================================

set -e

export PATH=$PATH:/usr/local/go/bin
APP_DIR="/www/wwwroot/liputan.172.235.246.16.nip.io"
SERVICE_NAME="eliputan"

cd "${APP_DIR}"

# Fetch latest
git fetch origin main --quiet

# Check if there are new commits
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" = "$REMOTE" ]; then
    exit 0
fi

echo "[$(date)] Updating: ${LOCAL:0:7} → ${REMOTE:0:7}"

# Pull changes
git reset --hard origin/main

# Rebuild
cd go-eliputan
CGO_ENABLED=0 go build -ldflags="-s -w" -o /usr/local/bin/eliputan .

# Restart service
systemctl restart ${SERVICE_NAME}

echo "[$(date)] Update complete. Service restarted."
