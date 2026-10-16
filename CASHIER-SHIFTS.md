# Cashier shifts and cash drawers

The POS now also provides automatic opening, persistent locking, transactions and
counted closing dialogs. See [POS-DRAWER.md](POS-DRAWER.md). After that integration is
installed, POS checkout always requires an open, unlocked drawer; the optional policy
below continues to control other payment workflows such as online pickup collection.

Open **Cashier Shifts** in the sidebar or use **Manage shift** in the POS banner.
Shift selection uses an authenticated session, not an ID in the URL. Each request
rechecks role permissions, ownership and the user's current branch assignment.

## Initial setup

1. Select a branch and add at least one active register.
2. Assign the needed permissions in **Roles and Permissions → Cashier Shifts**.
3. Open the cashier's shift on the selected register and enter the physically counted
   opening float. Opening time, cashier, register and opening operator are recorded.
4. In **Configure**, enable mandatory open shifts once the branch is ready.

Mandatory shifts are initially **off** to preserve existing checkout during setup.
Even while optional, opening a shift automatically starts payment and refund tracking.
When mandatory shifts are on, POS checkout and online pickup payment collection require
an active shift in that branch. Online stock reservation alone does not move drawer cash.
Existing sales are never backfilled into newly opened shifts.

New shifts require independent manager approval by default. This policy is captured
when a shift opens; subsequent configuration changes do not bypass its closing review.
One cashier can have one active shift, and one register can have one active cashier.

## Permissions

- `view_own_shifts`: own shifts and printable closing summaries.
- `open_cash_shift`: open own shift, with POS permission.
- `close_cash_shift`: close own shift and enter counted cash.
- `cash_drawer_movements`: reasoned cash-in/out on own drawer.
- `view_cash_shifts`: monitor all shifts in accessible branches.
- `manage_cash_shifts`: operate on behalf of branch cashiers, add audited corrections
  to open drawers, and record manager recounts on pending closings.
- `approve_cash_shifts`: independently approve pending closings.
- `manage_registers`: branch registers and shift configuration.
- `view_shift_reports`: branch closing CSV reports and monitoring access.

On first installation, existing roles with POS access inherit own-shift viewing,
opening and closing. Cash movement, correction, review, register management and report
permissions are separate. Admin/Superadmin receive newly introduced permissions.
Subsequent synchronization does not restore permissions an administrator has revoked.
New custom roles should be explicitly assigned their required shift permissions.

## Cash calculations

Expected drawer cash is:

    opening float + cash sales + cash-in - cash-out - cash refunds +/- corrections

Sales use the final amount after discounts, not the amount tendered before change.
Electronic sales/refunds are tracked by their original payment method and excluded
from drawer cash. Money uses integer cents. Cash-out and cash refunds cannot make
the expected drawer balance negative; record an authorized cash-in first if necessary.

Refunds follow the POS's existing full-refund behavior. No payment-processor refund or
cash payout is executed by this module; it records the existing application refund.
Inventory restoration remains a separate controlled action. An already financially
refunded sale can have stock restored without creating a second drawer refund.

Refunds of shift-tracked sales require a currently open drawer. Late refunds debit the
current operator's active drawer in the sale's branch, preserving the original frozen
closing. Legacy unassigned sale refunds are recorded when paid from an active drawer;
optional setup without a drawer preserves legacy behavior.

## Closing, review and corrections

Count physical cash and choose **Count & close shift**. A nonzero overage/shortage
requires an explanation. Closing records expected cash, actual count, difference,
operator and timestamp, freezes its ledger, and releases cashier/register assignments.
The summary is either closed or awaiting manager review, depending on the opening policy.

An authorized manager other than the cashier or closing operator can review the shift.
A manager recount changes only the counted amount and difference, preserving the old
count and explanation in the audit events. Approval locks the summary. Approved or
unconditionally closed summaries cannot be edited or reopened through this module.
Open-drawer corrections are signed, reasoned ledger entries rather than edits to
existing entries. Register/policy changes also have audit records.

Active cashier accounts and registers cannot be archived. Branches with register/shift
history cannot be deleted. If staff assignments change during a shift, an authorized
manager in the original branch must close the drawer on their behalf.

Use **Print shift summary** for a single authorized shift. **Export closing report**
uses closing dates and branch filters and includes payment totals, refunds, movements,
opening cash, frozen expected/count/difference, status and explanation. The active-shift
screen shows the latest 200 accessible shifts; export is not restricted to that list.

## Setup and validation

Run `C:\xampp\php\php.exe tools/migrate-cash-shifts.php --apply` from the project root.
It creates additive tables and permissions without modifying existing sales or stock.
It is safe to rerun and preserves existing per-branch configuration.

Run `C:\xampp\php\php.exe tools/tests/burger-integration.php` for the disposable-database
integration suite. The shift cases include cash/electronic checkout and pickup payments,
refunds and retries, insufficient drawer cash, discrepancies, review/recounts, roles,
branch isolation, closing reports, transactional rollback, concurrent opening,
concurrent cash-out and checkout racing against closing. Existing sales, recipes,
inventory, transfers and purchasing cases remain in the same regression suite.
