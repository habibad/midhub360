#!/bin/bash
# ==============================================================================
# Midhub360 Server Setup & Configuration Script
# Configures Nginx for dual HTTP (Port 80) & HTTPS (Port 443), with security rules
# that keep secrets, source internals, DB dumps and docs off the public web.
#
# Optional, explicit maintenance step (never runs by default):
#   RESET_SUPERADMIN=1 sudo ./setup_server.sh   -> unblocks newyork24 and clears IP blocks
#                                                  (prompts for the DB password; does NOT
#                                                  reset the admin password any more)
# ==============================================================================

set -e

APP_DIR=/var/www/midhub360.com
PHP_SOCK=/var/run/php/php7.4-fpm.sock
# The site runs on PHP 7.4-FPM. On Ubuntu 24.04 a plain "php" may be PHP 8.3 without the
# mysqli extension, so cron and CLI tasks use php7.4 when it is installed.
PHP_BIN="$(command -v php7.4 || command -v php)"

echo "--------------------------------------------------------"
echo "  Starting Midhub360 Server Setup on $(hostname)"
echo "--------------------------------------------------------"

# 1. Permissions: code read-only for PHP, writable only where needed
echo "[1/6] Setting web root permissions..."
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod 755 "$APP_DIR/setup_server.sh"
mkdir -p "$APP_DIR/storage/keys"
chmod 700 "$APP_DIR/storage" "$APP_DIR/storage/keys"
[ -f "$APP_DIR/.env" ] && chmod 600 "$APP_DIR/.env"
[ -f "$APP_DIR/storage/keys/app.key" ] && chmod 600 "$APP_DIR/storage/keys/app.key"

# 2. Disable default conflicting Nginx site
echo "[2/6] Removing default conflicting Nginx configuration..."
rm -f /etc/nginx/sites-enabled/default

# 3. Detect SSL Certificates
SSL_CERT="/etc/letsencrypt/live/www.midhub360.com/fullchain.pem"
SSL_KEY="/etc/letsencrypt/live/www.midhub360.com/privkey.pem"

echo "[3/6] Generating Nginx configuration (/etc/nginx/sites-available/midhub360.com)..."

# Rate-limit zones (http level). Login/PIN pages are limited per IP; limits are generous
# because many Bangladeshi mobile users share carrier (CGNAT) IPs.
cat << 'EOF' > /etc/nginx/conf.d/midhub360-ratelimit.conf
limit_req_zone $binary_remote_addr zone=mh_login:10m rate=30r/m;
limit_req_zone $binary_remote_addr zone=mh_api:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=mh_general:20m rate=30r/s;
limit_req_status 429;
EOF

# PHP handler, shared by every PHP location.
cat << 'EOF' > /etc/nginx/snippets/midhub360-php.conf
try_files $uri =404;
include snippets/fastcgi-php.conf;
fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_param HTTP_PROXY "";
include fastcgi_params;
EOF

# Shared rules, included by both server blocks.
cat << 'EOF' > /etc/nginx/snippets/midhub360-common.conf
root /var/www/midhub360.com;
index index.php index.html index.htm;
client_max_body_size 16M;
server_tokens off;

add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
# Baseline CSP that the legacy inline scripts tolerate: no framing by other sites, no plugins,
# no <base> hijacking. (A strict script-src would break the legacy admin theme.)
add_header Content-Security-Policy "frame-ancestors 'self'; object-src 'none'; base-uri 'self'" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

# --- SECURITY: never serve internals --------------------------------------------
# ^~ prefix matches win over every regex location (including \.php$ below), so
# nothing inside these directories is ever executed or downloaded over HTTP.
location ^~ /lib/            { deny all; return 404; }
location ^~ /database/       { deny all; return 404; }
location ^~ /storage/        { deny all; return 404; }
location ^~ /cron/           { deny all; return 404; }
location ^~ /docs/           { deny all; return 404; }
location ^~ /tests/          { deny all; return 404; }
location ^~ /config/         { deny all; return 404; }
location ^~ /yk/             { deny all; return 404; }
location ^~ /admin/class/    { deny all; return 404; }
location ^~ /customer/class/ { deny all; return 404; }
location ^~ /apps/customer/class/ { deny all; return 404; }
location ^~ /payment/install/ { deny all; return 404; }

# Dotfiles (.env, .git, .htaccess, .user.ini) except ACME challenges.
# Regex locations match in order, so these MUST stay above the \.php$ block.
location ~ /\.(?!well-known/) { deny all; return 404; }
# DB dumps, docs, archives, scripts, backups
location ~* \.(sql|md|bak|old|orig|log|zip|tar|gz|tgz|rar|7z|sh|bat|ini|key|lock|dist|example)$ { deny all; return 404; }

location / {
    try_files $uri $uri/ /index.php?$args;
}

# --- Rate limits (brute force / abuse / DDoS) ------------------------------------
location = /login.php        { limit_req zone=mh_login burst=20 nodelay; include snippets/midhub360-php.conf; }
location = /admin/index.php  { limit_req zone=mh_login burst=20 nodelay; include snippets/midhub360-php.conf; }
location = /apps/index.php   { limit_req zone=mh_login burst=20 nodelay; include snippets/midhub360-php.conf; }
location = /customer/process_bill_payment.php { limit_req zone=mh_login burst=20 nodelay; include snippets/midhub360-php.conf; }
location ^~ /api/            { limit_req zone=mh_api burst=30 nodelay; location ~ \.php$ { include snippets/midhub360-php.conf; } }
location ^~ /apimobile/      { limit_req zone=mh_api burst=30 nodelay; location ~ \.php$ { include snippets/midhub360-php.conf; } }
location ^~ /gateway/        { limit_req zone=mh_api burst=50 nodelay; location ~ \.php$ { include snippets/midhub360-php.conf; } }

location ~ \.php$ {
    limit_req zone=mh_general burst=60 nodelay;
    include snippets/midhub360-php.conf;
}
EOF

cat << 'EOF' > /etc/nginx/sites-available/midhub360.com
# PORT 80 - HTTP
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name midhub360.com www.midhub360.com 124.6.224.116 _;
    include snippets/midhub360-common.conf;
}
EOF

if [ -f "$SSL_CERT" ] && [ -f "$SSL_KEY" ]; then
    echo "       SSL certificates found! Adding HTTPS port 443 block..."
    cat << EOF >> /etc/nginx/sites-available/midhub360.com

# PORT 443 - HTTPS
server {
    listen 443 ssl default_server;
    listen [::]:443 ssl default_server;
    server_name midhub360.com www.midhub360.com 124.6.224.116 _;
    ssl_certificate $SSL_CERT;
    ssl_certificate_key $SSL_KEY;
    ssl_protocols TLSv1.2 TLSv1.3;
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header Content-Security-Policy "frame-ancestors 'self'; object-src 'none'; base-uri 'self'; upgrade-insecure-requests" always;
    include snippets/midhub360-common.conf;
}
EOF
else
    echo "       [NOTE] SSL certificate not found at $SSL_CERT. Operating on HTTP port 80 only."
    echo "       [WARNING] Payment gateways (bKash/Nagad/Stripe) require HTTPS callback URLs in Live mode."
fi

# 4. Link & reload Nginx
echo "[4/6] Testing Nginx configuration and reloading..."
ln -sf /etc/nginx/sites-available/midhub360.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# 5. Background worker + nightly backup + Fail2ban + certificate renewal
echo "[5/6] Installing cron jobs (worker every minute, DB backup 02:30)..."
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

# 6. Optional super-admin unblock (explicit opt-in only)
if [ "${RESET_SUPERADMIN:-0}" = "1" ]; then
    echo "[6/6] Unblocking super admin 'newyork24' (enter the DB password when prompted)..."
    mysql -u midhubDBuser -p midhub-db << 'EOFSQL'
DELETE FROM user_blocklist WHERE username='newyork24';
UPDATE user SET active='1' WHERE user='newyork24';
EOFSQL
else
    echo "[6/6] Skipping super-admin unblock (set RESET_SUPERADMIN=1 to run it)."
fi

echo ""
echo "========================================================"
echo "  VERIFICATION TESTS"
echo "========================================================"
check() { printf "  %-40s expect %s got %s\n" "$1" "$2" "$(curl -k -s -o /dev/null -w '%{http_code}' "$1")"; }
check "http://127.0.0.1/admin/index.php" 200
check "http://127.0.0.1/.env" 404
check "http://127.0.0.1/.git/config" 404
check "http://127.0.0.1/aanonda_software_new.sql" 404
check "http://127.0.0.1/lib/bootstrap.php" 404
check "http://127.0.0.1/storage/keys/app.key" 404
check "http://127.0.0.1/debug_login.php" 404
check "http://127.0.0.1/CLAUDE_PROJECT_DEVELOPMENT_PROMPT.md" 404
check "http://127.0.0.1/bkash2.html" 404
echo ""
echo "Next: sudo -u www-data $PHP_BIN $APP_DIR/database/migrate.php  (deploy/update_live.sh does this for you)"
echo "========================================================"
