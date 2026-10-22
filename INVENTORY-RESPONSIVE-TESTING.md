# Product Directory responsive verification

Verified on 22 October 2026 using the signed-in local application in the Codex in-app browser. Live checks changed only filters, sorting, pagination and opened dialogs; no product or stock changes were saved.

## Layout

The original DataTables rows reflow into cards below 1200px. Product actions, permissions, server pagination and row lookup use the same DOM and handlers. Larger screens retain the table. Filters and actions wrap, and phone summary cards use two columns. Product dialogs have bounded height with scrolling form bodies and reachable header/footer controls.

## Viewports checked

320×568, 360×800, 375×667, 390×844, 414×896, 430×932, 600×960, 768×1024, 820×1180, 834×1194, 1024×768, 1180×820, 1199×900, 1200×800, 1280×800, 1366×768, 1440×900 and 1920×1080.

All 18 passed the document horizontal-overflow check. Confirmed grid cards below the breakpoint and table rows at and above it. Checked narrow-phone card content bounds and visually reviewed phone and compact desktop layouts.

## Functional checks

- All eight sort choices returned records and kept card field labels after redraw.
- Stock filtering, unmatched search, reset and next/previous pagination worked on mobile.
- Product action menus opened from the same card row; Product Insights, Edit Product and Package availability opened for the selected product.
- Product Insights fits at 320px after fixing barcode controls and breakdown table wrapping. Wide batch tables scroll inside their own container.
- Add Product and the package editor were measured at 320×568, 390×844, 667×375, 768×1024, 820×1180 and 1024×768. Dialog bodies fit horizontally; Save/Cancel footers remained within the viewport. Opened the pricing and package tabs without submitting forms.
- Package availability fits at 320×568 with its Close action visible.
- The isolated PHP regression harness passed 633 integration checks. Filter persistence and all sorting mapping tests passed; PHP syntax validation passed.

This covers browser viewport emulation, not physical-device or Safari/Firefox certification. Virtual keyboards, assistive technology and every possible screen size were not exhaustively tested.

## Modal card follow-up

Inventory modal tables now reflow into labeled cards below 768px. Labels refresh after AJAX/DataTables redraws, preserving original rows, inputs and handlers. Empty states and aggregate totals are handled separately.

Verified Product Insights at 320, 390, 600, 767, 768 and 1024px: cards below 768px, normal table rows above, and no modal body horizontal overflow. Stock-history cards were checked at 320, 390, 600, 767 and 768px; pagination retained all seven field labels after redraw. Added and removed an unsaved variant row at 320px, confirming original controls still work and the form stays within its width. Popular badges stay on a single line and do not overlap category badges at 320px. No product or stock changes were saved.
