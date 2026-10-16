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

**Reusable recipes for any product.** Open Inventory → Recipe Management (`?route=inventory/recipes`). Create a named recipe with any active raw ingredients and compatible units, or edit an existing product's recipe there. A new standalone definition is not sellable and has zero stock. Recipe saves and preview quantity changes never add, reserve or deduct inventory. Recipes can describe drinks, food, prepared goods or bundles; component amounts are per one unit sold. In Add New Product → Recipe, use Load a saved recipe to copy its ingredient rows, set the product price and Initial Stock, and save. The copied formula belongs to that product; future source-definition edits do not rewrite existing products or consumption history. No additional schema migration is required.

**Branch ingredients and global recipes.** Choose a branch first: ingredient options contain that branch's active ingredients plus shared ingredients. Changing the branch removes incompatible selections. Admin/Superadmin can select Global (All branches); such recipes are visible across branches and may reference shared ingredients only. This does not pool, copy or replenish physical stock: every branch retains its own batches. Stock-free standalone definitions can change scope, subject to the same validation. Branch staff can use global definitions but cannot modify them or create global definitions. Existing sellable products keep their original branch ownership.

**Opening finished stock (16 October 2026).** Add New Product → Recipe now defaults to finished-stock preparation. Configure ingredients for one unit sold, then enter Initial Stock under Pricing & Stock. The preview follows that quantity. Saving creates that many finished units and consumes the matching ingredients in one transaction; insufficient ingredients roll back the whole product save. Initial Stock 0 saves only the recipe. Entering positive stock selects prepared-batch mode automatically, so later sales consume finished stock without consuming the base recipe again. Inventory-management permission is required to create positive opening stock. Repeating the same form request cannot create another product or deduct ingredients twice.

For a B1T1 product, one stock unit represents one pair: configure two burgers' ingredients per unit. Initial Stock 10 then creates 10 pairs using ingredients for 20 burgers. Recipe edits never consume or return stock; use Prepare batch for additional finished units. Made-to-order mode with zero opening stock remains available for consumption at checkout.

**Ingredients first.** In Inventory & Catalog, choose **Add ingredient**, or add a product with type **Raw Ingredient**. Set its branch, base unit, cost, opening stock and thresholds. Raw ingredients are tracked and are not directly sellable by default. Use `pc` for buns/patties/slices/wrappers, `g` or `kg` for weight, and `ml` or `l` for volume. Counted units require whole quantities; weight and volume support four decimal places.

**Add a burger.** Choose **Recipe / Made-to-order**, set its selling price, and use the recipe tab to select ingredients. Quantities are **per burger**. Search filters help find ingredient options; the form shows an estimate from current ingredient costs. Compatible kg/g and l/ml conversions are handled on the server. Units cannot be changed after stock batches or recipe references exist; create a new item and reconcile explicitly instead of reinterpreting historical quantities.

Saving a burger or changing its recipe does **not** consume or return ingredients. Invalid recipes reject the entire product save. Duplicates, missing/inactive/non-tracked ingredients, self-reference, nested recipes, incompatible units, fractional counted ingredients and cross-branch ownership are rejected.

Choose one consumption mode:

- **Made to order:** checkout consumes the current per-burger ingredients multiplied by the quantity sold. Availability comes from the limiting ingredient at the selected branch, without requiring finished-burger stock. Catalog rows show “can make” capacity; inventory filters/counts use that same capacity. Physical valuation excludes this virtual capacity to avoid counting ingredients twice.
- **Prepared batch:** save the recipe/mode first. Reopen Edit → Recipe, enter a whole batch size and choose **Prepare batch**. Confirm actual preparation. Ingredients are consumed once and finished stock is created atomically. Checkout then consumes only finished units, plus any explicitly selected extra ingredients.

The same burger can occupy separate cart lines with different extras. Extra cheese, patties and other modifiers have explicit ingredient quantities/units and server-owned prices. A stale/removed modifier requires reloading the cart rather than trusting its client-supplied price.

Example per-burger recipe: one bun, one patty, one cheese slice, 15 g lettuce, 20 ml sauce and one wrapper. Selling two made-to-order burgers consumes two of each counted ingredient, 30 g lettuce and 40 ml sauce. Preparing ten uses ten recipes; selling two of those prepared burgers leaves eight without consuming the base ingredients again. These examples were used only in isolated tests, not added to the working database.

## Adjustments, waste and returns

**Whole-product branch reassignment.** Product actions → Move product ownership & all stock retains the earlier whole-product move under its own `reassign_products` permission. It moves ownership and all physical batches, preserves audit records, costs and expiry, and blocks incompatible recipe/add-on references, open orders and open stock transfer requests. It is separate from the request/dispatch/receiving workflow below. Branch staff remain limited to their assigned source branch.

The Add and Edit product Stock & Threshold controls display two decimals, matching the View summary. Ingredient calculations, batch balances and ledger quantities retain four-decimal precision.

Add/Edit product forms show inline field errors and open the tab containing the first invalid field, focus it and scroll it into view. Required product fields and recipe ingredient selections/quantities are checked before submitting; errors clear as corrected. Server failures remain visible in the modal, preserving values and selected images. Submissions show a spinner with Saving or Updating, prevent duplicate submission, restore controls on failure and stay busy through successful navigation.

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

## Manual stock adjustments for recipe products

Product permissions are listed under Inventory in Roles and Permissions. View Products covers the catalog, details, batch viewing, recipe viewing and barcode downloads. Add Products controls creation. Edit Products covers general changes, recipes, modifiers, variants, attributes and categories. Delete Products controls archiving. Print and Export Products controls CSV exports. Manage Stock covers manual adjustments, waste, stock returns and inventory reports.

Additional assignable controls are View Product Stock History, Edit Stock Batch Cost and Price, Prepare Recipe Stock, and Transfer Products Between Branches. Each action checks its own permission without additional Manage Stock or Edit Products requirements. Stock transfer access is permission-based for every role, including Manager. Staff can dispatch stock from their assigned branch to active destinations; receiving staff confirm arrivals in their own branch. Shared product records retain separate physical branch balances. Buttons and server endpoints check the same controls. Product helper reads and writes enforce branch ownership.

Run `php tools/seed-permissions.php --apply --inventory-actions` when deploying permission controls. It registers the permissions and preserves existing role access once; subsequent runs do not restore permissions revoked by an administrator. The stock transfer workflow also requires the migration below.

Adding recipe stock prepares the entered whole quantity using the product's saved ingredients. The same transaction consumes those ingredients in the selected physical branch and adds finished stock. Insufficient ingredients cancel the entire adjustment. Repeating the same request does not prepare it twice. Recipe management itself never consumes stock.

For made-to-order products, the adjustment requires confirmation to switch to prepared-stock sales, preventing checkout from consuming the ingredients again. Shared products require a global product manager for this mode change. The modal previews ingredient totals and records expiry when supplied. Removing or wasting finished stock only reduces the finished product: already-used ingredients are neither returned nor deducted again. No additional database tables are required.

## Inter-branch stock transfer workflow

Open Inventory → Stock Transfers (`inventory/transfers`), or Request stock transfer in a product's actions. Requests support multiple ingredient, ordinary product or prepared recipe items, quantities, source/destination branches and matching destination product records. Branches and products use searchable datalist suggestions. Destination records must already exist with the same normalized product name, type, ingredient flag and stock unit; a different product with the same unit cannot be substituted. A unique matching destination record is selected automatically. Free text and forged mismatched product IDs are rejected. Product ownership and recipe links stay unchanged. Made-to-order capacity and variant parent totals cannot be physically transferred.

Requests do not change or reserve stock. Approval is required by default; Settings → Security → Stock transfer approval can turn it off for future requests. When required, another authorized approver approves or rejects the request. The policy is captured on creation. Source staff confirm dispatch, which atomically deducts usable FIFO stock and records cost/price/expiry allocations. Destination staff enter actual quantities received. Receiving adds actual stock once, preserving source lot metadata. Prepared products never consume their base ingredients again during transfers.

Short, zero and excess receipts require discrepancy reasons; completed discrepancies remain visible separately. Excess receipts are explicit stock gains valued using the final dispatched lot, with recorded actual quantities and reasons. Requests may be cancelled before dispatch. Dispatched shipments cannot be cancelled; missing shipments must be received as zero with a documented loss. Every transition records the actor, time and notes in transfer history and existing activity logs. Retries cannot duplicate requests, dispatch deductions or receiving receipts. Failures roll back quantities, allocations, events and status together.

Pending, approved, in-transit, completed, discrepancy, rejected and cancelled records are filterable. Staff can view transfers involving their branch; only the source can approve/dispatch/cancel and only the destination can receive. The permission toggles are `transfer_products` (request/dispatch/cancel), `approve_stock_transfers`, `receive_stock_transfers`, and `view_stock_transfers` (read only). Each permission works independently of role names and unrelated inventory permissions. Approval still requires a separate actor when enabled.

Deploy using `php tools/migrate-stock-transfers.php --apply`, then the inventory permission seeder above. The idempotent migration adds `stock_transfers`, `stock_transfer_items`, `stock_transfer_allocations`, and `stock_transfer_events`; existing inventory operations, movements, batches and logs remain the stock ledger. It changes no existing stock. Branches with transfer history cannot be deleted, and products involved in open transfers cannot be archived or reassigned.
