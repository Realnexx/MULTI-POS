# Online cart, pickup orders and stock

Customers use the website cart, choose an active pickup branch, check availability, and sign in to place an order. Cart changes are saved to the database. Guest carts are merged into the account at sign-in; guest reads cannot access customer-owned rows. Checkout captures the customer name, email, contact number, branch, product names, quantities and prices.

Each order receives an unguessable `WEB-...` tracking number. Customers can see their orders under Track order (or Account > Track your order) and open the tracking page. Tracking is private to the signed-in owner. Branch staff use Admin > Online Orders, also linked from the POS header. Staff can find older records by their exact tracking number.

## Stock rules

- Made-to-order recipes consume their required ingredients once when the online order is placed.
- Prepared-batch recipes consume finished burger stock only. Ingredients were already consumed by Prepare batch and are not deducted again.
- Tracked products without recipes consume their own finished stock once. Products intentionally configured as unlimited or non-stock retain that setting.
- Adding or updating a cart does not consume inventory.
- Online checkout and ordinary POS checkout share the branch's inventory ledger and product locks. Neither can oversell stock already consumed by the other channel.

Placing an online order reserves stock immediately and saves the order as Pending / Unpaid. It does not add paid revenue. The order progresses from Pending to Preparing, Ready, and Completed. Staff with both Manage Online Orders and POS permission use Mark paid after collecting payment at the counter. This creates one completed Online sale and its receipt items immediately, even while fulfillment is Pending, Preparing or Ready. Pickup can be completed only after payment. The sale is linked to the original order and stock movements. This step never consumes stock again. Staff must finish an existing website order through the Online Orders queue instead of ringing it as a new, unrelated POS sale.

Unpaid pending cancellation requires Manage Online Orders plus refund or inventory permission. Paid orders must be refunded through Order History. It restores the exact reserved quantities to the original batches once. Expired batches remain unavailable. Cancellation after preparation is blocked so cooked food cannot silently become raw ingredients again. Use the existing financial refund and inventory/waste policies after preparation. Explicit supported sale reversals use the original movement history, and refunds are reflected in tracking.

## Integrity and traceability

Checkout, order snapshots, stock consumption, and cart clearing form one database transaction. The server calculates all prices and recipe requirements. A changed cart or price requires review. Combined ingredient needs are checked after product locks; failures roll back and preserve the cart. Persistent checkout tokens make retries return the original order without duplicate stock deductions. Cart owner locks prevent simultaneous sessions from checking out the same account cart twice.

Order status and payment completion use locked rows and validated transitions. Repeated completion does not create another sale. Staff access is permission checked and restricted to their assigned branch; Admin and Superadmin can work across branches. Writes require POST and CSRF validation. Checkout requires an authenticated account and is rate limited to ten attempts per account per hour.

Tables: online_orders, online_order_items, online_order_events, online_cart_owners. Existing inventory_operations, inventory_movements, inventory_logs, sales, and sales_items provide stock and financial traceability. Historical order item names, prices, recipe mode, and component requirements are saved separately from the editable product catalog.

## Installation and operation

Run `php tools/migrate-online-orders.php` once per database before deploying this runtime. The migration has been applied locally. It only creates the new order/cart-lock tables and does not modify existing stock, products or sales. Database initialization also includes these tables. Existing runtime deployment packaging includes the new models, controller, and views; no production deployment was performed.

Settings > General > Online ordering enables or disables new checkout (enabled by default). Closing ordering preserves existing tracking, fulfillment and payment operations. Customers choose Pay at counter; staff record the actual method received. The Kanban board groups New, Preparing, Ready and Picked up orders, with cancelled/refunded records below. Manage Online Orders controls board access and fulfillment, POS permission additionally controls payment, and refund/inventory permission controls unpaid cancellation. Admin/Superadmin have full permissions; Cashier and Manager receive the order permission from the idempotent seeder. Fulfillment is pickup with payment at the counter. No card payment gateway or external payment authorization is implied. Pending orders hold inventory until fulfilled or cancelled by authorized staff; staff should review their active queue regularly. The queue prioritizes active orders and shows the latest 200 relevant records; exact tracking-number lookup remains available for older orders.

## Verification

Tests use an isolated schema clone. They verify recipe and prepared-batch deductions, simple-product stock, retries, payment completion, cancellation, insufficient-stock rollback, shared ingredient shortages, guest/account cart isolation, stale quotes, customer ownership, branch restrictions, CSRF, method restrictions, and end-to-end browser checkout/tracking. A simultaneous online/POS purchase of the last unit permits exactly one winner. Existing RBAC checks also pass. Test inventory and orders are never written to the live database.
