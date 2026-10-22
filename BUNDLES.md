# Fixed-price packages and bundles

## Implementation plan and result

1. Store package components separately from recipes in `product_combos`.
2. Configure included sellable products, whole quantities and preparation stations in the product modal's **Package items** tab.
3. Expand package sales into component inventory requirements, aggregate shared ingredients and lock the affected products during checkout.
4. Preserve sold component names and quantities in financial snapshots; show them in carts, receipts and preparation tickets.
5. Verify capacity, consumption, costing, retries, returns, branch scope and permissions with isolated database fixtures.

These steps are implemented. Packages use the existing `combo` product type and product permissions (`view_product`, `add_product`, `edit_product`); no new role grants are required.

## Setup and use

Run `php tools/migrate-product-bundles.php --apply` for each deployment database. The migration creates only the component relationship table and is safe to rerun. It has been applied to this local project database.

Create or edit a product, choose **Combo / Bundle**, enter its fixed selling price and open **Package items**. Add the component products and their quantities. For example, a package can contain three made-to-order items, two prepared items and one stocked item. A two-item package with a fixed price also supports a Twin Pack deal.

Components must be active and sellable, in the package's branch or shared across branches. Shared packages use shared components. When creating copies in multiple branches, choose shared components compatible with every copy. Nested packages, variant parents, raw ingredients and fractional item quantities are rejected.

The POS displays a package badge, a Packages filter and included quantities. Receipts preserve the contents at sale time. **Print preparation ticket** groups included quantities by station, with the parent package name underneath each item.

## Inventory and costs

Packages have no separate physical stock. Capacity is the lowest available component capacity after combining shared ingredient requirements. Made-to-order components consume raw ingredients; prepared components consume finished batches; simple products consume their own batches. Prepared products do not deduct raw materials a second time.

Checkout records actual consumed batch costs against the package sale item. New production batches now receive the actual consumed ingredient cost. Existing historical batches are unchanged. Editor costs and gross profit are estimates; missing stock uses recorded product costs, and shared package estimates need branch-specific stock to be accurate. Gross profit excludes operating expenses.

A void before preparation restores the original batches. Finished-stock returns are supported for packages containing only stocked or prepared components. Packages containing made-to-order items cannot return raw materials after preparation. Existing refund, authorization and audit requirements still apply.

Existing combos with physical stock must be reconciled before configuration. Converting an established ordinary product into a package, or the reverse, is rejected. A component used by an active package cannot be archived. Moving products between branches must preserve package component compatibility; this feature does not silently move the entire component graph.

Promotions continue to require explicit selection. Packages do not automatically apply discounts to unrelated cart items.

## Verification

Run `php tools/tests/burger-integration.php --bundles-only` for the focused package checks. Tests use a disposable database and leave source business data unchanged. Run the full integration harness separately for broader regression coverage.
