# Farmart Helper

Gropart-owned extensions for Farmart/Botble. Existing phone/form/tracking extensions remain in their original provider. Version 1.1.0 adds checkout integrity without editing Ecommerce, Marketplace or Razorpay vendor files.

## Checkout integration

- `CheckoutIntegrityServiceProvider` binds the Ecommerce OrderHelper service to `CheckoutOrderHelper`, decorates the existing `core.action` dispatcher, and registers `GuardCheckoutSuccess` in web middleware. These integrations activate only with Ecommerce and Payment enabled.
- `PaymentActionGuard` intercepts only non-completed Razorpay `PAYMENT_ACTION_PAYMENT_PROCESSED` actions while Razorpay is active. Everything else, including completed Razorpay, COD and bank transfer, delegates to the original dispatcher. Existing listeners, registration, priorities, lookup and removal are delegated rather than rebuilt.
- `RazorpayAttemptRecorder` exposes the installed gateway's protected payment persistence method. It retains the gateway's payment deduplication/paid-sibling handling without copying the controller or bypassing webhook/callback signature validation. The guarded action is downstream of the original gateway controller. It does not perform payment capture or call Razorpay.
- `CheckoutOrderHelper` blocks failed/pending Razorpay finalization and preserves retained shipping/shipping tax when the parent rebuilds an existing cart total. It leaves an explicitly supplied total alone.
- `GuardCheckoutSuccess` checks all unfinished orders under the checkout token before the controller/Marketplace success handler can finalize them. Unsuccessful Razorpay attempts return to the existing checkout cancellation/retry URL. An actual completed payment is not hidden by a failed sibling attempt.

The dispatcher decoration is necessary because this installed CMS ignores action callback return values; adding a normal action listener returning false cannot cancel the Ecommerce listener. Both already-registered and later listeners remain on the original dispatcher. Only the identified payment action is intercepted.

## Vendor files

The local copies of these files were restored to repository HEAD, which matches their content before this chat's emergency patches:

- `platform/plugins/ecommerce/src/Supports/OrderHelper.php`
- `platform/plugins/marketplace/src/Providers/OrderSupportServiceProvider.php`
- `platform/plugins/razorpay/src/Http/Controllers/RazorpayController.php`

This does not revert older project customizations already present in HEAD. The previous Razorpay amount fallback edit is not required here: its existing `capturedChargeAmount()` already returns the payment entity amount (despite the method name) when available, including a failed attempt. The guard persists that amount without passing through Ecommerce's order-total overwrite.

## Local verification

Run against a local development database:

```sh
php platform/plugins/farmart-helper/tests/checkout-integrity.php
```

The old `tests/Regression/RazorpayFailedPaymentRegression.php` entry point delegates to the same script. It refuses production, rolls back database fixtures, fakes application events, and uses an in-memory cart for shipping calculations. No gateway API calls are needed. The checks cover resolution/registration, failed and pending action handling, duplicate failures, success-page protection, completed/offline payment policy, dispatcher delegation, shipping, shipping tax, explicit totals and unchanged carts.

These focused checks do not claim full end-to-end validation of every payment gateway, partial-COD capture, refund or concurrent request. The local PHPUnit dependency tree was unavailable, so this is an executable standalone regression suite.

## Deployment and updates

Deployed to gropart.com on 17 October 2026 with a backup of the previous helper and all three vendor files. Production checks passed: failed-webhook replay preserves the incomplete order, no OrderPlacedEvent is dispatched, and shipping-inclusive cart totals are retained. Deploy the Farmart Helper changes and remove the matching three emergency server patches together, using a backup and verified baseline. Keep Farmart Helper active. Do not upload only the reverted vendor files while leaving the old helper on the server: that would remove protection.

A vendor update should no longer overwrite the custom fix files. Upstream behavior can still change: verify the OrderHelper signatures, Razorpay saveOrUpdatePayment visibility/signature, core.action delegation contract, checkout success route name/middleware, and cart amount semantics on a staging copy after updates. Run the regression suite before promotion. Test partial COD, multi-vendor captures, refunds, free shipping, coupons, wholesale and loyalty workflows for release-level confidence.

The earlier live correction of order #10018873 is a separate database repair and is not replayed by this plugin.


## Inclusive GST integrity (1.2.0)

Farmart Helper binds the wholesale finalization listener to preserve the already calculated checkout snapshot whenever a basket contains inclusive-tax lines (including mixed baskets). Sale and wholesale pricing remain in the checkout pricing flow; finalization must not reprice a paid basket.

Tax component storage reconciles component amounts and JSON breakdown to the stored line tax, allocating rounding residue to the last component. Quantity and discount tax snapshots are retained. Invoice creation stores net line prices/subtotals and gross line totals for inclusive prices. Admin order creation populates a missing inclusive flag from the product and stores quantity-aware line tax. Explicit saved flags are preserved. Indian GST invoice price totals use line subtotals, including quantity.

Local regression: `php platform/plugins/farmart-helper/tests/inclusive-tax.php` (transactional, refuses production). Admin create-order calculation is tested directly; no separate POS plugin was found in this checkout, so independent external POS clients are not covered. No author-managed files are modified.
