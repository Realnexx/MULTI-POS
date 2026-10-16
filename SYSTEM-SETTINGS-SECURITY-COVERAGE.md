# L. System Settings and Security — implementation check

Checked 16 October 2026 against the active POS application. This is a source and feature-coverage review, not a penetration test or assurance that every endpoint is secure. The separate `auroratabs` application and external server/hosting configuration are excluded.

The existing settings, branches, roles, permissions, activity logs, monitoring, authentication security, and PayMongo configuration tables are installed. Application-managed backup settings, private archive storage and the local maintenance task are now installed. External host backups remain unverified.

## 1. Business and branch settings — implemented

Store identity, logo, address/contact details and online-ordering controls are saved through System Settings. Branch management supports branch details, status, manager/staff assignment, images, an operational overview and guarded deletion.

Evidence: `views/modules/settings.php`, `controllers/AdminController.php::settings`, `models/Setting.php`, `models/Branch.php`, `models/BranchOverview.php`, `ajax/branch_action.php`.

Global financial settings are separate from branch profiles; branch-specific tax/receipt overrides are not implemented.

## 2. Receipt and payment method configuration — implemented

Receipt settings and an editable preview exist. Manual POS payment choices work. PayMongo settings support enabled/disabled state, encrypted credentials, capability synchronization into database records, hosted online checkout and verified payment confirmation. POS PayMongo integration is intentionally deferred.

Actual receipt renderers now consume saved receipt text, atomic numbering and immutable financial snapshots. Historical sales retain their original numbering fallback. Manual payment methods are configured in POS Operations and enforced at both POS and online-order counter collection. PayMongo initiation remains online-only.

Evidence: `views/modules/settings.php:306`, `controllers/PosController.php:415`, `assets/js/main.js:848`, `models/OnlineOrder.php::record_payment`, `models/PayMongo.php`, `views/partials/paymongo_settings.php`.

PayMongo credentials have not been supplied in the source database, so real merchant connectivity and settlement are not verified. Gateway refunds and their financial reconciliation remain outside automatic synchronization.

## 3. Tax and discount configuration — implemented for configurable business rules

Tax rate and service-charge fields are saved. Manual amount discounts are validated during server-side checkout, including rejection of discounts exceeding the subtotal.

Explicit tax and service-charge switches now control server-side checkout for POS and online orders. Tax supports inclusive and exclusive pricing after discount. Service charge applies to the discounted listed price; tax does not apply to that service charge. Old orders retain their quoted totals. Discounts can be disabled or capped as a percentage of eligible items, and the product `is_discountable` flag is enforced. This implements configurable business rules; statutory eligibility and manager approval workflows are not provided.

Evidence: `views/modules/settings.php:246`, `views/modules/settings.php:256`, `models/RecipeInventory.php::checkout`, `ajax/pos_cart.php`, `models/Product.php`.

## 4. User roles and permissions — implemented

Roles, permission assignment, user-role links, server-side page/action checks and branch restrictions exist. The permission catalog includes module-level and action-level controls. Role changes produce activity records. Coverage exists beyond simply hiding menu items.

Evidence: `models/Role.php`, `models/User.php`, `config/permissions.php`, `middleware/rbac.php`, `middleware/access_policy.php`, `controllers/AdminController.php`.

This confirms the feature exists; it does not certify every legacy action or external application.

## 5. Activity and audit logs — implemented at a basic level

Activity records include actor, action, description, IP and time, with filtering and printing. Inventory movements, transfer events, purchasing events, cashier shift events and schedule audit records retain operational history.

No centralized retention policy, tamper-evident storage or comprehensive before/after snapshot for every settings change was found. The activity screen is a basic audit trail, not an immutable audit service.

Evidence: `models/ActivityLog.php`, `views/modules/activity_logs.php`, `models/RecipeInventory.php`, `models/Purchasing.php`, `models/CashShift.php`, `models/CashierSchedule.php`.

## 6. Error logging and basic monitoring — implemented

PHP error logging is enabled. The monitoring module records page status/duration and security events such as denied access, expired sessions and server errors. Monitoring has its own permission and date filters; its tables are installed and collection starts from the common bootstrap.

The monitoring page now includes managed, grouped application errors with source location, route, counts and review acknowledgements for global settings administrators. Raw error text/request data is excluded. Configurable retention is applied by the hourly maintenance runner; it also records a health heartbeat. In-app warnings show overdue maintenance and backup failure. External email/pager alerts, external uptime checks and PHP/server log rotation remain deployment responsibilities.

Evidence: `config/app.php`, `config/db.php:185`, `models/Monitoring.php`, `controllers/MonitoringController.php`, `views/modules/monitoring.php`.

## 7. Secure access controls — consistency fixes implemented

Implemented safeguards include password hashing and verification, login throttling, password-reset tokens, session regeneration, idle/absolute expiry, CSRF validation, route/action permissions, branch scoping, upload checks, protected internal directories and encrypted gateway secrets.

The session timeout drives actual idle expiry, the strong-password policy is shared by all password persistence paths, security settings require current-password confirmation, and administrative sign-in can require authenticator enrollment/challenge with encrypted secrets, throttling, replay protection and hashed single-use recovery codes. Enabling the policy revokes existing sessions. PIN updates use bound statements and password hashes; existing PINs are migrated without changing their values. The terminal and new PIN settings consistently use four digits. The POS PIN requirement is enforced according to its switch, while cashier drawer unlocking still requires credentials. The previously unused admin-secret field was removed rather than presented as protection.

See `DATABASE-BACKUPS.md`, `models/SystemPolicy.php`, `models/TwoFactor.php`, `models/AuthSecurity.php`, `controllers/AuthController.php` and the focused integration tests. These changes do not constitute a penetration test or a guarantee covering every legacy endpoint.

## 8. Initial database backup configuration — implemented

System Settings now supports encrypted manual/daily database copies, retention, current-password-confirmed downloads, a separately exported recovery key and restore verification on a disposable database. Archives include payment/authenticator encryption keys. A CLI recovery tool restores into a uniquely named new database, never over the live one. The local hourly Windows task runs under the current limited user while signed in; unattended hosting needs an appropriate service account. See `DATABASE-BACKUPS.md` for configuration, limitations and the recovery procedure. Photos/server credentials require separate file backups.

## 9. Supported data export functionality — implemented for selected modules

CSV exports exist for products, POS transactions, purchasing reports and cashier shift closing reports. Report/branch-audit/activity pages support printing, including browser Save as PDF. Export permissions and branch filters exist.

There is no universal export center, native Excel export or full database-backup export in the reviewed code. Printable HTML is distinct from server-generated PDF export.

Evidence: `controllers/InventoryController.php:90`, `controllers/PosController.php:133`, `controllers/PurchasingController.php:74`, `controllers/CashShiftController.php:57`, `views/modules/reports_print.php`, `views/modules/branch_report.php`, `views/modules/activity_logs.php`.

## Implementation validation

The isolated application suite, focused settings/authentication tests, encrypted backup/recovery tests and responsive settings checks cover the implemented paths. Source database business records are not changed by these tests. Operational installation creates backup metadata, migrates legacy PIN hashes, adds feature tables and receipt counter, configures the scheduled runner and creates encrypted recovery-tested copies. Receipt/payment and financial policy changes apply to new transactions; no historical sale amounts are rewritten.
