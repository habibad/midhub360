#!/bin/bash
# ==============================================================================
# Midhub360 Nginx site. Called by setup_server.sh (and so by deploy/update_live.sh);
# can also be run on its own:   sudo bash deploy/nginx_site.sh
#
# Makes the server IP and the domain serve Midhub360 instead of the "Welcome to nginx!" page:
#   - disables Nginx's stock welcome-page site and older configs for this same site
#     (moved to /etc/nginx/disabled-by-midhub360/, never deleted)
#   - writes the Midhub360 site (HTTP, plus HTTPS when a certificate is found) with its
#     security rules and rate limits
#   - checks the result with "nginx -t" and reloads Nginx. If the check fails, every file is
#     put back exactly as it was and the running site is not touched.
# ==============================================================================
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/midhub360.com}"
PUBLIC_IP="${PUBLIC_IP:-124.6.224.116}"
NGINX_DIR="${NGINX_DIR:-/etc/nginx}"
LETSENCRYPT_LIVE="${LETSENCRYPT_LIVE:-/etc/letsencrypt/live}"
PHP_SOCK="${PHP_SOCK:-/var/run/php/php7.4-fpm.sock}"
PHP_UPSTREAM="${PHP_UPSTREAM:-unix:$PHP_SOCK}"
DISABLED_DIR="$NGINX_DIR/disabled-by-midhub360"
STAMP="$(date +%Y%m%d-%H%M%S)"

note() { echo "       $*"; }
regex_quote() { printf '%s' "$1" | sed 's/[][\.*^$+?(){}|]/\\&/g'; }
# "listen ... <port> ... default_server" (any address form: 80, [::]:80, 0.0.0.0:80)
listen_default_re() { printf '^[[:space:]]*listen[[:space:]]+([^;[:space:]]*:)?%s([[:space:]]+[^;]*)?[[:space:]]default_server' "$1"; }

[ -f "$NGINX_DIR/nginx.conf" ] || { echo "       [ERROR] $NGINX_DIR/nginx.conf not found: is Nginx installed?" >&2; exit 1; }

# Ubuntu/Debian packages load sites-enabled/*; nginx.org packages load only conf.d/*.conf.
if awk '!/^[[:space:]]*#/ && /(^|[;{[:space:]])include[[:space:]]+[^;]*sites-enabled\//{f=1} END{exit !f}' "$NGINX_DIR/nginx.conf"; then
    SITE_FILE="$NGINX_DIR/sites-available/midhub360.com"
    SITE_LINK="$NGINX_DIR/sites-enabled/midhub360.com"
else
    SITE_FILE="$NGINX_DIR/conf.d/midhub360.com.conf"
    SITE_LINK=""
fi
RATELIMIT_FILE="$NGINX_DIR/conf.d/midhub360-ratelimit.conf"
PHP_SNIPPET="$NGINX_DIR/snippets/midhub360-php.conf"
COMMON_SNIPPET="$NGINX_DIR/snippets/midhub360-common.conf"
mkdir -p "$NGINX_DIR/snippets" "$NGINX_DIR/conf.d" "$(dirname "$SITE_FILE")" "$DISABLED_DIR"
[ -z "$SITE_LINK" ] || mkdir -p "$(dirname "$SITE_LINK")"
[ -S "$PHP_SOCK" ] || note "[WARNING] PHP-FPM socket $PHP_SOCK not found (is php7.4-fpm running?)"

APP_ROOT_RE="^[[:space:]]*root[[:space:]]+\"?$(regex_quote "$APP_DIR")/?\"?[[:space:]]*;"
WELCOME_ROOT_RE='^[[:space:]]*root[[:space:]]+"?(/var/www/html|/usr/share/nginx/html)/?"?[[:space:]]*;'
NAME_RE="server_name[^;]*($(regex_quote "$PUBLIC_IP")|midhub360\\.com)"

# ---------------------------------------------------------------- SSL certificate
SSL_CERT=""
SSL_KEY=""
# Uses the certificate an existing config file points at (if both files exist).
cert_from_file() {
    [ -z "$SSL_CERT" ] && [ -f "$1" ] || return 0
    local c k
    c="$(awk '$1=="ssl_certificate"{sub(/;.*/,"",$2); gsub(/"/,"",$2); print $2; exit}' "$1")"
    k="$(awk '$1=="ssl_certificate_key"{sub(/;.*/,"",$2); gsub(/"/,"",$2); print $2; exit}' "$1")"
    if [ -n "$c" ] && [ -n "$k" ] && [ -f "$c" ] && [ -f "$k" ]; then
        SSL_CERT="$c"
        SSL_KEY="$k"
    fi
}
for d in "$LETSENCRYPT_LIVE/www.midhub360.com" "$LETSENCRYPT_LIVE/midhub360.com"; do
    if [ -f "$d/fullchain.pem" ] && [ -f "$d/privkey.pem" ]; then
        SSL_CERT="$d/fullchain.pem"
        SSL_KEY="$d/privkey.pem"
        break
    fi
done
# Otherwise keep the certificate this script used last time (it may have come from an older
# config that an earlier run disabled).
cert_from_file "$SITE_FILE"

# ---------------------------------------------------------------- what is enabled today
is_ours() {
    local real
    real="$(readlink -f "$1" 2>/dev/null || echo "$1")"
    [ "$real" = "$(readlink -f "$SITE_FILE" 2>/dev/null || echo "$SITE_FILE")" ] \
        || [ "$1" = "$SITE_LINK" ] || [ "$1" = "$RATELIMIT_FILE" ]
}
TO_DISABLE=()
KEEP=()
for f in "$NGINX_DIR"/sites-enabled/* "$NGINX_DIR"/conf.d/*.conf; do
    [ -e "$f" ] || [ -L "$f" ] || continue
    is_ours "$f" && continue
    real="$(readlink -f "$f" 2>/dev/null || echo "$f")"
    [ -f "$real" ] || { TO_DISABLE+=("$f"); continue; }   # broken symlink
    name="$(basename "$f")"
    if grep -Eq "$APP_ROOT_RE" "$real"; then
        TO_DISABLE+=("$f")                                  # older config of this same site
    elif { [ "$name" = default ] || [ "$name" = default.conf ]; } && grep -Eq "$WELCOME_ROOT_RE" "$real"; then
        TO_DISABLE+=("$f")                                  # "Welcome to nginx!" page
    else
        KEEP+=("$f")
        continue
    fi
    # Keep HTTPS working when the old config used a certificate somewhere else
    cert_from_file "$real"
done

# Another site that stays enabled may already be Nginx's default server on port 80/443: two
# default servers on one port is a fatal error, so Midhub360 then answers by name (IP + domain).
DEFAULT80=" default_server"
DEFAULT443=" default_server"
for f in ${KEEP[@]+"${KEEP[@]}"}; do
    real="$(readlink -f "$f" 2>/dev/null || echo "$f")"
    for port in 80 443; do
        if grep -Eq "$(listen_default_re "$port")" "$real"; then
            if [ "$port" = 80 ]; then DEFAULT80=""; else DEFAULT443=""; fi
            note "[NOTE] $f is Nginx's default site on port $port and is left alone; Midhub360 answers for $PUBLIC_IP and midhub360.com by name."
        fi
    done
    if grep -Eq "$NAME_RE" "$real"; then
        note "[WARNING] $f also claims $PUBLIC_IP or midhub360.com. If the welcome page persists, disable it:  sudo rm $f"
    fi
done

# ---------------------------------------------------------------- change (with undo)
BACKUP="$DISABLED_DIR/backup-$STAMP"
mkdir -p "$BACKUP"
UNDO=()
remember() { # back up a file this script is about to replace, or note that it is new
    if [ -e "$1" ] || [ -L "$1" ]; then
        local saved
        saved="$BACKUP/$(printf '%s' "$1" | tr '/' '_')"
        cp -P "$1" "$saved"
        UNDO+=("restore|$1|$saved")
    else
        UNDO+=("remove|$1|")
    fi
}
rollback() {
    local i kind path saved
    for (( i=${#UNDO[@]}-1; i>=0; i-- )); do
        IFS='|' read -r kind path saved <<< "${UNDO[$i]}"
        case "$kind" in
            restore) rm -f "$path"; cp -P "$saved" "$path" ;;
            remove)  rm -f "$path" ;;
            moved)   mv "$saved" "$path" ;;
        esac
    done
}

for f in ${TO_DISABLE[@]+"${TO_DISABLE[@]}"}; do
    dest="$DISABLED_DIR/$(printf '%s' "${f#"$NGINX_DIR"/}" | tr '/' '_').$STAMP"
    mv "$f" "$dest"
    UNDO+=("moved|$f|$dest")
    note "disabled $f  (kept as $dest)"
done

remember "$RATELIMIT_FILE"
# Rate-limit zones (http level). Login/PIN pages are limited per IP; limits are generous
# because many Bangladeshi mobile users share carrier (CGNAT) IPs.
cat << 'EOF' > "$RATELIMIT_FILE"
limit_req_zone $binary_remote_addr zone=mh_login:10m rate=30r/m;
limit_req_zone $binary_remote_addr zone=mh_api:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=mh_general:20m rate=30r/s;
limit_req_status 429;
EOF

remember "$PHP_SNIPPET"
{
cat << 'EOF'
# Midhub360 PHP handler (written by deploy/nginx_site.sh). Self-contained on purpose: Ubuntu's
# snippets/fastcgi-php.conf has its own try_files, and Nginx refuses a second try_files in the
# same location ("try_files directive is duplicate"), so including it made nginx -t fail.
fastcgi_split_path_info ^(.+?\.php)(/.*)$;
try_files $fastcgi_script_name =404;
set $path_info $fastcgi_path_info;
include fastcgi_params;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_param PATH_INFO $path_info;
fastcgi_param HTTP_PROXY "";
fastcgi_index index.php;
EOF
printf 'fastcgi_pass %s;\n' "$PHP_UPSTREAM"
} > "$PHP_SNIPPET"

remember "$COMMON_SNIPPET"
{
printf '# Midhub360 shared server rules (written by deploy/nginx_site.sh)\n'
printf 'root %s;\n' "$APP_DIR"
cat << 'EOF'
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
location ^~ /deploy/         { deny all; return 404; }
location ^~ /tools/          { deny all; return 404; }
location ^~ /yk/             { deny all; return 404; }
location ^~ /admin/class/    { deny all; return 404; }
location ^~ /customer/class/ { deny all; return 404; }
location ^~ /apps/customer/class/ { deny all; return 404; }
location ^~ /payment/install/ { deny all; return 404; }

# Dotfiles (.env, .git, .htaccess, .user.ini) except ACME challenges.
# Regex locations match in order, so these MUST stay above the \.php$ block.
location ~ /\.(?!well-known/) { deny all; return 404; }
# DB dumps, docs, archives, scripts, backups
location ~* \.(sql|md|bak|old|orig|log|zip|tar|gz|tgz|rar|7z|sh|bat|ini|key|lock|dist|example|patch)$ { deny all; return 404; }

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
} > "$COMMON_SNIPPET"

remember "$SITE_FILE"
{
cat << EOF
# Midhub360 site. Rewritten by deploy/nginx_site.sh on every update: change that script, not this file.
# PORT 80 - HTTP
server {
    listen 80$DEFAULT80;
    listen [::]:80$DEFAULT80;
    server_name midhub360.com www.midhub360.com $PUBLIC_IP _;
    include snippets/midhub360-common.conf;
}
EOF
if [ -n "$SSL_CERT" ]; then
cat << EOF

# PORT 443 - HTTPS
server {
    listen 443 ssl$DEFAULT443;
    listen [::]:443 ssl$DEFAULT443;
    server_name midhub360.com www.midhub360.com $PUBLIC_IP _;
    ssl_certificate $SSL_CERT;
    ssl_certificate_key $SSL_KEY;
    ssl_protocols TLSv1.2 TLSv1.3;
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header Content-Security-Policy "frame-ancestors 'self'; object-src 'none'; base-uri 'self'; upgrade-insecure-requests" always;
    include snippets/midhub360-common.conf;
}
EOF
fi
} > "$SITE_FILE"
if [ -n "$SSL_CERT" ]; then
    note "HTTPS: certificate $SSL_CERT"
else
    note "[NOTE] no SSL certificate found: HTTP (port 80) only."
    note "[WARNING] Payment gateways (bKash/Nagad/Stripe) require HTTPS callback URLs in Live mode."
fi

if [ -n "$SITE_LINK" ]; then
    remember "$SITE_LINK"
    ln -sfn "$SITE_FILE" "$SITE_LINK"
fi

# ---------------------------------------------------------------- test, then reload
if ! nginx -t; then
    rollback
    echo "       [ERROR] Nginx rejected the new configuration (message above)." >&2
    if nginx -t >/dev/null 2>&1; then
        echo "       Every file was restored; the site keeps running exactly as before." >&2
    else
        echo "       Files were restored, but the configuration was already invalid before this run." >&2
    fi
    echo "       Send the message above to the developer." >&2
    exit 1
fi
rm -rf "$BACKUP"
if systemctl reload nginx 2>/dev/null || systemctl restart nginx; then
    note "Nginx reloaded"
else
    echo "       [ERROR] the configuration is valid but Nginx did not (re)start:  sudo systemctl status nginx" >&2
    exit 1
fi
