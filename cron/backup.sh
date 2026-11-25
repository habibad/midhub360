#!/bin/bash
# ==============================================================================
# Midhub360 nightly database backup (installed by setup_server.sh at 02:30 daily)
#
#   - mysqldump --single-transaction (consistent, no table locks) -> gzip
#   - credentials from .env via a temporary 0600 defaults file (never on the command line)
#   - keeps BACKUP_KEEP_DAYS days locally (default 14) in BACKUP_DIR (default /var/backups/midhub360)
#   - optional off-site copy: set BACKUP_RCLONE_REMOTE in .env (e.g. "gdrive:midhub360-backups")
#     after configuring rclone (`rclone config`) for Google Drive / S3 / any cloud drive
# ==============================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$APP_DIR/.env"
envget() { [ -f "$ENV_FILE" ] && grep -E "^$1=" "$ENV_FILE" | tail -1 | cut -d= -f2- | sed -e 's/^["'"'"']//' -e 's/["'"'"']$//' || true; }

DB_HOST="$(envget DB_HOST)"; DB_HOST="${DB_HOST:-localhost}"
DB_PORT="$(envget DB_PORT)"; DB_PORT="${DB_PORT:-3306}"
DB_NAME="$(envget DB_NAME)"; DB_NAME="${DB_NAME:-midhub-db}"
DB_USER="$(envget DB_USER)"
DB_PASS="$(envget DB_PASS)"
BACKUP_DIR="$(envget BACKUP_DIR)"; BACKUP_DIR="${BACKUP_DIR:-/var/backups/midhub360}"
KEEP_DAYS="$(envget BACKUP_KEEP_DAYS)"; KEEP_DAYS="${KEEP_DAYS:-14}"
REMOTE="$(envget BACKUP_RCLONE_REMOTE)"

if [ -z "$DB_USER" ]; then
  echo "$(date -Is) backup: DB_USER missing in $ENV_FILE" >&2
  exit 1
fi

umask 077
mkdir -p "$BACKUP_DIR"
CNF="$(mktemp)"
trap 'rm -f "$CNF"' EXIT
printf '[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n' "$DB_HOST" "$DB_PORT" "$DB_USER" "$DB_PASS" > "$CNF"

STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="$BACKUP_DIR/midhub-db-$STAMP.sql.gz"
mysqldump --defaults-extra-file="$CNF" --single-transaction --quick --routines --triggers --no-tablespaces "$DB_NAME" | gzip -9 > "$OUT"
# a truncated dump is worse than none: verify the gzip and the dump footer
gzip -t "$OUT"
zcat "$OUT" | tail -1 | grep -q "Dump completed" || { echo "$(date -Is) backup: dump incomplete, removing $OUT" >&2; rm -f "$OUT"; exit 1; }

# The encryption key file (if APP_KEY is not in .env) must be backed up with the data.
if [ -f "$APP_DIR/storage/keys/app.key" ]; then
  cp "$APP_DIR/storage/keys/app.key" "$BACKUP_DIR/app.key"
fi

find "$BACKUP_DIR" -name 'midhub-db-*.sql.gz' -mtime +"$KEEP_DAYS" -delete

if [ -n "$REMOTE" ] && command -v rclone >/dev/null 2>&1; then
  rclone copy "$OUT" "$REMOTE" --quiet && echo "$(date -Is) backup: uploaded to $REMOTE"
fi
echo "$(date -Is) backup: OK $OUT ($(du -h "$OUT" | cut -f1))"
