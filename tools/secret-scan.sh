#!/usr/bin/env bash
# ==============================================================================
# Midhub360 secret scanner: keeps passwords, API keys, private keys, .env files and database
# dumps out of git (and therefore out of GitHub).
#
#   tools/secret-scan.sh --staged       what is about to be committed (pre-commit hook)
#   tools/secret-scan.sh --range A..B   commits about to be pushed (pre-push hook)
#   tools/secret-scan.sh --all          audit every tracked file
#
# Exit code 1 = something secret-looking was found; the commit/push is refused.
# Secrets belong in the server's .env file (never committed) or in Admin > Gateway Settings
# (stored encrypted in the database).
#
# A line that is a deliberate, public sandbox/demo value can be allowed by ending it with
# the comment  secret-scan:allow  (never use this for a real key or password).
# ==============================================================================
set -u
export LC_ALL=C

mode="${1:---staged}"
range="${2:-}"

# Files that must never be committed.
FORBIDDEN_PATHS='(^|/)\.env$|(^|/)\.env\.[^/]*$|(^|/)CLAUDE_[^/]*\.md$|\.sql(\.gz)?$|\.dump$|\.(key|p12|pfx|jks|keystore)$|\.pem$|(^|/)id_(rsa|dsa|ecdsa|ed25519)|(^|/)[^/]*api_token[^/]*$|(^|/)storage/keys/|(^|/)credentials?\.(json|txt)$'
ALLOWED_PATHS='(^|/)\.env\.example$|(^|/)cacert\.pem$|(^|/)__MACOSX/'

# Content that looks like a real credential.
SECRET_RE='BEGIN [A-Z ]*PRIVATE KEY|pv_(live|test)_[A-Za-z0-9]{8,}|sk_live_[A-Za-z0-9]{8,}|rk_live_[A-Za-z0-9]{8,}|whsec_[A-Za-z0-9]{16,}|AKIA[0-9A-Z]{16}|ghp_[A-Za-z0-9]{30,}|github_pat_[A-Za-z0-9_]{30,}|AIza[0-9A-Za-z_-]{35}|xox[abposr]-[A-Za-z0-9-]{10,}|eyJ[A-Za-z0-9_-]{15,}\.eyJ[A-Za-z0-9_-]{15,}\.'
# Hardcoded literal assigned to a password/secret/key name, 6+ chars, not a $variable (secret-scan:allow)
ASSIGN_RE='(pass(word|wd)?|pwd|secret|api_?key|app_?secret|private_?key|access_?token|auth_?token|store_?passw(or)?d)["'"'"']?[[:space:]]*(=>|=|:)[[:space:]]*["'"'"'][^"'"'"'[:space:]$<{[][^"'"'"'[:space:]]{5,}["'"'"']'
TAB="$(printf '\t')"
# KEY=value lines as in a .env file (records are "path<TAB>line")
ENVLINE_RE="${TAB}"'[[:space:]]*[A-Z0-9_]*(PASS|PASSWORD|SECRET|TOKEN|API_KEY|APP_KEY|PRIVATE_KEY)[A-Z0-9_]*[[:space:]]*=[[:space:]]*["'"'"']?[^[:space:]$#"'"'"'][^[:space:]]{5,}'
# Lines that are clearly not real credentials
ALLOW_LINE_RE='secret-scan:allow|qa_|example\.com|placeholder|your[_ -]|changeme|<[A-Za-z_ ]+>|\{\[|\*\*\*|xxxx|dummy|\$_(POST|GET|REQUEST|SERVER|ENV)|env\(|getenv\(|Input::get'
# Third-party code where generic "password = ..." patterns are documentation or tests
VENDOR_PATHS="^([^${TAB}]*/)?(vendor|third_party|node_modules|bower_components|__MACOSX)/|^assets/libs/|^payment/assets/|^qrpay/public/|^tests/|^[^${TAB}]*\\.min\\.js${TAB}"

found=0
# stdin: "path<TAB>line" records; prints them as findings with a reason
report_records() {
    awk -F"$TAB" -v why="$1" '{ l = $2; if (length(l) > 140) l = substr(l, 1, 140) "..."; printf "  %s\n      %s: %s\n", $1, why, l }' >&2
}

check_paths() { # stdin: file paths
    local hits
    hits="$(grep -E "$FORBIDDEN_PATHS" | grep -Ev "$ALLOWED_PATHS")"
    if [ -n "$hits" ]; then
        printf '%s\n' "$hits" | awk '{ printf "  %s\n      file type that must never be committed (secrets / database dump / AI prompt with credentials)\n", $0 }' >&2
        found=1
    fi
}

check_lines() { # stdin: "path<TAB>line" records
    local tmp hits allow
    tmp="$(mktemp)"
    # .secret-scan-allow: exact public sandbox/demo strings (one per line) that are not secrets.
    # Needed for old commits, where an inline "secret-scan:allow" comment cannot be added.
    allow="$(git rev-parse --show-toplevel 2>/dev/null)/.secret-scan-allow"
    if [ -f "$allow" ]; then
        grep -v -e '^#' -e '^[[:space:]]*$' "$allow" | tr -d '\r' > "$tmp.allow"
        if [ -s "$tmp.allow" ]; then grep -vF -f "$tmp.allow" > "$tmp" || true; else cat > "$tmp"; fi
        rm -f "$tmp.allow"
    else
        cat > "$tmp"
    fi
    hits="$(grep -E "$SECRET_RE" "$tmp" | grep -v 'secret-scan:allow')"
    if [ -n "$hits" ]; then printf '%s\n' "$hits" | report_records "API key / token / private key"; found=1; fi
    hits="$(grep -Ev "$VENDOR_PATHS" "$tmp" | grep -Ev "$ALLOW_LINE_RE" | grep -Ev "$SECRET_RE" | grep -Ei "$ASSIGN_RE")"
    if [ -n "$hits" ]; then printf '%s\n' "$hits" | report_records "hardcoded password/secret (use .env or Gateway Settings)"; found=1; fi
    hits="$(grep -Ev "$VENDOR_PATHS" "$tmp" | grep -Ev "$ALLOW_LINE_RE" | grep -Ev "$SECRET_RE" | grep -Eiv "$ASSIGN_RE" | grep -E "$ENVLINE_RE")"
    if [ -n "$hits" ]; then printf '%s\n' "$hits" | report_records ".env-style secret value"; found=1; fi
    rm -f "$tmp"
}

# Added lines of a unified diff -> "path<TAB>line"
diff_added_lines() {
    awk '
        /^\+\+\+ b\//      { path = substr($0, 7); next }
        /^\+\+\+ /         { path = ""; next }
        /^\+/ && path != "" { sub(/^\+/, ""); gsub(/\t/, " "); print path "\t" $0 }
    '
}

# Fast prefilter: only lines matching any rule reach the slower per-line checks.
PREFILTER='BEGIN [A-Z ]*PRIVATE KEY|pv_(live|test)_|sk_live_|rk_live_|whsec_|AKIA|ghp_|github_pat_|AIza|xox[abposr]-|eyJ|pass|pwd|secret|api_?key|token|PRIVATE_KEY|APP_KEY'

case "$mode" in
    --staged)
        check_paths < <(git diff --cached --name-only --diff-filter=ACMR)
        check_lines < <(git diff --cached -U0 --no-color --diff-filter=ACMR | diff_added_lines | grep -Ei "$PREFILTER")
        ;;
    --range)
        [ -n "$range" ] || { echo "usage: $0 --range <rev-range>" >&2; exit 2; }
        # shellcheck disable=SC2086
        check_paths < <(git log --format= --name-only --diff-filter=ACMR $range | sort -u)
        # shellcheck disable=SC2086
        check_lines < <(git log -p -U0 --no-color --format= --diff-filter=ACMR $range | diff_added_lines | grep -Ei "$PREFILTER")
        ;;
    --all)
        check_paths < <(git ls-files)
        check_lines < <(git grep -I -n -E -i "$PREFILTER" -- . | awk -F: '{ path = $1; sub(/^[^:]*:[0-9]+:/, ""); gsub(/\t/, " "); print path "\t" $0 }')
        ;;
    *)
        echo "usage: $0 [--staged | --range <rev-range> | --all]" >&2
        exit 2
        ;;
esac

if [ "$found" -ne 0 ]; then
    cat >&2 <<'MSG'

BLOCKED: secret-looking content found (listed above). Nothing was committed/pushed.
  - Real passwords/keys go in the server's .env file or Admin > Gateway Settings, never in code.
  - Remove the file from the commit:  git restore --staged <file>   (and add it to .gitignore)
  - A public sandbox/demo value may be marked with the comment  secret-scan:allow
MSG
    exit 1
fi
exit 0
