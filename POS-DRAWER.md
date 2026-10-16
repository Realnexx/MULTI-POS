# Drawer workflow inside the POS

The POS opens a drawer dialog automatically after the normal PIN and branch screens.
No shift: select an available branch register and count the opening float.
Existing shift: review the current drawer and choose Continue to POS.
Locked shift: enter the assigned cashier's personal POS PIN or account password.
Normal menu/category navigation within the POS does not repeatedly reopen the overview.

The POS toolbar contains Drawer, Transactions, Lock drawer and Close POS. Drawer tabs
provide the opening balance, cash/electronic sales, expected cash, latest 200 ledger
transactions, cash-in/out and closing. Sensitive actions retain the existing role and
branch checks. The API selects only the authenticated cashier's current drawer; modal
operations include its ID in the POST body to reject stale tabs operating on a new shift.

POS cart edits and checkout require an open, unlocked drawer regardless of the older
optional branch policy. The same rule is rechecked inside the checkout transaction.
Other payment workflows still follow the branch's mandatory-shift setting. A locked
drawer also blocks online collection, refunds and management-page movements affecting
that cashier's drawer. A separate authorized manager can close another cashier's locked
shift through the management module; the cashier cannot close a locked drawer to bypass
reauthentication. Lock/unlock events are audited without storing submitted credentials.
Five invalid unlock attempts cause a persistent one-minute cooldown shared across tabs.

Close POS opens the physical count and discrepancy explanation form. In-app navigation
away from an active POS also opens that form. Closing preserves the shift ledger and
manager-approval policy, shows the frozen expected/count/difference summary, and offers
Print summary, Leave POS or Start another shift. An unpaid cart is retained; it is not
included in the closing cash balance.

Browsers cannot show an application cash-count dialog after their tab/window has closed.
Use Close POS for a completed financial closing. Browser unload uses the standard browser
confirmation and a best-effort lock beacon; it never invents a count or automatically
closes the shift. Refresh/return checks persistent lock state again. The server rejects
transactions from stale tabs even before their five-second status poll updates the screen.

Register setup and independent manager review remain available in Cashier Shifts.
Opening, locking/unlocking, counting/closing, movements and transaction viewing require
no navigation out of the POS. An active drawer must be closed before changing POS branches.

## Installation

Apply the base cash-shift migration first, then:

    C:\xampp\php\php.exe tools/migrate-pos-drawer.php --apply

The additive migration adds persistent lock records and the `lock_cash_drawer`
permission. Existing POS roles inherit this own-drawer permission once; revoked
permissions are not restored by synchronization. Existing sales, stock, shifts and
branch policies are not rewritten.

The integration suite uses `pos_drawer_integration=0` only while running legacy fixtures,
then enables the feature for dedicated POS drawer endpoint cases. The production default
is enabled after its table is installed. Run the full disposable-database regression suite:

    C:\xampp\php\php.exe tools/tests/burger-integration.php

After that suite, run `node tools/tests/pos-drawer-ui-check.cjs` for the existing local
Playwright/Chromium browser harness. It exercises actual native dialogs and forms with
mocked drawer responses, including stale polling and network failure. Server integration
tests separately cover authentication, money, transactions, locks and branch permissions.

## Starting another shift

The opening modal separates register/cash selection from a previous-count summary and adapts to small screens. Select a register, then choose **Enter a new counted amount** or **Use previous counted drawer cash**. The latter is offered only when the latest closed shift in the same register and branch belongs to the current cashier. Its actual counted cash is a reference, not a live balance; electronic payments and expected cash are not carried forward.

Reusing cash requires confirmation that it was physically counted and remains in the drawer. The opening transaction validates the source shift, cashier, branch, register, latest closing reference and amount under the register lock. It records the source in the opening audit and retains idempotent retry handling. Another cashier's closing figures are not exposed through this option. A fresh count can always be entered if cash has been removed or added. No automatic cash transfer or prior-ledger change occurs.
