# Promotion simulation results

Validated on October 21, 2026 using unique disposable `pos_burger_test_*` databases. Fixtures contain invented customers, products, sales and gateway credentials. Test databases are dropped after each run; source business records are unchanged. Gateway calls use an in-process simulated transport and never reach PayMongo.

## Results

- 70 promotion checks passed, including actual POS AJAX requests with CSRF/session guards and separate concurrent checkout processes.
- 5 POS interface regression checks passed.
- 632 existing integration checks passed after the fixes.

## Flows verified

1. Create draft → activate → eligible automatic offer or code → server-calculated discount/tax/service totals → immutable receipt and redemption.
2. Pause/expire after preview → checkout rejects without creating a sale. Reactivate and review → successful checkout. A repeated payment request returns the same sale.
3. Above-limit manual amount → rejection → supervisor request → independent approval → explicit Apply → correct preview → sale consumes approval. Approval cannot be reused, changed to a different amount, used after expiry, or applied without permission.
4. Online order → reserved promotion/stock → gateway payload contains discounted amount → verified simulated payment → one completed redemption and sale. Duplicate verification does not consume stock or use twice.
5. Expired gateway session → payment lock released while order remains payable and reservation is retained → new payment session → successful settlement of the original reservation.
6. Unpaid order cancellation releases usage and stock. Refund records refunded redemption; refunded uses continue counting against limits.
7. Concurrent checkouts against different products cannot both claim the last use of a shared promotion.
8. Branch-only supervisors cannot see another branch's targeted offer or report rows. Revoked permissions stay revoked after both installers are rerun.
9. Eligibility includes branch/channel/product/category restrictions, minimum purchase, maximum discount, schedules, global discount controls, customer limits, and no stacking.

## Corrections from this simulation

- Added an explicit Apply button beside the POS manual discount. Previously an unchanged approved amount depended on triggering the field's change event again.
- Restored the saved manual-discount input value when reloading the POS cart, keeping the form consistent with the server-calculated total.
- Added repeatable regression coverage for both interface behaviors and the full POS/gateway lifecycle.

## Repeat

`php tools/tests/burger-integration.php --promotions-only`

`node tools/tests/pos-promotion-ui.cjs`

`php tools/tests/burger-integration.php`

Real merchant settlement, provider-side delivery of webhooks, and the hosted deployment still require testing with the hosting environment and PayMongo test-mode account. Those were not contacted or changed by this simulation.

## Explicit selection and responsive follow-up

Promotions now require a code or explicit selection on both channels; a blank checkout code applies no offer. POS includes a branch-scoped offer picker with Select and Copy code controls. Active code-free promotions remain selectable. Old unpaid orders retain their previously promised financial snapshot.

77 promotion checks passed after this behavior change, alongside all 632 existing integration checks and 9 POS interface regression checks. Tests cover blank input, explicit code-free selection, removing selection, and branch-scoped offer listing.

A browser simulation tested the populated POS and the open offer picker at 20 viewport configurations: 320×568, 360×640, 390×844, 430×932, 540×720, 600×800, 768×1024, 820×1180, 834×1194, 900×700, 901×600, 1024×768, 1180×820, 1280×720, 1366×768, 1440×900, 1920×1080, 844×390, 1024×600, and 1440×560. Fixtures used 12 cart lines, long product names, large totals, expanded discounts, and 27 catalog cards. No horizontal page/order/dialog overflow was detected. Desktop totals stay with the sticky checkout button; mobile layouts use normal page scrolling and section navigation. These are browser viewport simulations, not physical-device or multi-browser certification.

Cart lines now use two-row controls for narrow order panels. The total moved into the checkout footer after the short-screen simulation showed it could scroll away from the payment button.
