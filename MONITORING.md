# Page and security monitoring

Open `?route=admin/monitoring`, or Page & Security Monitoring in the admin sidebar.

Installation: run `php tools/migrate-monitoring.php` against each target database before deploying. This idempotently creates page_telemetry and security_events and seeds canonical permissions. Local installation is complete. Normal HTTP requests never create tables or grant permissions. Superadmin and Admin receive view_monitoring; other roles can be granted it through Roles and Permissions. Branch users see only their assigned branch; Admin and Superadmin see all records, including public and unassigned traffic.

The shared database bootstrap registers request-end recording for public, admin, POS and AJAX entry points. Only rendered GET pages count as page views: redirects, JSON responses, polling and writes do not. Security status events cover authentication required (401), denied access (403), rejected CSRF (419), rate limits (429), and server errors (5xx). Authentication additionally records successful and failed sign-ins, logout, password reset success, and expired sessions. Security events can include background requests.

Stored data: route identifier without query parameters, account ID if signed in, database-assigned branch, response code, processing duration, timestamp. Page views also use a hashed random browser-session marker distinct from the authentication session ID. No IP addresses, request bodies, passwords, email addresses, reset tokens, or raw error messages are recorded. Metrics describe server processing and browser sessions, not full browser performance or unique people. Monitoring failures are logged server-side and do not interrupt checkout or sign-in.

Date filters support up to 366 days, the page ranking shows up to 100 routes, and security history shows the latest 200 events in the selected interval with counts across that interval. The monitoring page itself is counted after its response; Refresh shows the new record. Data is retained until an administrator archives or removes it through database maintenance. No fabricated production traffic is seeded. This is application event monitoring, not external uptime monitoring, malware detection or an automated incident-response service.

Verification: tools/tests/monitoring-check.php uses the disposable RBAC schema; tools/tests/monitoring-browser-check.cjs uses the isolated local test server. They test schema idempotency, permissions, branch isolation, absence of submitted secrets, aggregate counts, rendered dashboard and automatic denial recording.
