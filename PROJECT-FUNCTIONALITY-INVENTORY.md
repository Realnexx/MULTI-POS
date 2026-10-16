# Project feature and functionality inventory

Reviewed: 16 October 2026. Scope: the current working tree of `C:\xampp\htdocs\pos`, including existing uncommitted edits.

## What this review establishes

This directory contains **two applications**: the active multi-branch POS/storefront and a bundled AuroraTabs competition, voting and OJT attendance application. AuroraTabs is explicitly blocked by the POS `.htaccess` and excluded from its deployment package. Its features must not be advertised as features of the deployed POS.

Features below are established from routes, controllers, models, action handlers and interfaces. A feature being present in code does not mean every interaction has passed a browser test. Test results and limitations are listed separately. No application source, existing test fixture, business inventory, accounts or sales were intentionally changed. Isolated tests used generated records in disposable databases.

## A. Main POS and storefront

### 1. Public website and product discovery

- Homepage with editable hero title/subtitle, store branding, featured products, best sellers, catalog categories and active branch previews.
- Browse the product catalog; filter by category and search text.
- Product detail pages with image, description, price, availability, quantity selection and related products.
- Add products to the website cart from storefront interfaces.
- About page using stored website content.
- Contact page with name, email, subject and message submission; messages are stored for administrators.
- Active branch directory with branch contact/location information and images where supplied.
- Shared website navigation, cart count, account links, footer, logo/favicon and responsive styling.
- Motion/animation scripts and reduced-motion handling in the public presentation.
- Unknown routes return a 404 page; legacy customer-login URL redirects to the unified login.

Sources: `controllers/WebsiteController.php`, `models/Website.php`, `models/Product.php`, `views/website/`, `views/partials/shop_*`, public JavaScript and CSS.

### 2. Customer cart

- Database-backed guest carts and signed-in customer carts.
- Add an item, increase/decrease quantity, remove an item and calculate totals.
- Cart ownership separates guest sessions from customer accounts.
- Merge a guest cart into the account at login, without repeating the merge on subsequent calls.
- Choose an active pickup branch and display availability for that branch.
- Server validates whole sale quantities and excludes raw ingredients from direct sale.
- Cart changes do not consume stock.
- Checkout quote/fingerprint and request key detect cart/price changes and prevent duplicate checkout.
- AJAX cart responses update the displayed cart count; ordinary forms also work through redirects.

Sources: `models/Cart.php`, `WebsiteController::cart*`, `views/website/cart.php`.

### 3. Online pickup ordering

- Sign in and place a pickup order from the website cart.
- Capture customer name, email, phone and pickup branch.
- Save item names, quantities, prices and inventory requirements as order snapshots.
- Generate a random `WEB-...` tracking number.
- Reserve/consume inventory when the order is placed, using the same inventory service as POS.
- Orders start Pending and Unpaid; unpaid orders do not count as paid sales revenue.
- Customers list their orders and privately view tracking details and status events.
- Staff queue/board groups new, preparing, ready and picked-up orders, with cancelled/refunded records also available.
- Search an exact tracking number, including records older than the normal latest-200 queue window.
- Advance Pending → Preparing → Ready → Completed, subject to validated transitions.
- Record payment at the counter independently of preparation status; create one linked sale and receipt.
- Require payment before completing pickup.
- Payment retries and repeated completion do not create duplicate sales or consume stock again.
- Cancel an eligible unpaid pending order and restore its original inventory reservations once.
- Block cancellation after preparation and direct paid-order refunds to sale history.
- Reflect recorded refunds in customer tracking.
- Permission checks and saved branch assignments restrict staff access.
- Setting to close new online ordering while preserving existing tracking and fulfillment.
- Checkout rate limit: ten attempts per account per hour.

**Boundary:** pickup and pay at counter are implemented. The POS storefront has no online card authorization, delivery dispatch or automatic expiry/release of pending reservations.

Sources: `models/OnlineOrder.php`, `controllers/OnlineOrderController.php`, online-order/cart/tracking views, `ONLINE-ORDERS.md`.

### 4. Login, registration and account profile

- Unified staff/customer login with username or email and password.
- Customer self-registration; posted role/branch choices cannot create privileged accounts.
- Staff landing destination depends on permissions; customers return to the storefront.
- Profile setup/editing with first/middle/last name, suffix, email, birthday, biography, avatar, address subdivisions, ZIP, store and legacy signature-layer preferences.
- Avatar image upload; duplicate-email checking on profile updates.
- Logout and regenerated login sessions.
- Forgot-password and reset-password forms with SMTP delivery through PHPMailer.
- Hashed reset tokens, expiration, single use, invalidation of older links and revocation after delivery failure.
- Password reset invalidates existing authenticated sessions and requires a subsequent login.
- Account/IP login throttling and separate registration/recovery/reset request limits.
- Session expiry after 30 idle minutes or eight hours total by default; background GET polling does not keep the session alive.
- HTTP-only SameSite cookies, HTTPS Secure cookies, authentication no-store headers and CSRF-protected forms.
- Registration/reset passwords require at least 12 characters and at most 72 bytes.

**Caveats:** profile setup still uses legacy halo-halo language and an `ingredients` table for layer options. The routed `notification` page redirects home even though a separate notification view/controller method remains. Password change in Settings uses a different six-character minimum.

Sources: `controllers/AuthController.php`, `models/AuthSecurity.php`, `models/User.php`, `services/PasswordMailer.php`, `middleware/session.php`, authentication views.

### 5. Roles, permissions and branch access

- Role-based page and action authorization enforced on the server.
- Create/edit/delete custom roles and assign grouped permissions.
- Create/edit/delete noncanonical permission records and list their modules.
- Multiple roles can be attached to an account.
- Canonical operational permissions cover dashboard, monitoring, products, printing, stock, online orders, POS, refunds, reports, expenses, branches, users, roles, activity logs, settings and website administration.
- Superadmin receives all permissions, including future permissions.
- Protect the Superadmin role, its grants and the last active Superadmin account.
- Restrict management of higher-privilege accounts and prevent granting access the actor lacks.
- Hide protected Superadmin accounts from the ordinary user directory.
- Admin and Superadmin can work across branches; other staff use their database-assigned active branch.
- Refresh branch assignments rather than trusting posted or stale session values.
- Show a setup notice and prevent branch operations for unassigned staff.
- Separate staff home page exposes the actions available to the signed-in account.
- Idempotent permission seeder for installation/maintenance.

Sources: `config/permissions.php`, `middleware/access_policy.php`, `middleware/rbac.php`, User/Role/PermissionSeeder models and role/user screens.

### 6. Staff and account administration

- Search, sort and paginate the user directory; show role-distribution counts.
- Fetch individual account details and available assignable roles.
- Create accounts with name parts, email, password, role assignments, photo and POS PIN.
- Edit account details, optional password, roles, image and PIN.
- Remove eligible accounts; prevent self-deletion and protected-account removal.
- Manage staff assignment through branch administration.
- Account-management actions write activity records.

**Boundary:** a `set_status` model helper exists, but there is no corresponding main user-screen controller action in the reviewed dispatcher; do not assume a complete account activation UI.

Sources: `AdminController::users/getUserData/getSingleUser/getRoles`, `models/User.php`, `views/modules/users.php`.

### 7. Branch management

- Create, view, update and remove branches.
- Maintain branch name, address, phone, active status and image.
- Choose a manager and assign staff, including custom staff roles.
- List potential managers/staff and current assignments.
- Branch totals/statistics and printable branch report.
- Active branches feed the storefront, pickup selection and POS terminal selector.
- Cross-branch management is restricted to Admin/Superadmin with the required permission.

Sources: `models/Branch.php`, `ajax/branch_action.php`, branch views and AdminController branch methods.

### 8. Catalog and categories

- Create, view, edit and archive products.
- Maintain category, name, description, SKU, barcode, selling price, cost, image, active status and branch ownership.
- Automatic SKU, barcode, next-product-ID and batch-number helpers.
- Featured and best-seller flags for storefront presentation.
- Product types include simple stock, raw ingredients, recipes and variants/catalog attributes.
- Stock tracking, sellability, ingredient flag, inventory mode, base unit and low/max thresholds.
- Store a per-product discountability flag; see the enforcement gap below.
- Search, sort and paginate inventory; filter branch, category and stock status.
- Display branch stock breakdown, inventory totals, valuation and branch performance information.
- Product detail, edit, delete/archive, stock-history, adjustment and batch-price modals.
- Create/edit/delete categories with descriptions and inspect/search their products.
- Category presentation includes view/filter controls and item identifiers.
- Define attributes, save product attribute values and create/edit variant records.
- Product archive preserves historical sales/stock references; referenced ingredients cannot simply be removed.

**Boundary:** variant catalog helpers exist. Variant-specific burger recipes are not implemented, and the existence of variant storage does not establish a complete variant checkout workflow.

Sources: `controllers/InventoryController.php`, `models/Product.php`, `ajax/inventory_action.php`, product/category views and modals.

### 9. Ingredients and recipes

- Ingredient-first product creation with branch, cost, unit, opening stock and thresholds.
- Base units support counted items and weight/volume quantities, including kg↔g and l↔ml conversions.
- Four-decimal inventory precision; counted ingredients require whole quantities.
- Define ingredient quantities per finished item; search ingredient choices.
- Recipe ingredient cost estimate.
- Preview total ingredient quantities and estimated cost for a chosen number of burgers without changing the saved per-burger quantities.
- Validate duplicate ingredients, self/nested recipes, incompatible units, inactive/nontracked ingredients and branch ownership.
- Saving/editing a recipe does not consume inventory and invalid edits roll back the entire product save.
- Made-to-order mode calculates how many products the branch can make from the limiting ingredient.
- Made-to-order checkout consumes the recipe ingredients multiplied by sold quantity.
- Prepared-batch mode consumes ingredients during explicit batch preparation and creates finished units.
- Selling prepared products consumes finished stock, without deducting their base ingredients again.
- Legacy recipes remain blocked from sale until reviewed/reconciled and assigned a supported mode.
- Prevent changing units/ownership/type in ways that reinterpret existing batch or recipe history.

Sources: `models/RecipeInventory.php`, `models/InventoryQuantity.php`, `assets/js/recipe-admin.js`, recipe partials/modals and `BURGER-INVENTORY.md`.

### 10. Add-ons and modifiers

- Define product-specific active modifiers with name and additional price.
- Optionally associate modifiers with an ingredient, quantity and unit.
- POS customization modal selects extras and previews price.
- Same product with different modifier combinations gets separate cart lines.
- Server re-reads modifier prices and ingredient requirements; reject removed/stale/duplicate selections.
- Checkout consumes selected extra ingredients and snapshots modifier details on receipt items.

**Boundary:** this is a POS customization flow. The website Cart model accepts product/quantity and does not expose the equivalent modifier-selection checkout path.

Sources: Product/RecipeInventory modifier helpers, `ajax/pos_cart.php`, `assets/js/main.js` and POS view.

### 11. Stock, batches, adjustments and waste

- Opening inventory and subsequent stock receipts create batches.
- Record stock-in, stock-out, quick restock and waste/spoilage with quantities and remarks.
- Record branch, batch number, cost, optional selling price and expiry.
- Select specific batches where supported; otherwise allocate sale consumption FIFO.
- View active batches, batch breakdown, product stock history and stock movements.
- Update batch cost/selling price while keeping quantity read-only in the price editor.
- Product edits cannot overwrite physical stock balances.
- Exclude expired stock from availability; allow expired stock to be recorded as waste.
- Synchronize stock totals with batch balances and preserve decimal quantities.
- Transactions, product/batch locks and aggregated ingredient requirements prevent partial deductions and overselling.
- Preserve operation snapshots, ingredient/product names, units, costs, original batches, actor, branch and sale-item links.
- Request tokens deduplicate preparation, checkout and supported stock-return operations.

**Boundary:** no dedicated purchase-order, supplier-accounting or interbranch stock-transfer workflow was found in the active route/action map.

Sources: RecipeInventory/Product models, inventory action handler, stock/batch modals and inventory logs.

### 12. Stock alerts

- Identify out-of-stock, low-stock and overstock conditions.
- Branch-scoped header notifications with counts and filtered alert lists.
- Mark one or all alerts read; resolve obsolete alerts when stock changes.
- Update existing unread alerts instead of repeatedly inserting the same condition.
- Dedicated low-stock product page with restock interaction.
- Preserve fractional balances in alerts.
- Made-to-order products rely on ingredient alerts rather than fictitious finished-stock alerts.

Sources: `models/StockNotification.php`, `ajax/notification_action.php`, header and low-stock view.

### 13. POS terminal

- Optional PIN lock before making sales.
- Personal cashier PIN and a master PIN path for users with settings permission.
- Admin/Superadmin terminal branch selection/reset; other staff automatically use their assigned branch.
- Search/filter sellable active products by category/type and display availability.
- Dine-in and Take-out order modes.
- Session-based active order; add items/extras, edit quantities, remove items and clear with confirmation.
- Fixed monetary discount, server subtotal/total, tendered amount and change calculation.
- Quick cash controls, payment validation, payment modal and success/receipt display.
- Visible payment choices: Cash, GCash and Card (recorded as Credit Card).
- Backend also accepts Card, Maya, Bank Transfer and Debit Card labels; these labels are not proof of gateway integration.
- Re-read authoritative product/modifier prices and current stock during transactional checkout.
- Persist sale, items, modifier snapshots and inventory consumption together.
- Reject insufficient payment, invalid quantities, invalid methods and stock shortages.
- Retry a completed checkout without duplicating the sale.
- Refresh product availability following checkout.
- Shortcuts: F1 Dine-in, F2 Take-out, F4 clear, F7 search, F8 payment, F9 complete payment, F10 close modals; additional Escape handling.

**Caveat:** PIN setup is inconsistent: the settings screen permits six digits while terminal entry, user management and verification use four digits. PINs are compared as stored values rather than password hashes.

Sources: `controllers/PosController.php`, `ajax/pos_cart.php`, `ajax/pos_pin.php`, `views/modules/pos.php`, `assets/js/main.js`.

### 14. Receipts, history, refunds and stock returns

- Receipt lookup from sale ID and receipt data endpoint.
- Receipt preview, printing and reprinting with store/cashier/branch, items, extras, totals, discount, payment and change.
- Order history search, pagination and date/status/channel/location filtering.
- History statistics and filtered transaction CSV export with summary rows.
- Permission-protected changes to supported sale statuses.
- Financial refund/cancellation does not automatically recreate usable food inventory.
- Explicit whole-sale stock return with either a before-preparation ingredient void or usable finished-goods return.
- Restore original movement/batch quantities rather than recalculating from the current recipe.
- Prevent a second distinct stock return; safe retries of the original return.
- Reject unsupported returns, expired/missing original batches and old sales lacking movement history.
- Link refund state back to an originating online order.

**Boundary:** partial-item returns/refunds are not implemented. Returning finished goods leaves extra ingredient consumption in place. Payment labels/status changes do not execute external payment-provider refunds.

Sources: PosController, Sale/RecipeInventory models, history/receipt views and inventory return actions.

### 15. Dashboard, reporting and printing

- Dashboard period/branch selection with persisted branch preference.
- Completed sales totals, order counts, average order value, product/user counts and low-stock indicators.
- Recent transactions, top-selling products and seven-day sales chart data.
- Business report screen with sales and inventory movement datasets.
- Summary revenue, discount and transaction metrics; search/sort/paginate report tables.
- Date presets/custom date intervals and branch filters.
- Separate inventory reports/analytics screen combining inventory metrics, sales, expenses and stock movements.
- Inventory log date/product/branch/search filters and summary counts.
- Printable sales reports and inventory movement reports.
- Printable branch report, activity log, expense ledger and expense summary.
- Dedicated print layouts and print-specific styles.

**Caveats:** a print action is browser printing, not a dedicated server-side PDF export. Sales/inventory report data retrieval uses a 5,000-row cap in the generic print controller. Some dashboard product/user/low-stock counts use global queries even where sales are branch-filtered.

Sources: AdminController, InventoryController, Sale/Product/Expense models, dashboard/reports/logs/print views.

### 16. Expenses

- Create, inspect, update and delete expenses.
- Record branch, category, amount, expense date and description.
- Filter/search/sort/paginate expense records by branch and period.
- Expense statistics including monthly spend, daily count and highest spending category.
- Monthly revenue/expense comparison data and category/branch summary information.
- Print the filtered ledger or an expense summary.
- Scope staff expense access to their branch and log changes.

Sources: `controllers/ExpenseController.php`, `models/Expense.php`, `ajax/expense_action.php` and expense views.

### 17. Website content management

- Edit homepage hero title/subtitle and About text.
- Edit public contact address, phone and email.
- Read submitted contact messages and mark messages read.
- Record CMS changes in the activity log.

**Boundary:** there is no outbound customer-message reply/email workflow in the CMS action map.

Sources: Website model, AdminController CMS method, CMS handler/view.

### 18. System settings

- General: store name, email, phone/alternate phone, address, logo, founder name/title/quote, expansion milestone and online ordering switch.
- Financial fields: currency label/symbol, tax percentage, service charge and tax-inclusive switch.
- POS fields: receipt text, prefix/start number, stock threshold and default order type.
- Security fields: login-attempt count, session timeout, strong-password switch, 2FA switch, admin key, require-POS-PIN switch, master PIN and cashier PIN entries.
- Appearance: primary and secondary color fields.
- Own-password change requiring the current password and matching replacement entries.
- Category-based setting persistence, logo upload/preview and activity logging.

**Important:** fields being saved is not proof that they affect runtime. No active tax/service-charge computation or `is_discountable` enforcement was found in RecipeInventory checkout. The 2FA switch has no main-POS challenge/verification flow. Authentication limits/timeouts come from `config/auth.php`/private auth configuration rather than these saved UI fields. Receipt prefix/start fields are not consumed in the inspected receipt-generation paths. Default order type is also hardcoded in key cart initialization paths. Treat these as configuration UI or partial integration until wired and tested.

Sources: `AdminController::settings`, `models/Setting.php`, settings view, authentication policy and checkout service.

### 19. Activity logging and request monitoring

- Record operational events for users, roles, branches, product/stock changes, sales, exports, CMS and settings.
- Search, sort, paginate and filter activity history by supported user/action/date fields; printable activity log.
- Page/security monitoring dashboard with date filters and branch access restrictions.
- Record rendered GET page views, route, response status, server processing duration, account/branch and a hashed browser-session marker.
- Record authentication success/failure/logout/reset/session expiry and selected denial/rate-limit/server-error events.
- Show page rankings, aggregate traffic/error/session metrics and recent security events.
- Exclude query parameters, passwords, reset tokens and request bodies from monitoring records.
- Exclude writes, JSON/polling and redirects from page-view counts.
- Monitoring errors do not intentionally interrupt business flows.

**Boundary:** application telemetry is not external uptime monitoring, malware scanning, full browser-performance monitoring or automated incident response. No retention/purge UI is provided.

Sources: ActivityLog/Monitoring models, monitoring controller/view, database bootstrap and access middleware.

### 20. Shared interface behavior and technical operations

- Shared admin sidebar/header/footer; permission-sensitive links and branch context.
- Toast feedback, validation/error dialogs, loading indicators, modal close/reset behavior and logout confirmation.
- Modal submissions preserve entered values/images on failed saves and allow retry; attach CSRF tokens.
- Reusable admin pagination, request-security and loading scripts.
- Image validation for JPG/JPEG/PNG/GIF/WebP, matching image type/extension and a 5 MB maximum.
- Configurable application base path and fail-closed private configuration loader.
- Protected internal directories, private metadata and nonentry PHP through `.htaccess`.
- CLI-only initialization; manual migration tools for inventory, authentication, online ordering, monitoring and legacy data.
- Deployment allowlist/staging builder, automated deployment/quantity checks and documented deployment/rollback process.
- Runtime includes bundled presentation libraries and PHPMailer.

**Boundary:** root-level patches, schema repair utilities, previews, diagnostic pages and migration scripts are developer/maintenance artifacts. They are not ordinary customer/admin features and should not be executed indiscriminately.

## B. Bundled AuroraTabs application

The following are separate source modules under `auroratabs/`. They are blocked from the POS web deployment and have not been certified through end-to-end operation in this review.

### 21. Identity, organizations and administration

- Separate administrator, organizer, audience and judging login interfaces; login handlers, role lookups and username checking.
- Registration with organization/event setup interfaces, email OTP verification/resend handlers and payment selection.
- Account directory, create/update, status toggle, password reset, soft delete, restore and permanent deletion.
- Organization records, user association, status toggle, soft delete, restore and permanent deletion.
- Role CRUD, role-permission assignment and module CRUD.
- Profile details, avatar and password updates.
- Security settings retrieval/update and permission helpers.
- Dashboard statistics, activity history, bulk deletion, clear and CSV export.

### 22. Competition and participant administration

- Competition create/get/list/update, status changes, dependencies and logo upload.
- Competition soft delete, trash listing, restoration, permanent removal and empty trash.
- Candidate create/update, photos/details, automatic candidate numbering, competition lookup and the same trash lifecycle.
- Bulk candidate assignment/unassignment to competitions.
- Competition categories with create/update/get/list, competition lookup and trash lifecycle.
- Criteria create/update/list by category/competition, weight updates, required-criterion toggle and trash lifecycle.
- Judge directory/assignment pages; bulk competition/category assignments and unassignment.
- Tabulator data and assignment toggles.
- Competition summary and printable/exportable candidate/judge/score data.

### 23. Scoring, formulas and results

- Judge-specific scoring data and category-detail retrieval.
- Save criterion scores and finalize submissions.
- Judge self-unlock and administrator submission-detail/lock management.
- Lock/unlock individual submissions/categories, lock/unlock a competition, clear submissions and configure unlock limits.
- Formula save/list/get/delete, formula builder and a local simulator interface.
- Formula engine supports SUM, AVG, MAX, MIN, COUNT, ROUND, ABS and score/judge-count/category-weight/rank context.
- Live results, category rankings, final results and competition/category selectors.
- Recalculate results with history.
- Judge reports, judge logs and printable category/final results.
- Display launcher opens result/judge views in separate tabs and copies display links; competition logo settings.
- Deleted formula/judge screens exist, but the formula endpoint's action map does not by itself establish their complete trash lifecycle.

### 24. Audience voting and finance

- Competition/candidate browsing for voting, user vote-wallet information and casting votes.
- Audience rankings, voter list, vote logs and reports.
- Vote-package retrieval/save/delete and purchase interfaces.
- Voting settings/toggle screens and PayMongo settings, enabled methods and fee pass-through configuration.
- PayMongo checkout-session creation code for vote packages.
- Transactions dashboard, transaction retrieval and finance CSV export.
- Candidate, judge, score and vote CSV export; print audience rankings/export pages.
- Pricing-plan selection and management-oriented UI.

**Material limitations:** `voting_handler.php::process_purchase` credits a wallet and records successful GCash payment without a provider authorization call. `payment-success.php` credits pending purchases based on URL parameters/local records without querying PayMongo for confirmation. Organizer `auth_process_payment.php` similarly records success from the submitted method. These paths are not verified payment processing. Pricing/general-settings interfaces contain static content; general-settings has no visible save handler. Documentation references to Stripe/PayPal, advanced plans, whitelabeling or live-TV export are not sufficient implementation evidence.

### 25. OJT/student attendance subsystem

- Fetch students from a separately configured remote student database; list local/all student records.
- Fetch/update/delete a student and export student lists.
- Look up student information through a tracker endpoint.
- Record attendance, multi-entry attendance and inspect individual attendance records/logs.
- Calculate hours and attendance statistics; show hours summaries.
- Save/read monthly attendance and manual monthly hours.
- Print/export students, attendance and individual/all-student hours summaries.

**Boundary:** external student data depends on another database. The remote student query is hardcoded to a particular BSIT year/level/semester cohort. This subsystem is unrelated to POS inventory or payroll.

### 26. Bundled public/template material

- AuroraTabs public landing/navigation material and contact/newsletter form scripts.
- Authentication/administrator design templates, static documentation, hire-us/discount pages and theme demos.
- Duplicate profile/sidebar files, test/diagnostic scripts, SQL repair files and temporary pages.
- Third-party charts, icons, CSS, JavaScript, fonts and image assets support presentation; their demo pages are not additional implemented business modules.
- Offline judging/PWA and other roadmap items mentioned in documentation are not established as implemented.

## C. Verification performed and remaining coverage

### Passed during this review

- **5** database-free deployment/configuration/quantity unit tests.
- **50** isolated burger inventory, recipe, checkout, rollback, return, endpoint, catalog-render and concurrent-last-stock integration checks, after repairing fixture setup only in a temporary audit copy.
- **49** isolated role/permission and protected-account model checks.
- **12** isolated saved-branch/assignment/unassigned-staff checks.
- **10** isolated monitoring schema/grants/privacy/summary/branch-isolation checks.
- **19** isolated online cart/order/stock/payment/cancellation checks.
- **13** isolated guest-cart ownership/merge, stale quote, transition and refund checks.
- **10** isolated online-order settings/early-payment/permission/retry/stock checks.
- **13** isolated authentication throttling/reset/expiry/replay/session-revocation checks.
- **2 browser test suites** covering recipe quantity/cost previews and modal validation, retry, retained values/photo, CSRF and existing AJAX handlers.
- **26** first-party JavaScript/test JavaScript files passed syntax checks.
- **541** PHP files were syntax checked; **539 passed and 2 failed**, as listed below. This broad scan includes the bundled application and local utilities, excludes scratch/dependencies, and is not limited to the deployment allowlist.
- Local homepage returned HTTP 200.
- Anonymous GET smoke checks covered **all 61 registered routes**: 10 returned 200, 42 redirected, 3 rejected the method with 405 and 6 returned 400 for missing/invalid action input. No 5xx responses or PHP warning/error text were observed. Protected redirects are expected and do not verify the destination's authenticated functionality. Exact results are in `PROJECT-FUNCTIONALITY-HTTP-CHECK.md`.

The numbered database/unit groups total **181 checks**, plus two browser suites and the syntax scan. Counts exclude failed setup runs/repeated checks.

### Failures and integration gaps found

1. `auroratabs/profile.php:1302`: PHP parse error, unexpected `else`.
2. `auroratabs/temp/samp.php:130`: PHP parse error, unexpected `<`.
3. Original burger test harness is stale: it unconditionally reapplies an already-present migration, omits newer auth/permission configuration and current JavaScript assets from its fixture, and lacks current authenticated/PIN session metadata. Only the separate audit copy was adjusted to obtain the 50 passing checks. The checked-in runner remains unchanged and needs maintenance.
4. POS PIN length differs between settings (six) and unlock/user entry (four).
5. Tax/service charge, per-item discountability, 2FA, some security UI fields and receipt numbering fields are only partial/settings integration as explained above.
6. Settings password change accepts a shorter password than registration/reset.
7. Legacy branding/profile layer fields remain alongside the burger-oriented workflow.
8. Some dashboard counters are not scoped in the same way as sales figures.
9. Bundled AuroraTabs payment-recording/return paths do not establish actual provider payment verification.

### What has not been proven

- A complete authenticated browser walkthrough of every main-POS screen, action, role and responsive viewport.
- End-to-end operation of every user/branch/category/expense/CMS CRUD interface or every print/CSV result.
- Delivery of real SMTP messages, external payment authorization/refund, remote OJT access or hosted deployment behavior.
- Full AuroraTabs behavior, access-control correctness, concurrency or payment safety.
- Every combinatorial edge case, hardware scanner/printer compatibility, load/capacity or backup restoration.

These are explicit remaining checks, not passing claims. Source inventory coverage and execution coverage are different: this report includes the discovered modules even where runtime verification remains incomplete.

## D. Exact route/action and file appendix

The companion `PROJECT-FUNCTIONALITY-SOURCE-INDEX.md` records the root route names, controller/model methods, handler action branches, application screens, and a classified file manifest. It preserves smaller or auxiliary entry points that would otherwise be easy to omit from a prose feature list. The appendix is an index of source declarations, not a claim that every indexed function is reachable or working.
