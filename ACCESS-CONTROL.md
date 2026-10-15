# Access control

Permissions are defined in `config/permissions.php`. Protected pages and AJAX actions are checked in `middleware/access_policy.php`; hiding buttons does not replace server checks. Writes require POST and a valid CSRF token. API denials return JSON; page denials show a responsive access notice.

Superadmin has every permission, including future permissions. Its role cannot be deleted, renamed, or reduced. Only Superadmin can manage Superadmin accounts, and the last active Superadmin cannot be removed or demoted. These accounts are excluded from the users table. Other account and role managers cannot grant permissions they do not hold or manage accounts with higher access.

Run `php tools/seed-permissions.php --apply` to seed missing permissions safely. Seeding is idempotent and preserves existing assignments. Admin receives the canonical administration permissions. Cashier retains existing grants and POS access; refunds require `refund_sales`. Manager receives an operational preset only when it has no grants. Customers receive no administration permissions.

## Sign-in and branch access

All accounts sign in at `?route=login` using username/email and password. The old customer login URL redirects here. Posted role and branch fields are ignored; authentication never changes branch assignments. Sessions are regenerated after login, and customer guest carts are migrated to their account.

Customers return to the website. Staff enter the admin workspace: the dashboard when permitted, otherwise a staff home page with available actions. Staff without an active assigned branch see a setup notice and cannot read or change branch data.

Only the named Admin and Superadmin roles have access across branches. They can select a POS terminal branch. All other staff use their saved branch automatically, refreshed from the database on protected requests. A page permission alone does not grant access to other branches. Branch management requires its permission and an Admin or Superadmin role.

Assign staff through Branches, then edit a branch and choose its staff. Custom staff roles are included. Existing live assignments are preserved by this update.

## Verification

The isolated test fixture clones schema and permission metadata into a disposable database. It does not mutate production accounts. Tests cover server authorization, privilege escalation, protected roles, CSRF, page rendering, login routing, session renewal, and branch restrictions. Remove the fixture with `php tools/tests/rbac-fixture.php cleanup` after stopping its local test server.
