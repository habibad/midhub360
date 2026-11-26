# Midhub360 — Gateway Integration & Production Launch Runbook

For the engineers deploying and operating Midhub360. Covers what changed, how to deploy it, how to configure each provider, how to test before taking live money, and what is still open.

---

## 0. Secrets policy and the one-command live update

**Nothing secret is ever committed.** Passwords, API keys and the encryption key live only in the server's `.env` (chmod 600) or in Admin > Gateway Settings (encrypted in the DB). The same code runs locally (XAMPP defaults) and live (`.env`).
- `.githooks/pre-commit` and `.githooks/pre-push` run `tools/secret-scan.sh` and refuse commits/pushes containing passwords, keys, tokens, `.env` files, `.sql` dumps or `CLAUDE_*.md` prompt files. `push.bat`/`pull.bat` enable them (`git config core.hooksPath .githooks`). Audit anytime: `bash tools/secret-scan.sh --all`.
- Never `git push --force` to `main`, never `--no-verify`.
- The GitHub history was rewritten on 2026-11-26 to purge leaked secrets. Every existing clone must re-sync: `git fetch origin && git reset --hard origin/main` (the live script does this).

**Live update (replaces the manual steps below):**
```bash
cd /var/www/midhub360.com && sudo bash deploy/update_live.sh
```
It completes `.env` (asks for the DB password once, creates `APP_KEY`), checks the DB connection, backs up the database, saves any edits made on the server, moves the code to `origin/main`, runs migrations, re-applies `setup_server.sh` and reloads PHP-FPM. Then, once: `sudo bash deploy/rotate_db_password.sh`.

If migrations are pending, every admin page shows a red "Database update needed" bar; a page that crashes shows the error in a red box instead of a blank dark screen.

## 1. Deploy in this order (manual reference)

Do every step on a **staging copy first** if one exists.

1. **Back up the production database.**
   `mysqldump --single-transaction midhub-db > midhub-db-$(date +%F).sql` (keep it off the web root).
2. **Deploy the code** (`git pull`).
3. **Create `/var/www/midhub360.com/.env`** from `.env.example`:
   - `APP_ENV=production`, `APP_URL=https://www.midhub360.com`
   - `DB_*` values (then remove the hardcoded production password from `config/env.php`, see §6).
   - `APP_KEY=base64:...`, generated with `php -r "echo 'base64:'.base64_encode(random_bytes(32)),PHP_EOL;"`. **Store a copy in your password manager.** Losing it means re-entering every gateway credential.
   - Optional: `ALERT_EMAIL`, `CRON_TOKEN`.
   - `chown www-data:www-data .env && chmod 600 .env`
4. **Run `sudo ./setup_server.sh`.** It rewrites the Nginx config with the deny rules, installs the cron worker, and prints verification checks. Every "expect 404" line must show 404.
   - The script no longer resets the super-admin password. `RESET_SUPERADMIN=1` only unblocks the account and prompts for the DB password.
5. **Run migrations as the web user:** `sudo -u www-data php database/migrate.php`
   - Migration 1 converts all MyISAM tables to InnoDB and the three wallet columns to `DECIMAL(14,2)`. It refuses to run if any wallet value is non-numeric and prints the query to find it. Run it in a low-traffic window, because `ALTER TABLE user` locks the table briefly.
   - Re-running is safe (`--status` lists applied and pending migrations).
6. **Smoke-check:** log in as super admin and open **Payment Gateways → Gateway Settings**. Payment gateways show **OFF / SANDBOX**; **Bill Pay** is seeded ON / SANDBOX in manual-payment mode (so bill fetch keeps working as before); **SMS** is OFF.
   - `setup_server.sh` also installs the nightly DB backup (02:30, `cron/backup.sh`, 14-day retention). Set `BACKUP_RCLONE_REMOTE` in `.env` to copy each dump off the server; until then backups stay on the same disk.
7. **Rotate credentials** (§6), then configure and test gateways (§3, §4).

Rollback: restore the DB backup and redeploy the previous commit. The migrations have no automatic down step.

---

## 2. What changed

### Security fixes (all live on production today until deployed)

| # | Issue | Impact | Fix |
|---|---|---|---|
| 1 | `admin/login.check.php` only checked that *a* session existed | Any reseller who logged in at `/login.php` could open `/admin/` and credit their own wallet | Re-verifies `level = 5` and `active` against the DB on every admin request |
| 2 | `customer/callback.php` (PayStation) credited on every page load | Reloading a paid invoice's callback URL credited the wallet again, without limit | Atomic claim of the `add_fund` row, plus amount check |
| 3 | Reseller API (`api/*.php`) concatenated `user`/`key` into SQL; `Xss::xssClean` does not escape quotes | Unauthenticated callers could authenticate as any API-enabled reseller and spend their balance | Whitelist validation, prepared statement, constant-time key compare |
| 4 | Every wallet debit read the balance, then wrote an absolute value (100 sites) | Parallel requests (double-click, scripted API) were served for a single debit | Conditional atomic debit (`... WHERE balance >= amount`); new engine uses `SELECT ... FOR UPDATE` |
| 5 | Admin approve/cancel never checked current status | Double-clicking Cancel refunded twice; approving add-fund twice credited twice (with upline commission) | Status transition is claimed once; refunds are relative increments |
| 6 | Unauthenticated DB editors (`edit.php`, `update_bkash_send.php`, `admin/editbkash.php`, ...) | Anyone could rewrite mobile-banking records | Super-admin guard |
| 7 | `debug_login.php` was public | Printed the admin password hash and confirmed the password | CLI only |
| 8 | Nginx only denied `/.ht*` | `.git/`, `.env`, `aanonda_software_new.sql`, `yk/` backup, `*.md` (with credentials) downloadable if present | Deny rules in `setup_server.sh` |
| 9 | No CSRF protection anywhere | A malicious page could trigger admin actions | Same-origin check on all `route.php` actions, SameSite=Lax session cookie, tokens on new forms |
| 10 | Failed logins stored the attempted password in plaintext | Password near-misses in `login_logs` | No longer stored (purge history, see §6) |
| 11 | `api/recharge_status.php` not scoped to the caller | Any reseller could read others' recharges by guessing `rid` | Scoped to the authenticated account |
| 12 | `Request.php` missing quote (Dealer branch) | Wallet debited, then the INSERT died: money lost, no record | Fixed (and the flow replaced, see Phase 2) |
| 13 | `config/env.php` used `str_starts_with()` | Fatal error on production PHP 7.4 the moment a `.env` exists | PHP 7.4-compatible |

Also: all 101 vendor-obfuscated class files (`eval(base64_decode(...))`) were decoded to plain PHP. The decoded output was scanned and contains no backdoors; behaviour is identical.

### New features

- **Gateway Settings** (`admin/gateway_settings.php`): per-gateway on/off, Sandbox/Live switch, separate encrypted (AES-256-GCM) credential sets, editable endpoints, limits, charge %, USD rate, copyable callback/webhook URLs, **Test connection** button, Self-PIN on every save (5 wrong PINs lock saving for 15 min), and an audit log of changed field names (never values).
- **Paywell recharge** (Phase 2): customer recharges go to Paywell automatically when it is active, otherwise to the legacy eFlexiload API or the manual queue, as before.
- **Add Money Online** (`customer/add_money.php`): bKash Tokenized, Nagad, Upay, UddoktaPay, and Stripe (USD to BDT), into any of the three wallets.
- **Digital Vouchers** (`customer/vouchers.php`, `admin/voucher_products.php`): catalog sync, admin pricing, Main-wallet purchase with PIN, encrypted voucher vault.
- **Ops:** `admin/gateway_transactions.php` (deposits + Paywell recharges, manual re-check), `admin/admin_alerts.php`, `cron/worker.php`.
- **Ledger:** every movement made by the new engine is recorded in `wallet_ledger` with before/after balances.

Round 2 (final concept):

- **5-level recharge commission** (`admin/recharge_commission.php`): on each *successful* recharge the retailer and 4 uplines are paid their % into the Recharge wallet, exactly once (unique payout key). **Ships OFF**: the legacy add-time "upper commission" may already pay the chain; the page warns if both are on.
- **Utility bill pay** with service charge slots, PIN, draft → debit → settle, **PDF money receipt** (`customer/bill_receipt.php`), new Cable TV (Akash DTH), Internet (Link3, Carnival, AmberIT) and DPDC billers. Payment mode is **manual** (admin queue) until the aggregator's payment endpoint is confirmed; switch the Bill Pay tab to **api** then.
- **SIM modem gateway** (`apimobile/`, `admin/modem_devices.php`): per-device bearer tokens (hash stored), atomic job claim, settle-once success/failure, refund on failure. The old SQL-injectable key and the 2023 kill switch are gone. Phones need the new token (`Authorization: Bearer mdm_...` or `?key=mdm_...`).
- **Game top-up / Player ID and direct MFS checkout** for vouchers: pay with Main wallet (PIN) or straight through bKash/Nagad; the order is placed automatically once the payment is verified. A rejected ID is refunded at once.
- **Popular Vouchers v1.1** (14,500+ products): resumable sync, filters and bulk markup in the admin, store search/category chips for customers. See §3.
- **Stripe Checkout Session** (Apple Pay / Google Pay / cards, 3DS): credited at *rate − safety margin*; new card customers are limited to $30/day for their first 3 days. The Add Money page shows the exact credited rate.
- **Circuit breaker + SMS alerts**: 5 consecutive provider failures pause that provider (or Paywell operator) for 10 minutes; recharges fall back to the manual queue. Critical alerts are also sent by SMS to the admin phones (SMS tab).
- **Brute force**: 3 wrong passwords block the account; 5 wrong PINs from one IP in 15 minutes ban PIN entry from that IP (across accounts); 20 failed logins from one IP lock that IP for 15 minutes.
- **Hardening** in `setup_server.sh`: Nginx rate limits (login 30/min, API 10/s), CSP + Permissions-Policy headers, Fail2ban (sshd, nginx-limit-req).

### Money-safety rules the new engine enforces

- A wallet is credited **only** after a server-to-server verification. Browser returns and webhooks are triggers, never proof.
- The amount, currency and echoed invoice number must match, and the provider transaction ID must not already be used. Otherwise the deposit goes to **`review`** (not credited) and a critical alert is raised.
- Settlement runs under a row lock, so duplicate callbacks, webhooks and cron runs credit **exactly once**.
- A recharge is refunded automatically **only** when the request certainly never reached Paywell, Paywell returned a structured rejection, or 3 consecutive enquiries say failed. Timeouts and 5xx stay **processing (status 6)** until resolved.
- Invoices and recharges are tagged with the Sandbox/Live mode they were created in, and are never settled from the other mode.

---

## 3. Configure each gateway

Admin → **Payment Gateways → Gateway Settings**. Leave a secret field blank to keep the saved value. Activation is refused unless the active mode's base URL and required credentials are filled in.

| Gateway | Credentials | Register with the provider | Notes |
|---|---|---|---|
| **bKash Tokenized** | app_key, app_secret, username, password | Callback URL `https://www.midhub360.com/gateway/bkash_callback.php` | The public sandbox merchant (pre-filled) passes Grant Token but rejects every callback URL (`2049 Invalid Merchant Callback URL`, even `https://example.com`). **Request your own sandbox credentials from bKash with this domain whitelisted.** |
| **Nagad** | merchant_id, merchant number, merchant private key, Nagad public key | Callback `https://www.midhub360.com/gateway/nagad_callback.php` | Keys accepted as PEM or bare base64. Test connection validates the keys and host only. A real test needs a checkout. |
| **Upay** | merchant id/key/code/name/mobile | Return URL shown on the page | **Upay has no public API documentation.** Confirm the 3 endpoint paths and the response field names against your Upay integration document before going live. |
| **UddoktaPay** | api_key | Webhook `https://www.midhub360.com/gateway/webhook.php?gw=uddoktapay` | Live Base URL is your own UddoktaPay panel domain. Sandbox key is pre-filled and verified working. |
| **Paywell** | reseller user/pass, API user/pass, api_key, encryption_key | Callback (optional) `https://www.midhub360.com/gateway/webhook.php?gw=paywell` | Legacy `api_paywell` credentials were imported into **Live** (left OFF). **Confirm the enquiry endpoint path, the status codes (`success_codes`/`pending_codes`) and operator codes** against Paywell's API spec v2.1.x. |
| **Popular Voucher** | api_key (Bearer), optional api_secret | Allowlist the server IP in the distributor portal | Base `https://medback.popularvouchers.com`. Catalog (`/distributor/catalog`, 100 per page) and account (`/distributor/account`) are **verified live**. The **order** field names (`variant_id`, `reference`, `delivery_data`) are editable defaults: **confirm them against the v1.1 document** (it is behind the distributor portal) or with one sandbox order before going live. The account's `environment` is checked: a LIVE store with a SANDBOX key places no orders. Sandbox with an empty base URL = mock mode. |
| **Bill Pay** | token | none | Fetch works against the aggregator sandbox. Keep `payment_mode = manual` until the payment and status endpoints are confirmed. |
| **SMS** | api_key, sender id | none | URL template with `{to} {message} {api_key} {sender}`. Set the admin phone numbers and the minimum severity. |
| **Stripe** | pk_, sk_ (or rk_), whsec_ per mode | Webhook endpoint `https://www.midhub360.com/gateway/webhook.php?gw=stripe`, events `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, `checkout.session.expired` | Uses Stripe Checkout (Apple Pay needs the domain verified in the Stripe dashboard). Test and Live webhooks have **different** `whsec_` secrets. Min/Max are in USD. Credited rate = exchange rate − `rate_margin_bdt`, locked per invoice. |

Then **Digital Vouchers → Sync from provider**. The full catalog takes several clicks (about 20 s each) or finishes by itself through the cron worker; it refreshes nightly at 03:00. New products are **inactive**. To publish: filter (category / country / search) → **Set markup %** (sell = provider cost + %, rounded up, follows the cost on every sync) → **Activate**. A product whose cost rises above a fixed sell price is hidden from the store automatically and raises a `Vouchers priced below cost` alert.

---

## 4. End-to-end test plan (per gateway)

Run each step in **Sandbox**, then repeat the critical ones with a small real amount right after switching to **Live**.

1. **Test connection** on the gateway tab: must be green.
2. **Happy path:** a customer adds the minimum amount. The wallet is credited once, Admin → Online Deposits shows `completed`, the customer gets a notification.
3. **Refresh/replay:** reload the return page and re-send the webhook. The balance must not change.
4. **Cancel:** cancel on the provider page. The status becomes `cancelled` and nothing is credited.
5. **Abandon:** close the tab before paying. After about 24h the cron marks it `cancelled` (or use **Check** to see `pending`).
6. **Stripe 3DS:** card `4000 0027 6000 3184` must show the challenge. `4000 0000 0000 9995` must fail cleanly.
7. **Recharge (Paywell):** one prepaid, one postpaid and one Skitto recharge, plus one to a wrong-operator number (must refund). Verify statuses in Admin → Paywell Recharges and the operator's delivery.
8. **Vouchers:** buy one code product and one Player-ID top-up, then one the provider rejects (must refund to the Main wallet). Pay one with bKash directly and check it is delivered after the payment.
10. **Bill pay:** fetch a bill (wrong PIN must be refused), pay it (manual mode: appears in the admin Bill Pay queue), mark it Success and download the PDF receipt; cancel another and confirm the full refund including the service charge.
11. **SIM modem:** create a device in Admin → SIM Modem Devices, put the token in the phone app, queue one recharge, confirm the phone claims it once and reports success.
9. **Kill switch:** turn a gateway OFF. It must disappear from Add Money immediately.

Automated suites (run on a staging/dev database, never production):

```bash
php tests/run.php          # 175 checks: money math, crypto, races, every flow vs mock providers (incl. the Popular Vouchers v1.1 shape)
php tests/http_smoke.php   # 101 checks through the web server: auth, CSRF, admin UI, customer pages, PDF receipt, modem, login lock
```

Both pass locally (PHP 8.2, MariaDB, strict SQL mode). All 235 changed PHP files were scanned token-by-token for PHP 8-only syntax and functions (none found). **Still run both suites once on staging with PHP 7.4**, because that check was static. The suites back up and restore gateway settings, platform settings and the voucher catalog, but they write to the database they run on: never point them at production.

---

## 5. Operations

- **Cron:** `/etc/cron.d/midhub360` runs `cron/worker.php` every minute (recharge enquiry, deposit reconciliation, payment intents, bill-pay status, voucher status, voucher catalog sync, Paywell and Popular Vouchers low-balance checks every 15 min) and `cron/backup.sh` at 02:30. Logs: `/var/log/midhub-worker.log`, `/var/log/midhub-backup.log`.
- **System Alerts** (admin menu + header bell, optionally email):
  - `Deposit needs review`: the customer paid but was **not** credited (amount/invoice mismatch or duplicate trx). Check the provider portal, then credit manually through the normal add-balance flow.
  - `Recharge stuck in processing`: no final Paywell status after 30 checks. Verify in the Paywell portal before acting.
  - `Paywell balance low`: below the threshold set on the Paywell tab (default 20,000 BDT). `Voucher provider balance low`: same for Popular Vouchers (default 2,000 BDT).
  - `Circuit breaker open`: a provider failed 5 times in a row and is paused for 10 minutes (**Resume now** on the alerts page).
  - `Voucher store blocked`: LIVE mode with a SANDBOX Popular Vouchers key. Save the live key.
  - `Vouchers priced below cost`: reprice or apply a markup.
  - `Bill payment stuck`: no final aggregator status after 30 checks.
- **Do not cancel `processing` (status 6) Paywell recharges** from the legacy pending pages: Paywell may still deliver them. Use **Check** on Admin → Paywell Recharges.
- `api_log` records every outgoing call and incoming webhook with secrets redacted. Plan a retention job (it grows quickly).

---

## 6. Open items and decisions (action needed)

**Credentials to rotate.** They are in git history and/or were exposed by the issues in §2:
- The production DB password: it was hardcoded in 16 files (now removed; every file reads `DB_PASS` from `.env`). Rotate it with `sudo bash deploy/rotate_db_password.sh`.
- The super-admin password and Self-PIN: they were written in an AI prompt file that reached GitHub. Change both in the admin panel.
- The Paywell `api_key`/`encryption_key` that were hardcoded in `Paywell.php`, if they were live keys.

**Repository hygiene (done 2026-11-26).** `yk/`, `aanonda_software_new.sql`, the `CLAUDE_*_PROMPT.md` files and three files that printed the DB password (`bkash2.html`, `edit_bkash_send.html`, `assets/libs/jquery-tabledit/example.php`) were removed and purged from the whole git history. See §0 for the rules that keep it that way.

**Data cleanup:** `UPDATE login_logs SET password = '[purged]';` removes historical plaintext login attempts.

**Not implemented (scope decisions):**
- **MFS Cash-In / Send-Money disbursement** (paying *out* to a customer's bKash/Nagad) is still the manual admin queue. Automating it needs a bKash/Nagad **B2C disbursement** agreement and API, which is separate from checkout. What was built is Add Money (collection) through the 5 gateways.
- **Utility bill payment API**: the full flow is built, but the aggregator's payment/status endpoints are unconfirmed, so it runs in **manual** mode (customer is debited, admin pays and marks Success). Switch to **api** once the endpoints are confirmed.
- **Per-recharge commission** is built but **OFF**. Decide whether it replaces the legacy add-time upper commission (Commission id 2) before turning it on, or the chain is paid twice.
- **Popular Vouchers order format**: confirm with the v1.1 document (§3).
- MD5 password/PIN hashing is unchanged (the login code already accepts `password_hash` values, so a gradual upgrade is possible).
- `apimobile/` was rebuilt with device tokens; the Android app must be updated to send its token.

**Directive mapping:** the original plan named some paths that didn't match the codebase (`recharge/source.php` was a 6-line stub; `payment/` is the UddoktaPay self-hosted installer). The new code lives in `lib/Midhub/` (services, namespaced), `gateway/` (return/webhook endpoints), `customer/` and `admin/` (pages), `database/migrations/`, `cron/`, and `tests/`.
