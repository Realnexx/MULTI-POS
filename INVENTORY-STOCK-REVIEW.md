# Inventory filters and recipe stock review

Filters on inventory/products now restore before the first DataTables request. Branch, category, stock level, search, sort and pagination are saved per user and installation path. Explicit URL filters take precedence; changing filters updates the URL immediately. Reset clears the filter/search selection and starts at the first page. Server-side branch authorization remains authoritative, including when roles or assignments change.

Recipe rows offer Ingredients & capacity. The read-only modal shows normalized ingredient quantity per finished unit, usable ingredient stock, limiting ingredients and capacity by branch. Made-to-order products show units that can be made. Prepared products distinguish ready finished stock from additional ingredient-based preparation capacity. Shared ingredient estimates cannot all be realized simultaneously across different recipes; extras may reduce achievable quantities. Viewing estimates never changes stock.

Corrections:
- Stock sorting uses calculated quantities rather than product IDs.
- Variant rows show the authoritative product stock ledger rather than the unrelated editable variant stock total. This does not implement a new independent variant inventory ledger.
- Untracked products are distinguished from out-of-stock products.
- Branch stock breakdown respects the selected branch.
- Batch expiry checks use the Philippine date consistently in availability, deductions, catalog/branch stock, stock transfer dispatch, expiry reconciliation and dashboard stock queries.
- Invalid ingredient units result in an explained zero-capacity configuration problem rather than crashing the capacity display.

Verification: 14 isolated stock/capacity checks, 5 filter persistence checks, and all 632 existing integration checks passed. Browser testing with synthetic DataTables responses verified Low Stock and its matching rows persist after reload. Tests confirmed unit conversion, limiting ingredients, exact made-to-order deductions, retry idempotency, shortage rollback, prepared-stock deductions without double ingredient consumption, branch isolation and expiry behavior under a different database timezone. Source business records were not modified or reconciled automatically.

Commands:
- php tools/tests/burger-integration.php --stock-review-only
- node tools/tests/inventory-filter-state.cjs
- php tools/tests/burger-integration.php
