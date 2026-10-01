#!/usr/bin/env bash
# Nightly backup of the MySQL database and private/public uploads.
# Usage: deploy/backup.sh [/path/to/app]
# Reads DB_* and BACKUP_* values from the application's .env file.
# Set BACKUP_GPG_RECIPIENT to encrypt archives with that public key.
set -euo pipefail

APP_DIR="${1:-/var/www/urban-haven}"
cd "$APP_DIR"

env_value() {
    local value
    value="$(command grep -E "^$1=" .env | tail -n 1 | cut -d '=' -f 2- || true)"
    value="${value%\"}"
    value="${value#\"}"
    printf '%s' "${value:-$2}"
}

DB_HOST="$(env_value DB_HOST 127.0.0.1)"
DB_PORT="$(env_value DB_PORT 3306)"
DB_DATABASE="$(env_value DB_DATABASE '')"
DB_USERNAME="$(env_value DB_USERNAME '')"
DB_PASSWORD="$(env_value DB_PASSWORD '')"
BACKUP_DIR="$(env_value BACKUP_PATH /var/backups/urban-haven)"
RETENTION_DAYS="$(env_value BACKUP_RETENTION_DAYS 30)"
GPG_RECIPIENT="$(env_value BACKUP_GPG_RECIPIENT '')"

if [[ -z "$DB_DATABASE" || -z "$DB_USERNAME" ]]; then
    echo "DB_DATABASE and DB_USERNAME must be set in .env" >&2
    exit 1
fi

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

MYSQL_CNF="$(mktemp)"
trap 'rm -f "$MYSQL_CNF"' EXIT
chmod 600 "$MYSQL_CNF"
printf '[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n' "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_PASSWORD" > "$MYSQL_CNF"

DB_FILE="$BACKUP_DIR/db-$STAMP.sql.gz"
FILES_FILE="$BACKUP_DIR/files-$STAMP.tar.gz"

mysqldump --defaults-extra-file="$MYSQL_CNF" --single-transaction --quick --routines --triggers "$DB_DATABASE" | gzip -9 > "$DB_FILE"
tar -czf "$FILES_FILE" -C "$APP_DIR/storage/app" private public

for file in "$DB_FILE" "$FILES_FILE"; do
    gzip -t "$file"
    if [[ -n "$GPG_RECIPIENT" ]]; then
        gpg --batch --yes --trust-model always --recipient "$GPG_RECIPIENT" --output "$file.gpg" --encrypt "$file"
        rm -f "$file"
    fi
done

find "$BACKUP_DIR" -maxdepth 1 -type f \( -name 'db-*' -o -name 'files-*' \) -mtime +"$RETENTION_DAYS" -delete

echo "Backup complete: $STAMP"
