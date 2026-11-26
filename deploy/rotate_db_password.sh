#!/bin/bash
# ==============================================================================
# Midhub360: give the application's MySQL user a new random password and store it in .env.
# Needed once because the old password was committed to GitHub (and served by bkash2.html).
#
#   cd /var/www/midhub360.com && sudo bash deploy/rotate_db_password.sh
#
# Works when MySQL/MariaDB runs on this server (root access through "sudo mysql").
# Every host entry of the user is changed (e.g. 'midhubDBuser'@'localhost' and @'%'), the
# connection is tested, and on failure the old password is put back.
# If MySQL runs on another machine, the script prints the SQL to run there instead.
# ==============================================================================
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/midhub360.com}"
cd "$APP_DIR"
[ "$(id -u)" = "0" ] || { echo "run as root: sudo bash deploy/rotate_db_password.sh" >&2; exit 1; }
PHP_BIN="$(command -v php7.4 || command -v php)"

envget() {
    grep -E "^[[:space:]]*$1[[:space:]]*=" .env | tail -n1 | cut -d= -f2- \
        | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/" || true
}
envset() {
    local tmp; tmp="$(mktemp)"
    grep -vE "^[[:space:]]*$1[[:space:]]*=" .env > "$tmp" || true
    printf '%s=%s\n' "$1" "$2" >> "$tmp"
    cat "$tmp" > .env; rm -f "$tmp"
    chown www-data:www-data .env; chmod 600 .env
}
app_connects() {
    sudo -u www-data "$PHP_BIN" -r 'require "config/env.php"; midhub_get_db_connection(); echo "OK";' 2>/dev/null | grep -q '^OK$'
}

[ -f .env ] || { echo "No .env yet: run deploy/update_live.sh first." >&2; exit 1; }
DB_USER="$(envget DB_USER)"; DB_USER="${DB_USER:-midhubDBuser}"
OLD_PASS="$(envget DB_PASS)"
# Letters and digits only: no quoting problems in .env, SQL or shell.
NEW_PASS="$(head -c 96 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 32 || true)"
[ ${#NEW_PASS} -eq 32 ] || { echo "could not generate a password" >&2; exit 1; }

app_connects || { echo "The app cannot connect with the current .env password; fix that first (deploy/update_live.sh)." >&2; exit 1; }

if ! mysql -N -e 'SELECT 1' >/dev/null 2>&1; then
    cat <<EOF
MySQL is not reachable as root on this server (it probably runs on the database machine).
Run this on the DATABASE server as the MySQL root user, for every host listed:

    SELECT user, host FROM mysql.user WHERE user = '$DB_USER';
    ALTER USER '$DB_USER'@'<host>' IDENTIFIED BY '<new password>';

then put the new password in $APP_DIR/.env  (DB_PASS=...)  and check the site.
Also allow port 3306 only from this web server (firewall), not from the whole internet.
EOF
    exit 1
fi

mapfile -t HOSTS < <(mysql -N -e "SELECT host FROM mysql.user WHERE user = '$DB_USER'")
[ ${#HOSTS[@]} -gt 0 ] || { echo "MySQL user '$DB_USER' not found" >&2; exit 1; }
echo "Changing the password of '$DB_USER' for host(s): ${HOSTS[*]}"

cp -p .env ".env.before-rotate"
for h in "${HOSTS[@]}"; do
    mysql -e "ALTER USER '$DB_USER'@'$h' IDENTIFIED BY '$NEW_PASS'"
done
mysql -e "FLUSH PRIVILEGES"
envset DB_PASS "$NEW_PASS"

if app_connects; then
    rm -f ".env.before-rotate"
    echo "OK: new password set in MySQL and in .env; the site connects."
    echo "Remote access check, user/host entries now:"
    mysql -e "SELECT user, host FROM mysql.user WHERE user = '$DB_USER'"
    echo "If a host is '%', MySQL accepts this user from anywhere: restrict port 3306 in the firewall."
else
    echo "The app could not connect with the new password: restoring the old one." >&2
    for h in "${HOSTS[@]}"; do
        mysql -e "ALTER USER '$DB_USER'@'$h' IDENTIFIED BY '$(printf '%s' "$OLD_PASS" | sed "s/'/''/g")'"
    done
    mysql -e "FLUSH PRIVILEGES"
    cat ".env.before-rotate" > .env; rm -f ".env.before-rotate"
    exit 1
fi
