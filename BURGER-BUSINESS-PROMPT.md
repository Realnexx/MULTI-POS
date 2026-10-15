# Adapt MULTI-POS for burger ingredients and recipe inventory

You are a senior PHP/MySQL developer experienced with restaurant POS systems and inventory accounting. Inspect my existing MULTI-POS project at `C:\xampp\htdocs\pos` and implement a reliable ingredient-first workflow for a burger business. Preserve the current application, existing work, permissions, branches, receipts, reports, and deployment configuration. Do not rebuild it or introduce a new framework.

## Business workflow

1. Admin creates ingredients first: buns, patties, cheese slices, lettuce, sauce, and packaging. Each ingredient has a stock unit, opening stock, cost, branch, and reorder threshold. Ingredients normally are not directly sellable.
2. Admin adds a burger product, sets its selling price, and selects existing ingredients with quantities **per burger** in the creation form. Editing must load and save those same recipe rows consistently.
3. A cheeseburger might use 1 bun, 1 patty, 1 cheese slice, 15 g lettuce, 20 ml sauce, and 1 wrapper. This is an acceptance-test recipe, not production data to insert automatically.
4. Inventory consumption must happen at one explicit business event, never as an accidental side effect of saving a recipe.

### Distinguish recipe setup, production, and sales

Creating a menu item defines its recipe. It must not silently consume ingredients just because the admin saved the form.

Support these clearly distinguished workflows if needed:

- **Made to order:** saving the burger recipe does not change stock. Completing a sale deducts recipe quantities multiplied by the quantity sold. The burger's availability is computed from its ingredients, without requiring artificial finished-burger stock.
- **Prepared batch:** an explicit “Prepare batch” operation consumes ingredients and creates a specified quantity of finished burgers. Selling those burgers deducts finished stock only. Never deduct their ingredients again at checkout.

Default new burger recipes to made to order unless the owner selects batch preparation. If an admin enters “prepare 10 now” while adding a product, make it a distinct, explicit production operation with a clear stock-consumption confirmation and audit reference. Recipe definition and production must remain distinguishable in the backend and logs even if the UI offers them together.

Do not reinterpret existing recipe products automatically. Existing behavior suggests some products may already have had ingredients consumed at recipe-save time. Provide a reviewed reconciliation/migration plan before switching their consumption mode, or historical stock could be deducted twice.

## Existing code and verified issues to inspect

- `views/modals/createProductModal.php`, `views/modals/editProductModal.php`, and `views/modules/products.php` already contain product types, ingredient flags, recipe rows, quantities, units, and modifier controls. Improve these instead of replacing them.
- `controllers/InventoryController.php` already calls `Product::save_recipes()` while creating/editing recipe products and records initial finished stock. Preserve normal simple-product workflows while correcting recipe behavior.
- `models/Product.php` provides `get_all_ingredients()`, `get_recipes()`, `save_recipes()`, `check_recipe_stock()`, `adjust_stock()`, and `deduct_stock_fifo()`.
- `ajax/inventory_action.php` has a separate `save_product_recipe` path which returns old ingredients and consumes new ingredients according to the recipe product's current stock. This differs from the regular form-save path. Consolidate recipe persistence so neither path changes inventory merely by editing a recipe.
- `models/Sale.php::create_sale()` currently deducts the sold product through FIFO and separately consumes modifier ingredients. It does not expand the burger's base recipe at checkout. Add consumption-mode-aware processing.
- Modifier consumption currently assumes one ingredient unit per sold item. Support explicit quantities and units for extra cheese, extra patties, and similar additions; verify allowed modifiers and their prices from server-side records.
- `Product::deduct_stock_fifo()` can return success despite insufficient batches, clamps the cached total to zero, and does not lock selected batches. It also casts/binds quantities as integers. Correct shortage handling, transaction ownership, concurrency, and decimal precision.
- `check_recipe_stock()` reads a global product stock value without a branch argument. Make availability branch-specific and based on the same stock source used for actual deductions.
- `ajax/pos_cart.php` accepts stock/price information into the cart and uses a stored stock limit. Revalidate active products, authoritative pricing, branch permissions, and inventory on the server at checkout.
- Inspect the actual database schema before changing it. UI decimal inputs and decimal recipe quantities do not prove that batches, logs, product totals, and every write path support fractions.
- Inspect cancellation/refund/status-change behavior. Do not assume stock reversal already exists or should happen for every status change.

## Required implementation behavior

### Admin ingredient and recipe setup

- Provide a clear ingredient-first path within the existing admin navigation.
- Ingredient forms should capture sensible base units such as pieces, grams, and milliliters, costs, branch stock and thresholds.
- Burger creation/editing should show searchable ingredient selection, per-item quantity, unit, and estimated recipe cost.
- Explain that recipe quantity is per burger; show a useful consumption preview.
- Provide an actionable empty state when no ingredients exist.
- Reject missing/deleted/non-ingredient references, duplicates, zero/negative quantities, unauthorized branch references, self-reference, and unsupported nested recipes. Either reject cycles or implement safe explicit recursion; do not allow silent infinite expansion.
- Saving, removing, or changing recipe rows should affect future consumption only. It must not invent stock returns for already prepared/sold burgers.
- Preserve recipe and ingredient snapshots or equivalent immutable consumption records so future edits cannot corrupt historical reports or reversals.

### Units and availability

- Use consistent fixed-precision decimal quantities end to end, including relevant schema columns, bindings, calculations, inventory logs, and UI. Do not truncate fractional usage.
- Define base-unit conversions explicitly: for example, 1 kg = 1000 g and 1 L = 1000 ml. Reject incompatible conversions such as pieces to grams without an explicit product-specific rule.
- Counted stock remains whole units where appropriate. Do not require fractional pieces merely because weight/volume stock supports decimals.
- Compute made-to-order availability from the limiting ingredient at the selected branch. Aggregate shared ingredients across every cart line and modifier before checking stock.
- Show shortages by ingredient and required/available quantities. An availability preview does not replace checkout validation.

### Atomic inventory changes

- One transaction owner should control a sale/production operation. Helper functions must not commit an outer transaction independently.
- Lock relevant stock rows/batches in a consistent order, recheck availability, then deduct. Roll back the whole operation on any shortage or persistence failure.
- Never record a successful sale/production with partial deductions, negative batches, or a silently clamped shortage.
- Ensure duplicate submissions/retries cannot record the same sale or production twice.
- Preserve FIFO batch allocation and branch isolation. Make cached totals consistent with the authoritative branch/batch balances.
- Record each consumption against its sale item or production batch, ingredient, branch, allocated inventory batch, quantity, user, and timestamp.
- Keep extra modifiers separate from the base recipe to avoid charging/deducting twice. Optional omissions should reduce consumption only where explicitly supported and validated by the server.

### Production and returns

- Batch preparation must validate ingredient availability, consume the recipe, and increase finished stock atomically. Recipe edits must not retroactively recalculate completed production.
- Sale cancellation/refund is not automatically a return of usable ingredients. Define a deliberate policy distinguishing a void before preparation, a return of usable finished stock, and prepared-food waste.
- Any permitted reversal must use recorded original movements and occur at most once. Never reconstruct old consumption using today's edited recipe.
- Add explicit spoilage/waste adjustments for discarded ingredients or prepared food, with permission checks and audit logs.

## Safety and scope

Read repository instructions and preserve unrelated modified/untracked files. Keep existing PHP/MySQLi architecture. Use prepared statements, existing authentication, CSRF protection, appropriate permissions and branch authorization for all new/read/write endpoints.

Do not run schema/data changes during requests or GitHub deployment. Provide reviewed manual migrations and a rollback/reconciliation plan. Do not seed demo ingredients, change live stock, or create test sales in production. Do not commit secrets or modify the deployment's upload/config protection.

## Acceptance tests

Use an isolated test database and test the complete admin-to-checkout flow:

1. Create ingredient stock, then create a burger with its recipe. Saving leaves all ingredient balances unchanged.
2. Sell two made-to-order example cheeseburgers: consume 2 buns, 2 patties, 2 cheese slices, 30 g lettuce, 40 ml sauce, and 2 wrappers exactly once.
3. Prepare 10 burgers in batch mode: consume the recipe for 10 and create 10 finished units. Selling 2 leaves 8 finished units and consumes no additional base ingredients.
4. Test extra cheese/patties, shared ingredients across different burgers, unit conversion, decimal precision, insufficient stock, expired/inactive batches, and branch isolation.
5. Race two checkouts for the last available ingredient. Only stock-supported orders succeed; failed orders leave no partial sales or movements.
6. Retry the same checkout/production request and confirm no duplicate consumption.
7. Edit a recipe after sales/production and confirm historical balances and consumption records do not change.
8. Test permitted void/reversal once, repeated reversal rejection, prepared-food waste, and rollback on a simulated write failure.
9. Verify existing simple products, variants, roles, receipts, reports, POS navigation and deployment checks still work.

Before implementation, present the existing call flow and the chosen inventory ownership rules. Then implement the coherent change, run relevant tests, and report changed files, migration/reconciliation requirements, test results, and remaining limitations. Do not claim live readiness based on syntax checks alone.
