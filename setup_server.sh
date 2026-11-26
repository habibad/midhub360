#!/bin/bash
# ==============================================================================
# Midhub360 Server Setup & Configuration Script
# Permissions, Nginx (deploy/nginx_site.sh: HTTP port 80 + HTTPS 443, security rules that keep
# secrets, source internals, DB dumps and docs off the public web), cron worker, backups, Fail2ban.
#
# Optional, explicit maintenance step (never runs by default):
#   RESET_SUPERADMIN=1 sudo ./setup_server.sh   -> unblocks newyork24 and clears IP blocks
#                                                  (prompts for the DB password; does NOT
#                                                  reset the admin password any more)
# ==============================================================================

set -e

APP_DIR=/var/www/midhub360.com
PHP_SOCK=/var/run/php/php7.4-fpm.sock
PUBLIC_IP=124.6.224.116
# The site runs on PHP 7.4-FPM. On Ubuntu 24.04 a plain "php" may be PHP 8.3 without the
# mysqli extension, so cron and CLI tasks use php7.4 when it is installed.
PHP_BIN="$(command -v php7.4 || command -v php)"

echo "--------------------------------------------------------"
echo "  Starting Midhub360 Server Setup on $(hostname)"
echo "--------------------------------------------------------"

# 1. Permissions: code read-only for PHP, writable only where needed
echo "[1/4] Setting web root permissions..."
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod 755 "$APP_DIR/setup_server.sh"
mkdir -p "$APP_DIR/storage/keys"
chmod 700 "$APP_DIR/storage" "$APP_DIR/storage/keys"
[ -f "$APP_DIR/.env" ] && chmod 600 "$APP_DIR/.env"
[ -f "$APP_DIR/storage/keys/app.key" ] && chmod 600 "$APP_DIR/storage/keys/app.key"

# 2. Nginx: Midhub360 on the IP and the domain (no "Welcome to nginx!" page), HTTPS when a
#    certificate exists, security rules and rate limits. Undoes itself if nginx -t fails.
echo "[2/4] Nginx site for midhub360.com and $PUBLIC_IP..."
APP_DIR="$APP_DIR" PUBLIC_IP="$PUBLIC_IP" PHP_SOCK="$PHP_SOCK" bash "$APP_DIR/deploy/nginx_site.sh"

# 3. Background worker + nightly backup + Fail2ban + certificate renewal
echo "[3/4] Installing cron jobs (worker every minute, DB backup 02:30)..."
cat << EOF > /etc/cron.d/midhub360
* * * * * www-data $PHP_BIN $APP_DIR/cron/worker.php >> /var/log/midhub-worker.log 2>&1
30 2 * * * root /bin/bash $APP_DIR/cron/backup.sh >> /var/log/midhub-backup.log 2>&1
EOF
mkdir -p /var/backups/midhub360 && chmod 700 /var/backups/midhub360

if ! command -v fail2ban-client >/dev/null 2>&1; then
    apt-get install -y fail2ban >/dev/null
fi
cat << 'EOF' > /etc/fail2ban/jail.d/midhub360.conf
[sshd]
enabled  = true
maxretry = 5
findtime = 10m
bantime  = 1h

# IPs that keep hitting the login / API rate limits
[nginx-limit-req]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/error.log
maxretry = 10
findtime = 10m
bantime  = 30m
EOF
systemctl enable --now fail2ban >/dev/null 2>&1 && systemctl restart fail2ban

if command -v certbot >/dev/null 2>&1; then
    certbot renew --dry-run --quiet && echo "       Let's Encrypt auto-renewal: OK" || echo "       [WARNING] certbot renew --dry-run failed: check the certificate"
else
    echo "       [NOTE] certbot not installed: apt install certbot python3-certbot-nginx && certbot --nginx -d midhub360.com -d www.midhub360.com"
fi
chmod 644 /etc/cron.d/midhub360
touch /var/log/midhub-worker.log && chown www-data:www-data /var/log/midhub-worker.log

# 4. Optional super-admin unblock (explicit opt-in only)
if [ "${RESET_SUPERADMIN:-0}" = "1" ]; then
    echo "[4/4] Unblocking super admin 'newyork24' (enter the DB password when prompted)..."
    mysql -u midhubDBuser -p midhub-db << 'EOFSQL'
DELETE FROM user_blocklist WHERE username='newyork24';
UPDATE user SET active='1' WHERE user='newyork24';
EOFSQL
else
    echo "[4/4] Skipping super-admin unblock (set RESET_SUPERADMIN=1 to run it)."
fi

echo ""
echo "========================================================"
echo "  VERIFICATION TESTS"
echo "========================================================"
# Requests go to 127.0.0.1 with the public IP as Host, exactly as a browser opening
# http://$PUBLIC_IP/ is routed by Nginx.
check() { printf "  %-40s expect %s got %s\n" "$1" "$2" "$(curl -k -s -o /dev/null -w '%{http_code}' -H "Host: $PUBLIC_IP" "http://127.0.0.1$1")"; }
check "/" 200
check "/admin/index.php" 200
check "/.env" 404
check "/.git/config" 404
check "/aanonda_software_new.sql" 404
check "/lib/bootstrap.php" 404
check "/storage/keys/app.key" 404
check "/deploy/update_live.sh" 404
check "/debug_login.php" 404
check "/CLAUDE_PROJECT_DEVELOPMENT_PROMPT.md" 404
check "/bkash2.html" 404
if curl -s -H "Host: $PUBLIC_IP" "http://127.0.0.1/" | grep -q 'Welcome to nginx'; then
    echo ""
    echo "  [FAIL] http://$PUBLIC_IP/ still shows the 'Welcome to nginx!' page. Send this to the developer:"
    echo "         sudo nginx -T 2>/dev/null | grep -nE 'server_name|listen|root|# configuration file'"
fi
echo ""
echo "Next: sudo -u www-data $PHP_BIN $APP_DIR/database/migrate.php  (deploy/update_live.sh does this for you)"
echo "========================================================"
