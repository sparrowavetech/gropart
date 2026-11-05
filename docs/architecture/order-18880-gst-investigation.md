# Order 18880 GST investigation — 17 October 2026

Read-only production inspection; no production order or functional source changes.

Live order 18880 and invoice 739: subtotal 2216.00, tax 338.03, shipping 247.00, fee/discount/shipping tax zero, total 2801.03. Payment 951 is completed Razorpay, stored amount 2463.00. This inspection did not independently fetch the Razorpay API.

Order item 21519/product 2183: quantity 1, price 2216.00, options.price_includes_tax=true, taxRate=18, tax_amount=338.03. Correct net subtotal is 1877.97; net + included GST + shipping = 2463.00. Tax breakdown separately contains 398.88 (18% of gross); that metadata is also inconsistent and needs a separate calculation-path correction.

## Reproduced cause

`ecommerce-wholesale/src/Listeners/SaveOrderWholesalePrices.php` runs on OrderPlacedEvent. Production wholesale is active/enabled and enabled for guests. It totals qty * stored item price as net subtotal regardless of price_includes_tax, then adds tax again. This also runs when no wholesale price applies. It writes the inflated order total and updates the invoice. Production and local listener SHA256 match: 4d6f13c7c3a0d976cade43fcf528af8fb67f7ce38f5b4ad80c9047f5ec08bf56.

Local executable `tests/Regression/Order18880GstInvestigation.php` invokes the real listener with synthetic equivalent amounts and a stub pricing service returning no wholesale price. It reproduced order AND invoice changing from 2463.00 to 2801.03, subtotal from 1877.97 to 2216.00. All local fixture database changes rolled back; events faked. This demonstrates the exact corruption mechanism, although a historical per-write production trace is unavailable.

## Indian GST review

The plugin registers GST labels, HSN/store fields and invoice-variable formatting. Its shipping listener returns rates unchanged; it does not recalculate this order's stored total. Its invoice template displays stored order/invoice amounts, so reflects the upstream corruption. Additional invoice concerns: total_price sums unit price without multiplying quantity; item tax values and totals depend on upstream fields being internally consistent. These are separate from the confirmed wholesale write defect.

Farmart Helper's shipping preservation adds only stored shipping and shipping tax on a cart rebuild; it does not contain this GST subtotal rewrite. Do not disable the failed-payment protection to address this issue.

## Next implementation scope

Correct the wholesale finalization behavior to respect inclusive/exclusive prices and avoid repricing a paid checkout from current catalog rules; cover no discount, real wholesale discount, quantity, mixed tax modes, shipping tax, and invoice items/totals. Preserve extensions outside author-managed core where feasible. Then back up and repair order 18880/invoice 739 consistently against captured gateway amount, including inconsistent tax breakdown. No live repair has been performed in this investigation.

## Implementation and deployment completed

Farmart Helper 1.2.0 implements the fix with container bindings, model listeners and filters; no ecommerce, marketplace, wholesale, Razorpay or Indian GST author files changed. It preserves the inclusive checkout snapshot on wholesale finalization, reconciles stored tax component amounts and breakdown, stores net invoice line prices and correct gross totals, and fixes the missing inclusive flag/full-quantity tax in admin order creation. The Indian GST invoice price total now includes quantities.

16 local GST assertions and the 26 existing payment/shipping assertions passed, including actual admin create-order calculation with a sale price, quantities, mixed inclusive/exclusive baskets, shipping tax/fees/discount preservation, component edits, invoice line values and binding resolution. All fixtures rolled back. No standalone POS plugin exists in this source tree; external POS clients were not tested.

Production Razorpay API confirmed captured INR 2463.00. Deployment and transactional repair dry-run passed, including a replay of the wholesale listener and prior failed-payment/shipping checks. Order 18880 and invoice 739 were repaired to subtotal 1877.97 + GST 338.03 + shipping 247.00 = total 2463.00. Invoice line and order/invoice tax components were repaired as well. Payment, stock, histories and order workflow were unchanged.

Backup: `/home/u834473639/codex-backups/gst-18880-20261017-172640` (original helper, full scoped database before/after snapshots, package/manifest and repair script). Repair committed after verification. No order placement event or customer notification was replayed by the repair; only the bound wholesale listener was invoked directly to confirm no repricing.
