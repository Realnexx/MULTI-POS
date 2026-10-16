# Database backups and maintenance

System Settings → Database backups controls a daily Philippine-time hour, enabled state and retention of 1–90 completed copies. Only global administrators with `manage_settings` can use it. Every action requires CSRF and a freshly verified account password. No editable public application URL is needed.

Install schema with `php tools/migrate-system-settings.php --apply`. This widens PIN storage and hashes legacy PINs without changing their values, creates receipt/financial snapshots, authenticator factors, managed errors and backup metadata. Configure replacement **four-digit** POS PINs where old settings contained six digits; the terminal accepts four digits. Newly set PINs are never displayed.

## Scheduling

The scheduler polls hourly; the application claims each daily run once, locks concurrent backup/verification jobs, and records its last check, last success and failure in Settings. Missed days are not backfilled. After a failed daily run, use Back up now to retry. Defaults are disabled, 02:00 Philippine time, seven completed copies. Enabling scheduled copies does not start a daemon by itself.

For this Windows/XAMPP installation run `powershell -File tools/install-maintenance-task.ps1` as the account operating the application. This installs a limited, hourly Windows task that runs while that account is signed in. For unattended servers, configure that same command under a dedicated least-privilege service account in Task Scheduler with permission to run when signed out. Never run application-writable scripts as SYSTEM or an elevated administrator.

For Linux, run the absolute PHP executable and absolute `tools/system-maintenance.php` path from the server's scheduler every hour under the application's limited account. The runner also applies monitoring retention and updates a heartbeat. Settings and Monitoring display a warning if the runner is more than two hours overdue. Scheduled-task errors also appear in the operating system task history.

## What is backed up

All base tables in the configured database, including all branches and historical records, schema, binary data and nulls. The dump uses an InnoDB consistent snapshot and metadata locks, and streams authenticated AES-256-GCM records directly to a private encrypted file. Each record has a distinct nonce and sequence-bound authenticated data; a final manifest detects missing records. No readable SQL dump is written. Only finalized, SHA-256 checked files can be downloaded. Retention removes completed files after a successful new copy; failed jobs never prune the last good copy.

Private files are under `config/backups`; the recovery key is `config/backup.key`. Both are Git-ignored and HTTP-blocked by the application's existing internal-directory rule. Encrypted archives include `config/paymongo.key` and `config/security.key` when present so restored payment/authenticator secrets can be decrypted. Database/SMTP login credentials and uploaded product/branch photos are **not** included: back those up separately. Keep an off-machine copy of archives and the separately downloaded recovery key; losing both the server key and its exported copy makes recovery impossible. A copy on the same disk does not protect against disk failure.

Non-InnoDB tables, views, routines, triggers and database events are rejected explicitly; use a database-native backup for such installations. Use an account with complete visibility of the source schema. Pause schema migrations during backups. There is no remote-cloud upload or external uptime service in this feature.

## Verification and recovery

Use Verify restore, or `php tools/database-backup.php verify ID`. This checks file hash, authenticates all records, recreates schema/data in a fresh `pos_restore_check_<random>` database, compares every table's row count and runs table checks. It then drops only that uniquely named database, even if verification fails. This requires CREATE/DROP DATABASE privileges. It never restores onto the live database. The verification timestamp is shown only after success.

Download the encrypted file and recovery key separately. On a recovery server, install the same application version, configure its database connection and run `php tools/recover-database-copy.php ARCHIVE RECOVERY_KEY_FILE`. This authenticates the archive, restores into a newly generated `pos_recovered_<random>` database, validates row counts/table health and leaves the copy for inspection. It cannot overwrite an existing/live database. Failure drops only its own fresh database. Recovered payment/authenticator keys are written to a private recovery folder beneath `config/backups`; the command prints that folder and the new database name, never key contents.

Validate the recovered copy, install those recovered private keys with their original filenames in the new deployment's `config` directory, then change private deployment configuration to select the recovered database. Back up the deployment configuration/photos separately. Do not replace live database configuration until the recovered copy has been reviewed. No live database replacement was performed during implementation.

## Checkout and security policies

Tax and service charges require explicit enablement to avoid silently changing existing prices. Tax supports inclusive/exclusive pricing, applies after discount, and does not tax the service charge. Service charge is a percentage of the discounted listed price. Discount configuration enables/disables amount discounts and caps the percentage of **eligible** products. These are configurable business rules, not statutory-discount eligibility or automated tax compliance. Existing online orders retain their quoted totals; settings changes affect new orders. Receipt counters advance atomically with successful sales and never reuse a number after a settings change. Existing receipts retain saved text and amounts; old sales retain their historical SHH-number fallback.

Manual payment method settings govern POS and online counter collection; they do not initiate gateway charges. PayMongo gateway checkout remains online-only.

Administrative sign-in can require a 30-second SHA-1 authenticator code using [RFC 6238](https://www.rfc-editor.org/rfc/rfc6238). First password-authenticated sign-in enrolls a device, verifies a code and shows eight single-use recovery codes. Until verification finishes, no authenticated user session exists. The challenge expires in ten minutes, codes are throttled, reused counters are rejected, secrets are encrypted and recovery codes are hashed. Keep recovery codes privately. Enabling the policy revokes existing sessions. There is no email-based bypass. Session timeout and strong password settings now drive the shared enforcement paths; all passwords retain a minimum 12-character baseline. The unused admin-secret field was removed in favor of actual current-password confirmation on sensitive settings.

Monitoring stores error category, source location, route, occurrence count and a fingerprint, never the raw error text or request details. Global administrators can review errors and configure 7–365 day retention; repeated errors reopen review. Existing activity/audit history is not pruned by this policy. External PHP/server logs remain managed by the server administrator.
