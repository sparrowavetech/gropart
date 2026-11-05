# Seller Invoicing (development preview)

Author: Sparrowave. Version 0.1.0. Requires active Botble Ecommerce and Marketplace plugins. All files are self-contained under this plugin.

Current local scope (activated only in the local environment during this development pass):

- Admin and seller libraries for review snapshots, with seller ownership checks.
- Admin order-detail link and seller order-detail link through Botble filters.
- An admin-only prepare action that captures the **existing** Marketplace revenue, order customer shipping, and current issuer/seller profile into plugin-owned immutable review records. On a newly completed order, a later-priority hook also attempts the same idempotent capture after Marketplace creates its revenue; capture failure is reported without blocking the order.
- Dynamic policy evidence from Marketplace settings: mode, global percentage/fixed fee, effective seller rate after subscription override, category-commission enablement and shipping toggles. Manual/historical review explicitly marks these as **current settings observed at review**, not proof of the order's original fee policy. The recorded revenue fee remains authoritative.
- Currency-aware, integer minor-unit check of the captured Marketplace merchandise base, existing fee and seller net. Currency precision comes from the matching Ecommerce currency row; absent/unsupported precision stays in review. The order screen shows the result and warns if the underlying Marketplace revenue later changes or disappears. An earlier review snapshot stays immutable.
- Plugin-owned `DocumentArithmetic` rejects draft line, tax-column and grand-total mismatches before a future issuance workflow can use them. Its local test includes the one-paisa discrepancy observed in the older invoice sample. It does not select tax rates or issue a document.
- Admin preparation displays only expected, translated validation messages; unexpected errors are reported internally and shown as a generic failure.
- No automatic billing, fee deduction, tax calculation, PDF tax invoice, tax filing, payment, notification or courier request.

The snapshot explicitly leaves fee classification, shipping allocation and tax treatment unresolved. It cannot be represented as a tax invoice. Order completion and a Marketplace revenue row are required to prepare it. Preparation does not change order, revenue, seller balance or payment rows.

Do not activate or run migrations on live data as part of a source review. On this workspace, dependency drift is documented in `GROPART_CONTEXT.md`; inspect it before install/update. The plugin manifest declares Ecommerce and Marketplace dependencies, and the provider also checks both at boot. `si_order_snapshots` and `si_documents` are business-record tables; the migration rollback intentionally preserves them. Subsequent development must add a separate, reviewed retention/export path before any uninstall cleanup.

Planned next work: authenticated admin/vendor view checks; fee-component adapters and shipping responsibility evidence; legal entity validation; versioned tax packs; issuance and credit-note lifecycle; PDF/archive; filing exports; cancellation/RTO reconciliation; expanded test matrix. See `docs/architecture/seller-invoicing-global-plan-2026-11-03.md` in the Gropart workspace.

Local verification performed: Ecommerce and Marketplace active, new plugin activated, migration ran, five plugin routes registered; local homepage HTTP 200 and unauthenticated admin/vendor libraries redirected to their login pages. `tests/snapshot-smoke.php` prepared the same existing order twice inside a database transaction, confirmed one review snapshot and exact stored fee, then rolled back. No lasting order/revenue/snapshot data was written by that test.

Further local verification: `tests/money-reconciliation.php` passed for 0/2/3-decimal money, precision rejection, revenue balance and strict document totals; the older sample's ₹0.01 row/grand mismatch was rejected. The rollback-only snapshot test passed with source-revenue drift detection, and plugin Blade views compiled. Five local routes remain registered. An authenticated local admin browser displayed the Seller fee library and its Marketplace menu link; it had no persistent snapshot rows. The authenticated order detail and vendor screens have not yet been visually verified.

Local UI follow-up: fixed the prepare POST route action for the installed Laravel runtime. An authenticated admin prepared one review snapshot for completed local order `10814`; its record appeared in the admin library with balanced existing Marketplace revenue. Local order `11138` is processing with no Marketplace revenue, so the button is hidden and the reason is shown. See `TESTING.md` for the exact supported UI test flow. This record is local-only and remains `needs_review`; vendor UI and tax-document issuance remain unverified/unimplemented.

Thumbnail prompt (built-in image generation using Product Free Shipping screenshot as visual reference): 2:1 mint-to-turquoise SparroWave-style plugin card, title Seller Invoicing, subtitle Marketplace fee invoices & records, byline SparroWave Technologies, oversized invoice/receipt/check illustration.
