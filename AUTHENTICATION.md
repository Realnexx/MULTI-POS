# Authentication and password recovery

Login, registration, forgot-password and reset-password pages share the burger website design. New registrations require a real email and always receive the Customer role, resolved by name. Posted role and branch choices are ignored.

## Limits and sessions

- Five account login attempts per 15-minute window; a successful sign-in clears that account bucket. Username and email aliases share the same bucket.
- Thirty login attempts per IP per 15 minutes; cookies and browser sessions cannot clear these database limits.
- Five registration attempts per IP per hour, ten recovery requests per IP per hour, and three recovery requests per email per 15 minutes.
- Ten reset submissions per IP per 15 minutes.
- Sessions expire after 30 minutes of inactivity or eight hours total. Background GET polling does not extend the idle deadline. Requests after expiry require sign-in; AJAX receives JSON with status 401.
- Password recovery revokes existing sessions through a database security version. Session cookies are HTTP-only, SameSite=Lax, session-only, and Secure on HTTPS. Authentication pages and authenticated responses use no-store.

Limits and timeouts can be overridden with the private `auth` configuration. Defaults are in `config/auth.php`.

## Recovery email

PHPMailer 6.12.0 is pinned under `lib/phpmailer` for compatibility with the local PHP 7.4 runtime. Upstream source, license and file checksums are recorded there. SMTP credentials are stored in ignored `config/mail.local.php`; `config/mail.example.php` documents the shape. They are excluded from deployment artifacts.

Gmail SMTP uses port 587 with STARTTLS. The configured sender was authenticated successfully without sending a test email. Production requires copying the private SMTP configuration separately and verifying that the host permits outbound SMTP. Never disable TLS certificate verification.

The trusted `public_url` determines reset links and is never derived from an HTTP Host header. Local links point to `http://localhost/pos/`; the configured production URL is `https://tindaflow.wuaze.com/`. Supported environment overrides: APP_PUBLIC_URL, SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_EMAIL, SMTP_FROM_NAME, SMTP_ENCRYPTION.

Recovery responses do not disclose whether the email exists. Tokens use 32 random bytes, only their SHA-256 digest is stored, and links expire after 30 minutes. A newer request invalidates older links; reset is atomic and single-use. A failed delivery revokes its token. Reset does not automatically sign the user in. Existing accounts need a working saved email to receive recovery mail.

Passwords for new registrations and resets require at least 12 characters and at most 72 bytes to prevent bcrypt truncation. Login continues to accept existing passwords. All forms validate on the server and require CSRF protection. Reset pages use no-referrer to avoid leaking URL tokens to external assets.

## Database and deployment

Run `php tools/migrate-auth-security.php` once on each database before deploying the new runtime. This idempotently creates rate-limit, password-reset and user security-version tables; the local migration has been applied. It does not change existing passwords, users or roles. Initialization also creates these tables.

Existing sessions without a security stamp must sign in again after this update. No production deployment has been performed. Private mail settings and credentials must be copied separately; the deployment artifact includes PHPMailer and public authentication configuration.

Optional maintenance: remove expired auth_rate_limits rows and expired/used auth_password_resets rows periodically. Never log raw reset tokens or SMTP credentials.

## Verification

Tests use an isolated schema clone and a local SMTP sink. They cover persistent limits, aliases, validation, registration role restrictions, CSRF, favicon settings, session expiry/revocation, token expiry/replay, PHPMailer email generation, reset completion and responsive layouts. No recovery email is sent externally by the automated tests.

References: https://github.com/PHPMailer/PHPMailer and https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html
