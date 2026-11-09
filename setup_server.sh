#!/bin/bash
# ==============================================================================
# Midhub360 Server Setup & Configuration Script
# Configures Nginx for dual HTTP (Port 80) & HTTPS (Port 443)
# Fixes permissions, unblocks Super Admin in DB, and reloads services
# ==============================================================================

set -e

echo "--------------------------------------------------------"
echo "  Starting Midhub360 Server Setup on $(hostname)"
echo "--------------------------------------------------------"

# 1. Fix Permissions
echo "[1/5] Setting web root permissions..."
chown -R www-data:www-data /var/www/midhub360.com
chmod -R 755 /var/www/midhub360.com
chmod -R 777 /tmp

# 2. Disable default conflicting Nginx site
echo "[2/5] Removing default conflicting Nginx configuration..."
rm -f /etc/nginx/sites-enabled/default

# 3. Detect SSL Certificates
SSL_CERT="/etc/letsencrypt/live/www.midhub360.com/fullchain.pem"
SSL_KEY="/etc/letsencrypt/live/www.midhub360.com/privkey.pem"

echo "[3/5] Generating Nginx configuration (/etc/nginx/sites-available/midhub360.com)..."

cat << 'EOF' > /etc/nginx/sites-available/midhub360.com
# ------------------------------------------------------------------------------
# PORT 80 - HTTP (Works with or without SSL, direct IP or domain, NO redirect)
# ------------------------------------------------------------------------------
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name midhub360.com www.midhub360.com 124.6.224.116 _;

    root /var/www/midhub360.com;
    index index.php index.html index.htm;

    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Append SSL block if certificate files exist
if [ -f "$SSL_CERT" ] && [ -f "$SSL_KEY" ]; then
    echo "       SSL certificates found! Adding HTTPS port 443 block..."
    cat << EOF >> /etc/nginx/sites-available/midhub360.com

# ------------------------------------------------------------------------------
# PORT 443 - HTTPS (SSL Enabled)
# ------------------------------------------------------------------------------
server {
    listen 443 ssl default_server;
    listen [::]:443 ssl default_server;

    server_name midhub360.com www.midhub360.com 124.6.224.116 _;

    root /var/www/midhub360.com;
    index index.php index.html index.htm;

    client_max_body_size 64M;

    ssl_certificate $SSL_CERT;
    ssl_certificate_key $SSL_KEY;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF
else
    echo "       [NOTE] SSL certificate not found at $SSL_CERT. Operating on HTTP port 80 only."
fi

# 4. Link & reload Nginx
echo "[4/5] Testing Nginx configuration and reloading..."
ln -sf /etc/nginx/sites-available/midhub360.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# 5. Database unblock and reset
echo "[5/5] Ensuring user 'newyork24' is active and unblocked in MySQL..."
mysql -u midhubDBuser -p'2345Midh@b#4$2025' midhub-db << 'EOFSQL'
DELETE FROM user_blocklist WHERE username='newyork24';
TRUNCATE TABLE ip_block;
DELETE FROM login_logs WHERE username='newyork24';
UPDATE user SET active='1', pass='e10adc3949ba59abbe56e057f20f883e' WHERE user='newyork24';
EOFSQL

echo ""
echo "========================================================"
echo "  VERIFICATION TESTS"
echo "========================================================"
echo -n "Test 1 - HTTP (Port 80) Admin Login:  "
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1/admin/index.php)
echo "HTTP Status $HTTP_STATUS"

if [ -f "$SSL_CERT" ]; then
    echo -n "Test 2 - HTTPS (Port 443) Admin Login: "
    HTTPS_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://127.0.0.1/admin/index.php)
    echo "HTTP Status $HTTPS_STATUS"
fi

echo ""
echo "=== ALL DONE! You can now access: ==="
echo "  1) HTTP  (No SSL): http://124.6.224.116/admin/index.php"
echo "  2) HTTPS (SSL)   : https://124.6.224.116/admin/index.php"
echo "  Username: newyork24 | Password: 123456"
echo "========================================================"
