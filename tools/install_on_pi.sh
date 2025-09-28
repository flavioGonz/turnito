#!/usr/bin/env bash
# tools/install_on_pi.sh
# Minimal installer for deploying the Turnero GPIO monitor to a Raspberry Pi.
# Usage (on the Pi):
#   sudo bash tools/install_on_pi.sh
# Optional overrides (environment variables):
#   WEBROOT=/var/www/html/turnero SERVICE_USER=turnero SERVICE_NAME=turnero-gpio-monitor

set -euo pipefail
IFS=$'\n\t'

WEBROOT_DEFAULT="/var/www/html/turnero"
WEBROOT="${WEBROOT:-$WEBROOT_DEFAULT}"
SERVICE_NAME="${SERVICE_NAME:-turnero-gpio-monitor}"
SERVICE_SRC="${WEBROOT}/tools/gpio_monitor.service"
SERVICE_DEST="/etc/systemd/system/${SERVICE_NAME}.service"
SERVICE_USER="${SERVICE_USER:-turnero}"

if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root (sudo)." >&2
  echo "Usage: sudo bash tools/install_on_pi.sh" >&2
  exit 1
fi

echo "Deploying Turnero GPIO monitor"
echo "WEBROOT = $WEBROOT"
echo "SERVICE_NAME = $SERVICE_NAME"

# 1) Install packages
echo "\n[1/6] Installing packages (python3, rpi.gpio, curl)"
apt update
apt install -y python3 python3-venv python3-pip python3-rpi.gpio curl || true

# 2) Create service user (system user) if not exists
if id -u "$SERVICE_USER" >/dev/null 2>&1; then
  echo "User $SERVICE_USER already exists"
else
  echo "Creating system user $SERVICE_USER"
  adduser --system --group --no-create-home "$SERVICE_USER" || true
fi

# 3) Add service user to gpio group
if getent group gpio >/dev/null 2>&1; then
  usermod -aG gpio "$SERVICE_USER" || true
  echo "Added $SERVICE_USER to gpio group"
else
  echo "No gpio group found; skipping group membership step"
fi

# 4) Verify WEBROOT exists
if [ ! -d "$WEBROOT" ]; then
  echo "ERROR: WEBROOT $WEBROOT does not exist. Copy your project to the Pi first." >&2
  exit 2
fi

# 5) Determine web user for ownership (www-data if present, otherwise pi)
WEBUSER="pi"
if id -u www-data >/dev/null 2>&1; then
  WEBUSER="www-data"
fi

echo "Setting ownership of $WEBROOT to $WEBUSER"
chown -R "$WEBUSER":"$WEBUSER" "$WEBROOT" || true

# 6) Install systemd unit
if [ ! -f "$SERVICE_SRC" ]; then
  echo "ERROR: Service template $SERVICE_SRC not found. Expected to exist under project WEBROOT/tools" >&2
  exit 3
fi

# Replace occurrences of the default path in the service template with actual WEBROOT
sed "s|/var/www/html/turnero|$WEBROOT|g" "$SERVICE_SRC" > "$SERVICE_DEST"
chmod 644 "$SERVICE_DEST"

# Reload systemd and start service
systemctl daemon-reload
systemctl enable --now "$SERVICE_NAME"

# Show status summary
echo "\nService installed and started. Status (last 10 lines):"
journalctl -u "$SERVICE_NAME" -n 20 --no-pager || true

# Final notes
cat <<EOF

INSTALL COMPLETE
- Service: $SERVICE_NAME
- Web root: $WEBROOT
- To follow logs: sudo journalctl -u $SERVICE_NAME -f
- To check events UI: http://<pi-ip>/$(echo $WEBROOT | sed 's|/var/www/html/||')/tools/gpio_monitor.php

If you see permission errors accessing GPIO, verify the service user is in the gpio group and reboot or re-login:
  sudo usermod -aG gpio $SERVICE_USER
  sudo reboot

EOF
