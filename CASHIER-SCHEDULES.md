# Cashier schedules and fresh POS entry

Open **Cashier Shifts > Cashier schedules**, select a branch, and add a cashier schedule. Admin/Superadmin receive the new **Configure Branch Cashier Schedules and Grace Periods** permission on migration; assign it explicitly to other managing roles in Roles & Permissions. Staff can see their own schedules; managers with this permission can manage schedules only in their accessible branches.

Schedules use explicit Asia/Singapore (UTC+8) dates and times, support overnight shifts up to 24 hours, and can be edited or cancelled without deleting history. Overlaps for the same cashier are rejected across branches. Branch grace periods allow 0–120 minutes before the start and after the end; both default to zero. The end boundary is exclusive. Schedule edits, cancellations, and grace changes are audited.

The selected branch must have an active schedule for the signed-in user, including Admin. POS entry, PIN verification, direct cart operations, drawer opening and transactions, and checkout enforce the schedule on the server. Checkout rechecks inside its transaction under the cashier lock, with current locking reads. Cancellation and sale completion serialize on that cashier. An expired/cancelled schedule triggers the closing modal through status polling; it never silently closes or discards an open drawer. Reconciliation, lock/unlock and closing remain available for safe shutdown.

Every fresh POS visit and full refresh consumes a new PIN verification. Successful verification gives one short-lived, single-use page grant. Category/search navigation uses a one-use navigation cookie bound to a server-generated session nonce; it rotates on each page, expires after 60 seconds once set, and carries no URL identifier. Normal category/search clicks do not repeatedly prompt. PIN verification has a persistent five-attempt per-minute limit. Drawer unlocking remains separate from entry PIN verification.

Install: `C:\xampp\php\php.exe tools/migrate-cashier-schedules.php --apply`. This adds only schedule tables and the new permission. It creates no schedules or financial records. **Assign schedules before cashiers enter POS.** Schedule enforcement defaults on; the existing internal setting `cashier_schedule_enforcement=0` supports controlled rollout/isolated legacy tests and is not exposed as a cashier bypass.

Validation: `php tools/tests/burger-integration.php` uses a disposable database; `node tools/tests/pos-drawer-ui-check.cjs` checks the actual modal in Chromium, including the schedule-expiry closing gate.

## Weekdays, attendance and unavailable dates

Use **Cashier Shifts > Cashier schedules**. Select a branch, then **Add cashier schedule**:
- **One specific date** uses a date and start/end time.
- **Recurring weekdays** uses selected weekdays and an effective date range of at most 366 days. Occurrences are generated immediately; no scheduler service is needed. End-on-following-day explicitly enables overnight shifts (maximum 24 hours).
- **Manage shift** changes only that occurrence and requires a reason. It becomes an exception that future series edits preserve.
- **Edit series** updates/cancels only future unmodified occurrences whose attendance is still scheduled. Started/past shifts, attendance records, and individual exceptions are preserved. Cancelling a series deliberately does not cancel an explicitly edited exception; use that occurrence's cancellation/block action when needed.
- **Attendance** records scheduled, present, absent, on leave, or completed. Absent/on-leave/completed deny POS use. No automatic absence inference is made from POS inactivity.
- **Block shift** blocks a specific occurrence, including after it is rescheduled and during its grace period. **Block unavailable dates** blocks whole local dates, inclusive of the final selected date, for absence, leave, rest day, suspension, or another reason. A date block applies at the current local time; an overnight shift stops when it enters a blocked date.
- **Restore availability** deactivates one block with a reason; other blocks and attendance restrictions still apply. Setting attendance back to present/scheduled does not remove separate blocks.

Calendar/list views support date, cashier, status, and existing branch filters. Non-managers see only their own records. Blocks have first priority over recurring/one-time schedules. PIN entry, direct POS endpoints, live status and transactional checkout use the same eligibility check. Schedule mutations serialize on the cashier's user row; checkout uses separate current locking reads of blocks and schedules so an older transaction snapshot cannot hide a committed block. Closing and reauthentication remain possible to reconcile an existing drawer.

Install with `php tools/migrate-cashier-schedule-planning.php --apply`. The script also ensures the original two schedule tables exist. The additive planning SQL is in `tools/migrations/20261016-cashier-schedule-planning.sql`.

### New tables in this extension

- `cashier_schedule_series`: weekly patterns, effective dates, local shift times, overnight flag, series state, and creator/updater.
- `cashier_schedule_blocks`: dated or occurrence-specific blocks, category, required reason, notes, active/restored state, and creator/updater.
- `cashier_schedule_events`: append-only application audit records linking occurrence/series/block, branch, cashier, actor, action, reason, before/after snapshots, and recorded timestamp.

The existing `cashier_schedules` table receives `series_id`, `occurrence_date`, `is_exception`, and `attendance_status`. Existing rows default to scheduled attendance and retain their dates, identifiers, branch, cashier, and cancellation state. Existing policy, financial shift, sale and inventory tables are not changed. Historical changes before installation are not reconstructed; new changes are audited.

Validation adds `tools/tests/cashier-planning-cases.php` to the disposable database integration suite and `node tools/tests/cashier-planning-ui-check.cjs` for the real Chromium calendar/modals/mobile workflow.

## Schedule interface and server-side audit table

Cashier choices display their assigned roles as badges in the searchable picker and selected value. All assigned roles are shown; role names are inserted as text and do not affect cashier eligibility or permissions.

Within **Cashier schedules**, use **Schedule planner**, **Recurring series**, **Availability blocks**, **Branch grace periods** (authorized managers only), and **Audit history** tabs. Active tabs have a solid blue background. The browser remembers the last selected subsection per branch. Outer branch/cashier filters also scope audit results; audit recorded-date filters are independent of roster dates.

Audit history uses the application's existing DataTables library in server-side mode through `ajax/cash_shift_audit.php`. The endpoint performs scoped counts, optional search/date filters, whitelisted sorting, and bounded SQL pagination (maximum 100 records/request). It browses the entire matching history rather than preloading the latest 200. Managers with `manage_cashier_schedules` see accessible branch records; other authorized cashier-module users see only their own history. Branch/role checks run on each request, including counts. Browser cells use DataTables' text renderer. No new tables or permissions are required.

The browser test uses the same DataTables version as the application (1.13.7); cache that test dependency in `scratch/datatables-1.13.7.min.js` before running `node tools/tests/cashier-planning-ui-check.cjs`.
