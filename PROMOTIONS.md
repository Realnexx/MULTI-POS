# Promotions and discounts

Open `?route=admin/promotions` under Promos & Discounts. Global administrators create drafts, configure rules, then activate them. Pause an offer before editing its rules. Archive preserves sales history and is permanent; archived codes cannot be reused.

## Rules

- Fixed PHP or percentage discounts; optional maximum discount and minimum cart subtotal.
- Philippine-time schedule: start inclusive, end exclusive.
- POS, online, or both. Blank code creates an offer that must be explicitly selected. A blank checkout code applies no promotion. Codes are case-insensitive.
- Empty branch selection means all branches. Product/category selections use OR eligibility; empty selections cover all discountable products. Non-discountable items never qualify.
- One discount per transaction. Select an offer or enter its code. Manual discounts cannot stack with selected offers or codes.
- Global discount enable/disable and maximum percentage still apply, including supervisor overrides. Tax and service charges use the discounted subtotal according to existing settings.
- Per-customer limits require an identified registered customer. POS cashiers identify customers by email. Anonymous POS sales cannot use customer-limited offers. This is staff identification, not customer identity verification.

## Usage lifecycle and concurrency

POS checks rules against server prices and locks promotion rows in the sale transaction. A failed sale rolls back its use. Online orders reserve uses together with stock and the immutable quoted total. Verified payment or counter payment completes that reservation; checkout retries cannot duplicate it.

An expired/failed payment session leaves the order payable, so its reservation stays held. Cancel an unpaid pending order in Online Orders to release both stock and promo usage. Reservations intentionally have no independent timer: releasing one while its order remains payable could oversubscribe a limited offer. Paid/refunded uses continue counting against limits to prevent refund/redeem abuse. Existing order totals are honored after a promotion is paused, edited, or expires.

## Manual approvals

The initial cashier limit is 10% of eligible item value. Global approvers can configure it on the management page. Applying a manual discount requires `apply_manual_discounts`. Above-limit discounts require an independent supervisor request approved in the inbox; approvers can apply their own overrides under their explicit permission. Approval is bound to the current branch, products, quantities, prices, modifiers and discount, expires after 30 minutes, and is consumed with the sale. Cashiers click Apply beside their proposed manual discount once approved; the saved amount is restored when the cart reloads. The global financial discount cap cannot be overridden.

## Permissions

`view_promotions`, `create_promotions`, `edit_promotions`, `activate_promotions`, `apply_manual_discounts`, `approve_discount_overrides`, `view_promotion_reports`.

New permissions are granted to Admin/Superadmin once. Existing POS roles receive within-limit manual discount access once. Grant supervisor permissions to additional roles through Roles & Permissions. Re-running installers does not restore revoked grants. Global promotion management requires both the action permission and all-branch access. Branch staff see only offers for their branch, approvals in their branch, and their branch report data.

## History and reports

Receipts and online financial snapshots retain the applied promotion and discount. Redemptions track reserved, completed, released, and refunded activity. Reports filter by date and branch access and separate lifecycle states. Sales after discount exclude tax and service charge. Estimated gross profit subtracts recorded stock consumption costs, excludes operating expenses, and may be overstated when item costs are missing. Promotion administration, approval actions and manual discount applications are audited.

## Deployment

Local schema and initial grants were installed. No live promotions were created.

On another server, upload changed application files and run `tools/migrate-promotions.php --apply` through PHP CLI, or import `tools/migrations/20261021-promotions.sql` in phpMyAdmin after selecting the existing application database. The SQL needs no temporary table privileges. Tables: `promotions`, `promotion_targets`, `promotion_redemptions`, `discount_approvals`. Back up the hosting database first using its normal export workflow.

Validation: 77 isolated promotion checks, 9 POS interface regression checks, 65 system-settings checks, and all 632 existing integration checks passed. The management page and modal were also inspected in the browser.

Focused checks: `php tools/tests/burger-integration.php --promotions-only`. Tests use a unique disposable database and do not modify source business records.

Bundle/BOGO offers, loyalty rewards and statutory senior/PWD discount calculations remain outside this fixed/percentage promotion implementation. They need separate inventory rules or confirmed statutory treatment before being enabled.
