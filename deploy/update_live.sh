#!/bin/bash
# ==============================================================================
# Midhub360: update the LIVE server to the latest code on GitHub (branch main).
#
#   cd /var/www/midhub360.com && sudo bash deploy/update_live.sh
#
# Steps (stops at the first problem; a database backup is taken before anything changes):
#   1. .env       completes the server's .env (DB password, APP_KEY). Secrets live ONLY here.
#   2. DB check   connects with the .env credentials.
#   3. Backup     full database dump to /var/backups/midhub360/.
#   4. Code       saves any edits made directly on the server, then moves to origin/main.
#                 Uses "git reset --hard": the GitHub history was rewritten on 2026-11-26 to
#                 purge leaked secrets, so a plain "git pull" would fail or re-merge old history.
#   5. Database   applies pending migrations (database/migrate.php).
#   6. Server     Nginx rules, permissions, cron worker (setup_server.sh), PHP-FPM reload.
#
# Safe to run again at any time (every step is idempotent).
# ==============================================================================
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/midhub360.com}"
BRANCH="${BRANCH:-main}"
BACKUP_DIR=/var/backups/midhub360
STAMP="$(date +%Y%m%d-%H%M%S)"

say()  { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '    \033[32mOK\033[0m  %s\n' "$*"; }
warn() { printf '    \033[33mNOTE\033[0m %s\n' "$*"; }
stop() { printf '\n\033[1;31mSTOPPED:\033[0m %s\n' "$*" >&2; exit 1; }

# Value of KEY in .env (last occurrence), surrounding quotes removed.
envget() {
    [ -f "$APP_DIR/.env" ] || return 0
    grep -E "^[[:space:]]*$1[[:space:]]*=" "$APP_DIR/.env" | tail -n1 | cut -d= -f2- \
        | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/" || true
}
# Sets KEY=VALUE in .env (replaces an existing line, keeps everything else).
envset() {
    local key="$1" val="$2" tmp
    tmp="$(mktemp)"
    if [ -f "$APP_DIR/.env" ]; then grep -vE "^[[:space:]]*$key[[:space:]]*=" "$APP_DIR/.env" > "$tmp" || true; fi
    printf '%s=%s\n' "$key" "$val" >> "$tmp"
    cat "$tmp" > "$APP_DIR/.env"
    rm -f "$tmp"
}

main() {
    [ "$(id -u)" = "0" ] || stop "run as root:  sudo bash deploy/update_live.sh"
    cd "$APP_DIR" || stop "$APP_DIR not found"
    PHP_BIN="$(command -v php7.4 || command -v php || true)"
    [ -n "$PHP_BIN" ] || stop "PHP CLI not found:  sudo apt install -y php7.4-cli php7.4-mysql"
    "$PHP_BIN" -m | grep -qi '^mysqli$' || stop "$PHP_BIN has no mysqli:  sudo apt install -y php7.4-mysql"
    echo "Midhub360 live update  |  $APP_DIR  |  $("$PHP_BIN" -r 'echo PHP_VERSION;')  |  $STAMP"

    # ------------------------------------------------------------------ 1. .env
    say "1/6  Server .env (passwords and keys are kept only in this file, never in git)"
    [ -f .env ] || { touch .env; ok "created .env"; }
    chmod 600 .env
    case "$(envget APP_ENV | tr 'A-Z' 'a-z')" in
        production) ;;
        "") envset APP_ENV production; ok "APP_ENV=production" ;;
        *) warn "APP_ENV was '$(envget APP_ENV)'; the live server must be production"; envset APP_ENV production ;;
    esac
    [ -n "$(envget DB_HOST)" ] || envset DB_HOST localhost
    [ -n "$(envget DB_NAME)" ] || envset DB_NAME midhub-db
    [ -n "$(envget DB_USER)" ] || envset DB_USER midhubDBuser
    if [ -z "$(envget DB_PASS)" ]; then
        echo "    The database password is no longer written in the code (it was public on GitHub)."
        read -r -s -p "    Type the current MySQL password of user '$(envget DB_USER)' (input hidden): " DBP; echo
        [ -n "$DBP" ] || stop "no password entered"
        envset DB_PASS "$DBP"
        unset DBP
        ok "DB_PASS saved in .env"
    else
        ok "DB_PASS present"
    fi
    if [ -z "$(envget APP_KEY)" ]; then
        if [ -s storage/keys/app.key ]; then
            ok "encryption key: existing storage/keys/app.key"
        else
            envset APP_KEY "base64:$("$PHP_BIN" -r 'echo base64_encode(random_bytes(32));')"
            warn "new APP_KEY written to .env. Copy that line into your password manager now:"
            warn "    sudo grep ^APP_KEY= $APP_DIR/.env"
            warn "Never change it later, or saved gateway credentials can no longer be decrypted."
        fi
    else
        ok "APP_KEY present"
    fi
    chown www-data:www-data .env
    chmod 600 .env

    # ------------------------------------------------------------------ 2. DB check
    say "2/6  Database connection"
    local out
    out="$(sudo -u www-data "$PHP_BIN" -r '
        require "config/env.php";
        $c = midhub_get_db_connection();
        $h = explode(" ", (string) $c->host_info);
        echo "OK ", strtolower($h[0]), " ", DB_NAME, "\n";' 2>&1)" || true
    case "$out" in
        OK\ *) DB_REAL_HOST="$(echo "$out" | awk '{print $2}')"; ok "connected to $(envget DB_NAME) on $DB_REAL_HOST" ;;
        *) stop "cannot connect with the .env credentials:
    $out
    Fix DB_PASS / DB_HOST in $APP_DIR/.env  (sudo nano $APP_DIR/.env), then run this script again." ;;
    esac

    # ------------------------------------------------------------------ 3. Backup
    say "3/6  Database backup"
    command -v mysqldump >/dev/null || stop "mysqldump not installed:  sudo apt install -y mysql-client  (or mariadb-client)"
    mkdir -p "$BACKUP_DIR"; chmod 700 "$BACKUP_DIR"
    DUMP="$BACKUP_DIR/before-update-$STAMP.sql.gz"
    local cnf
    cnf="$(mktemp)"; chmod 600 "$cnf"
    # credentials via a temporary 0600 file, never on the command line
    printf '[client]\nuser="%s"\npassword="%s"\nhost="%s"\nport="%s"\n' \
        "$(envget DB_USER)" "$(envget DB_PASS | sed -e 's/\\/\\\\/g' -e 's/"/\\"/g')" \
        "$DB_REAL_HOST" "$(envget DB_PORT | grep . || echo 3306)" > "$cnf"
    if mysqldump --defaults-extra-file="$cnf" --single-transaction --quick --routines --triggers "$(envget DB_NAME)" | gzip > "$DUMP"; then
        rm -f "$cnf"
        [ "$(stat -c %s "$DUMP")" -gt 10000 ] || stop "backup file is suspiciously small: $DUMP"
        chmod 600 "$DUMP"
        ok "$DUMP ($(du -h "$DUMP" | cut -f1))"
    else
        rm -f "$cnf" "$DUMP"
        stop "database backup failed (nothing was changed)"
    fi

    # ------------------------------------------------------------------ 4. Code
    say "4/6  Code: origin/$BRANCH from GitHub"
    [ -d .git ] || stop "$APP_DIR is not a git checkout. Clone it first:
    sudo git clone https://github.com/habibad/midhub360.git /var/www/midhub360.new
    then copy .env and uploads across (ask the developer)."
    git config --global --get-all safe.directory | grep -qx "$APP_DIR" || git config --global --add safe.directory "$APP_DIR"
    local changes old new
    changes="$(git status --porcelain --untracked-files=no)"
    if [ -n "$changes" ]; then
        local patch="$BACKUP_DIR/server-edits-$STAMP.patch"
        git diff HEAD > "$patch" || true
        chmod 600 "$patch"
        warn "files edited directly on the server were saved to $patch :"
        echo "$changes" | sed 's/^/          /'
    fi
    old="$(git rev-parse --short HEAD 2>/dev/null || echo none)"
    git fetch --prune origin "$BRANCH" || stop "git fetch failed (can this server reach GitHub / is it authorised?)"
    git reset --hard "origin/$BRANCH"
    new="$(git rev-parse --short HEAD)"
    ok "code $old -> $new ($(git log -1 --format=%s))"
    for f in CLAUDE_PROJECT_DEVELOPMENT_PROMPT.md CLAUDE_MISSION_VISION_ALIGNMENT_PROMPT.md aanonda_software_new.sql bkash2.html edit_bkash_send.html; do
        if [ -e "$f" ]; then rm -f "$f"; ok "removed leftover sensitive file $f"; fi
    done

    # ------------------------------------------------------------------ 5. Migrations
    say "5/6  Database migrations"
    sudo -u www-data "$PHP_BIN" database/migrate.php --status | sed 's/^/    /' || stop "could not read the migration status"
    sudo -u www-data "$PHP_BIN" database/migrate.php | sed 's/^/    /' \
        || stop "a migration failed; the ones after it were not applied. Backup: $DUMP
    Send the message above to the developer."
    ok "database schema is up to date"

    # ------------------------------------------------------------------ 6. Server
    say "6/6  Nginx rules, permissions, cron worker"
    bash setup_server.sh
    for svc in php7.4-fpm php-fpm; do
        if systemctl list-units --type=service --all 2>/dev/null | grep -q "$svc.service"; then
            systemctl reload "$svc" && ok "$svc reloaded" && break
        fi
    done

    say "Done"
    cat <<EOF
    Open:  https://124.6.224.116/admin/gateway_settings.php   and   /admin/voucher_products.php
    Backup of the database before this update: $DUMP

    Still to do (only once, see the developer's notes):
      1. sudo bash deploy/rotate_db_password.sh   (the old DB password was public on GitHub)
      2. Admin panel: change the super-admin password and Self-PIN (they were in a file on GitHub)
      3. Admin > Gateway Settings: enter the gateway keys again on this server (they are stored
         per database, so the ones typed on the local PC are not here)
EOF
}

main "$@"
