# Online Orders permissions

Configure roles under **Roles & Permissions > Online Orders**.

- `manage_online_orders`: View Online Orders in accessible branches. Controls the page, sidebar, POS links, and staff order reads. The existing permission name is retained so previously revoked page access stays revoked.
- `prepare_online_orders`: Start preparing and mark ready for pickup.
- `complete_online_orders`: Complete pickup after payment.
- `record_online_order_payments`: Record payment; also requires existing `access_pos` authorization and applicable drawer rules.
- `cancel_online_orders`: Cancel an unpaid pending order and release its reservations; also requires `refund_sales` or `manage_inventory`. Paid orders retain the existing Order History refund workflow.

Every action also requires page access. Having an action permission alone cannot open the page or update an order. Buttons use the same capability checks as the controller/model. Staff branch isolation, live role lookups, valid status transitions, request verification, and existing financial/inventory behavior are preserved. Customer tracking remains limited to the customer's own orders.

Seed missing permissions with `php tools/seed-online-order-permissions.php --apply`. New action permissions inherit existing Online Orders role access once. Runtime payment/cancellation checks still require the user's existing POS or financial/inventory permission, including when those permissions come from another assigned role. Newly created action permissions also receive Admin/Superadmin defaults. Existing permission entries only get their labels/module updated; revoked role assignments are never restored by this command. The general permission seeder delegates these permissions to the same preservation logic.

No new database tables are needed. The seed updates existing `permissions` and `role_permissions` records only. Integration checks cover page/action/sidebars, revoked permissions, direct POST/model calls, branch isolation, forged actors, request verification and repeat seeding.
