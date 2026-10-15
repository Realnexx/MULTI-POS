# Burger ingredients and inventory

The ingredient-first workflow is implemented. **Apply the reviewed manual migration before using the updated inventory or checkout.** The working database has not been migrated, and no working stock or sales records were changed by implementation/testing.

## Enable safely

1. Back up the intended database, media and application files. Test restoration, and schedule a maintenance window for the schema/code transition.
2. Review `tools/migrations/20261015-burger-inventory.sql`. It adds recipe modes, four-decimal inventory quantities, modifier quantities/units, request deduplication and movement history. It does not seed products, deduct stock, or automatically classify old recipes.
3. Apply that SQL **once** through your database administration tool against the intended database. On shared hosting, use the host's private phpMyAdmin/import tools; do not upload a public migration endpoint. The workflow never imports it. DDL can commit implicitly: if an import stops halfway, inspect the actual schema and resume only the missing reviewed statements rather than rerunning blindly.
4. Reconcile existing recipes before changing their mode. Prior recipe-save behavior may already have consumed ingredients. Compare a physical count with ingredient batches, finished-product batches and logs. Correct discrepancies through explicit, audited stock adjustments. Never assume that existing ingredients are still unused. Also compare legacy stock caches against actual active batches: positive cached stock without usable batches is not treated as available inventory. Establish any missing batches only from a reviewed physical count; do not fabricate stock from a stale cache.
5. Edit each existing recipe, choose its intended mode, and tick the stock-review confirmation only after reconciling. Existing recipes remain `legacy` and cannot be sold until reviewed. Switching to made to order requires resolving every remaining finished batch, including expired stock.
6. Verify the admin and POS flows on a restored test database/staging site before taking real orders. Keep backups and all movement history.

The product page displays a setup notice while migration tables/columns are missing. This is expected until the manual step is completed. Basic stock operations also need the new movement tables; do not mix old and new inventory writers during rollout.

## Daily workflow

**Ingredients first.** In Inventory & Catalog, choose **Add ingredient**, or add a product with type **Raw Ingredient**. Set its branch, base unit, cost, opening stock and thresholds. Raw ingredients are tracked and are not directly sellable by default. Use `pc` for buns/patties/slices/wrappers, `g` or `kg` for weight, and `ml` or `l` for volume. Counted units require whole quantities; weight and volume support four decimal places.

**Add a burger.** Choose **Recipe / Made-to-order**, set its selling price, and use the recipe tab to select ingredients. Quantities are **per burger**. Search filters help find ingredient options; the form shows an estimate from current ingredient costs. Compatible kg/g and l/ml conversions are handled on the server. Units cannot be changed after stock batches or recipe references exist; create a new item and reconcile explicitly instead of reinterpreting historical quantities.

Saving a burger or changing its recipe does **not** consume or return ingredients. Invalid recipes reject the entire product save. Duplicates, missing/inactive/non-tracked ingredients, self-reference, nested recipes, incompatible units, fractional counted ingredients and cross-branch ownership are rejected.

Choose one consumption mode:

- **Made to order:** checkout consumes the current per-burger ingredients multiplied by the quantity sold. Availability comes from the limiting ingredient at the selected branch, without requiring finished-burger stock. Catalog rows show “can make” capacity; inventory filters/counts use that same capacity. Physical valuation excludes this virtual capacity to avoid counting ingredients twice.
- **Prepared batch:** save the recipe/mode first. Reopen Edit → Recipe, enter a whole batch size and choose **Prepare batch**. Confirm actual preparation. Ingredients are consumed once and finished stock is created atomically. Checkout then consumes only finished units, plus any explicitly selected extra ingredients.

The same burger can occupy separate cart lines with different extras. Extra cheese, patties and other modifiers have explicit ingredient quantities/units and server-owned prices. A stale/removed modifier requires reloading the cart rather than trusting its client-supplied price.

Example per-burger recipe: one bun, one patty, one cheese slice, 15 g lettuce, 20 ml sauce and one wrapper. Selling two made-to-order burgers consumes two of each counted ingredient, 30 g lettuce and 40 ml sauce. Preparing ten uses ten recipes; selling two of those prepared burgers leaves eight without consuming the base ingredients again. These examples were used only in isolated tests, not added to the working database.

## Adjustments, waste and returns

Use stock adjustments for physical quantity changes. Product editing and batch-price editing cannot overwrite stock balances. Batch quantities are displayed read-only in the price editor. Stock additions create receipt batches; FIFO allocations, decimals and branch balances are maintained by the shared transaction service.

Use **Waste / Spoilage** for stock physically discarded. Expired batches are unavailable for sales but can be recorded as waste. An ordinary refund changes payment/sale status without inventing usable ingredients.

Inventory managers also have **Return stock from a recorded sale**:

- **Voided before preparation:** available for made-to-order sales only, after confirming that ingredients were not actually used. Restores the originally recorded ingredients, not today's edited recipe.
- **Usable finished goods returned:** restores the original finished-product batches for stock-based sales. Consumed add-on ingredients remain consumed. Made-to-order food is not converted back into raw ingredients.

Returns are whole-sale operations; mixed consumption modes may need separate operational reconciliation. Partial-item returns are not implemented. Missing/expired original batches or older sales without movement history require manual reconciliation instead of fabricated returns. Repeating the same return request is safe; a second distinct return for the same sale is rejected. Returned sales are marked Refunded. Prepared-food waste is not automatically returned to stock.

Referenced ingredients cannot be archived until active recipe/modifier references are removed. Product removal archives the product instead of deleting historical receipts or stock records.

## Inventory ownership and safeguards

`models/RecipeInventory.php` owns transactions and product/batch locking. Recipe, modifier and variant-save helpers join the caller's transaction instead of committing it. Checkout re-reads locked product/recipe/modifier data, aggregates shared ingredients, checks branch availability, allocates FIFO batches, and saves the sale and movements together. Shortage or write failure rolls everything back. A changed recipe introducing unlocked ingredients causes a safe reload/retry request.

`models/InventoryQuantity.php` represents stock with scaled integers at four decimal places. SQL stock, batch, recipe and log columns retain the corresponding decimal precision. Money is validated at two decimal places. No request silently clamps a stock shortage to zero.

Sales, preparation and stock returns use request tokens. The same operation cannot consume stock again when a request is retried. Existing authentication, CSRF and branch checks protect the new write operations; preparing batches and returning stock require inventory-management permission.

The new operation snapshots and movements record product names, units, recipe/extra details, original batch IDs, unit costs, actor, branch and sale-item associations. Later recipe edits do not recalculate historical consumption. Keep these records with your backups.

## Files changed

- New inventory/quantity services under `models/` and manual migration under `tools/migrations/`.
- Product and sale models route recipe/stock/checkout changes through the shared service.
- Inventory/POS controllers and AJAX handlers validate recipe modes, quantities, authoritative prices and branch scope.
- Existing product modals and catalog scripts now expose ingredient setup, recipe modes, batch preparation, modifier quantities, precision, cost preview, stock-return controls and waste.
- Stock notification comparisons retain fractional balances.
- `tools/tests/burger-integration.php` exercises isolated database transactions and the actual admin/cart handlers. Database-free quantity checks join the existing deployment tests.

The existing deployment protections remain in place. Runtime packaging includes the new PHP/JS automatically; migration SQL and test tools are excluded from deployment. No commits, pushes or deployments were performed.

## Verification

Run from the project root on local XAMPP:

```powershell
$env:PHP_BINARY = 'C:\xampp\php\php.exe'
python -m unittest discover -s tools/tests -v
C:\xampp\php\php.exe tools/tests/burger-integration.php
python tools/prepare-deploy.py C:\xampp\php\php.exe
node --check assets/js/main.js
node --check assets/js/recipe-admin.js
```

The integration runner requires local credentials that may create/drop a temporary database. It clones the source **schema only**, preserves its foreign-key definitions, and uses only generated test users/products. Its database names have a strict `pos_burger_test_` prefix and random suffix. Test databases are dropped in cleanup; private copied configuration is removed. It does not copy business records or migrate the source database. Do not run it using a live hosting account.

Verification passed: **50 isolated database/endpoint checks**, **five database-free configuration/precision tests**, PHP syntax validation for the **436-file runtime package**, standalone JavaScript syntax checks, and syntax checks on the rendered admin page's inline JavaScript. Scenarios include ingredient-first admin creation, recipe creation/editing without stock changes, all-or-nothing invalid edits, fractional quantities and conversions, modifier consumption, ordinary stocked products, variant helper rollback, fractional ingredient alerts, expired batches/waste, production rollback, branch isolation, legacy-mode blocking, historical returns, injected write failure, actual cart checkout/retry and two concurrent last-stock checkouts. The admin page rendered without PHP warnings. Local tests use PHP 7.4; the deployment workflow separately validates PHP 8.2 when run.

Visual browser QA and live-host verification remain necessary. This change does not introduce nested recipes, optional ingredient omissions, variant-specific burger recipes, kitchen-order tracking, or partial returns. The existing variant catalog editor is retained; those additional restaurant features need their own requirements and tests.

## Recovery

If code needs to be reverted, first stop new inventory writes and back up the current database. Reverting code alone does not reverse preparation/sales or make the old recipe-save consumption behavior safe. Restore/reconcile application code, schema and business records as a coordinated recovery. Do not delete movement history or narrow decimal columns to integers during an automatic rollback. Use the separately documented deployment `git revert` process only after reviewing this database compatibility requirement.
