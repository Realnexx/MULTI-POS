# Branch purchasing

Access Suppliers, Purchase Orders and Purchasing Reports from the inventory sidebar.
An administrator can select a branch or view all branches; other staff are restricted
to the branch assigned to their user record. Suppliers are registered separately for
each branch so contact details and transactions do not cross branch boundaries.

## Permissions

The Roles and Permissions screen has a Purchasing group:

- `view_suppliers`: supplier directory and contact details.
- `manage_suppliers`: register, edit and archive suppliers.
- `view_purchase_orders`: orders, delivery details, returns and supplier purchase history.
- `create_purchase_orders`: create drafts, submit orders and cancel outstanding orders.
- `approve_purchase_orders`: approve or reject submitted orders; creators cannot approve their own orders.
- `receive_purchases`: inspect orders and confirm deliveries with actual costs.
- `return_purchases`: inspect orders, return goods and record supplier credit/replacement settlement.
- `view_purchase_reports`: supplier/branch reports and CSV export.

Action permissions also allow viewing the orders needed to perform that action.
Supplier contact management and aggregate reports require their separate permissions.
The additive migration grants newly introduced permissions to Admin/Superadmin roles.
Other roles must be assigned explicitly. Re-running synchronization preserves revoked
permissions. Existing sales and inventory permissions are not expanded to purchasing.

## Workflow

1. Register an active supplier in the destination branch.
2. Select that branch on Purchase Orders and create a draft using stocked products or
   ingredients in their base stock units. Finished recipe goods must use prepared-batch mode.
   Drafts preserve the supplier contact snapshot and agreed costs. Cancel and recreate
   an incorrect draft rather than changing an approved order's historical lines.
3. Submit the draft and have another authorized approver approve it.
4. Record each delivery with a unique reference for that order, delivery date, accepted
   and rejected quantities, actual unit costs, and optional lot/expiry details.
   Only accepted quantities affect stock. Rejected quantities require a reason and remain
   outstanding. Over-receiving is rejected. Multiple receipts complete a partial order.
5. Return goods from an original delivery with a supplier return reference and reason.
   Only stock still available in that original lot can be returned. Expired original lots
   can be returned, but sold or moved stock cannot be substituted from another lot.
6. Record supplier credit or replacement settlement. Settlement does not change stock;
   receive replacement goods under a new approved purchase order. A return does not
   automatically reopen its original order.

Unit costs support two decimals and quantities use the existing four-decimal stock math
(whole units for counted products). Enter unit costs inclusive of relevant discounts,
taxes and landed charges. Per-line totals round to cents. Partial return values use the
change in cumulative returned value so rounding cannot exceed the original delivery cost.
This module tracks purchasing costs and supplier credits, not payments or tax filings.

Stock changes, purchasing documents, events and activity logs commit together. Retry
tokens protect each action against double submissions. Orders serialize receiving and
returns to prevent simultaneous requests from exceeding quantities. Product ownership,
units and archiving are protected while purchase orders remain open. Cancelling a
partially received order keeps all earlier received stock and documents intact.

## Reports

Filter by branch, supplier and date range; export the same results as CSV. Ordered
amounts exclude drafts, rejected orders and cancelled orders. Receipt and supplier
return costs use their own document dates, including receipts on subsequently cancelled
orders. Net received cost equals receipt cost less supplier return value regardless of
whether a credit has been settled. Amounts are calculated from immutable document lines,
not current product prices. Supplier purchase history links to every delivery and return
on each order; the order list shows the latest 200 matching orders.

## Setup and validation

Run `C:\xampp\php\php.exe tools/migrate-purchasing.php --apply` from the project directory.
It adds purchasing tables and permission definitions without changing existing stock.
It is safe to rerun. Deploy the new source and apply this migration on each environment.
The page shows a setup notice until the tables exist.

Run `C:\xampp\php\php.exe tools/tests/burger-integration.php` for the integration suite.
It creates a unique disposable test database, applies migrations there and removes that
database afterward; it never migrates the source database. Purchasing cases cover
fractional receipts, partial/rejected deliveries, original-lot returns, actual costs,
rounding, role revocation, CSRF, branch boundaries, cancellation, concurrent receiving,
rollback and rendering, alongside the existing sales, recipe and transfer checks.
