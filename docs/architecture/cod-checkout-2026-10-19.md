# COD checkout compatibility — 2026-10-19

## Ownership
Advanced COD: persisted advance intent, paise allocation across vendor orders, verified payment handling and balance, duplicate callback lock, order/item protection, registry-based gateway selection.
Shipmozo: COD delivery collection uses order total less verified net receipts; missing/overpaid advances block shipping for reconciliation.
Farmart Helper: third-party/core adapters for Razorpay hosted checkout, partial callback storage, pending-vs-spent cart distinction, checkout locks, wholesale and loyalty finalization.
No core, Razorpay, wholesale, loyalty or Indian GST files changed for this task.

## Confirmed causes
- Embedded Razorpay created gateway orders while payment options were rendered; the helper then locked totals before COD fee/shipping selection.
- Full-order payment checks rejected the intended advance; core payment handling expected full payment.
- A pending payment lock was reused by core isCheckoutTokenSpent, causing cart deletion despite no completed payment. Override now uses completed/cancelled/finished criteria only.
- Shipmozo formerly classified online advance as prepaid; collection now derives from actual receipts.

## Local verification
Passed: 30% paise splits, multi-vendor allocation, pending/failed guard, actual completed finalizer, repeated callback payment-row deduplication, late failure, wrong amount rejection, alternate registered gateway event without Shipmozo, loyalty split/single redemption, wholesale snapshot preservation, item economics lock, shipping preservation, source capture, cart token reset retaining contents/address.
Browser: total 4535, advance 1360.50, balance 3174.50; gateway request log confirms 136050 paise. Browser return with pending lock retains cart after fix.
External Razorpay page observation was denied by automatic approval review. No actual payment made.

## Deployment
Backup: /home/u834473639/codex-backups/cod-checkout-20261019-083547/plugins-before.tar.gz
Live COD percentage was 38; previous setting backed up in cod-percentage-before.json and set to requested 30. COD minimum 500 unchanged.
Only files listed below are deployed. No historical order/payment/status repairs.

## Limits
Alternate gateway was tested via its standard verified-event contract, not every gateway's hosted/embedded interface. Future gateways need to implement the platform payment-data and verified-payment hooks.
Wholesale and loyalty finalization tests used rollback-only local fixtures; no live wholesale/loyalty account was provided.

## Files and SHA256
- `platform/plugins/advanced-cod/src/Hooks/AdvancedCodCheckoutListener.php`: `2543bea8d47caed8cc27f9fe90ebd781b746ebecb875fd029421244134d47659`
- `platform/plugins/advanced-cod/src/Providers/AdvancedCodServiceProvider.php`: `f291cfbad55577267be34f596259894329dd78c31a7a4d8a5d3162a414303e43`
- `platform/plugins/shipmozo/src/Shipmozo.php`: `f7bb7b2ccae13b93c23c0559d53c805f07955c7e403df9849ea752bdb0bfa236`
- `platform/plugins/farmart-helper/src/Supports/PaymentSnapshot.php`: `b0f15a212dc24060ba43bbe2d714839f29d8c7695b595d2964ebf2d8935af5a6`
- `platform/plugins/farmart-helper/src/Supports/CheckoutOrderHelper.php`: `5ba64f3a81c8bfc59766ec38d09d133fdb7202772c0c61115f87ac484b03af0c`
- `platform/plugins/farmart-helper/src/Supports/CheckoutPaymentPolicy.php`: `e90b5b9a28bf5410e4e03f5ba6ce24ae915130bf7512d345fb7ad681951c63fb`
- `platform/plugins/farmart-helper/src/Providers/CheckoutIntegrityServiceProvider.php`: `317ff1a72459c0bc27f992c9eaf1cb93bd223f72ac9507061c397689f3facbe7`
- `platform/plugins/advanced-cod/src/Supports/CodAmounts.php`: `34aa71a6484b48da3ddfd44ecf8fb4aa517171c13d21f6426773e6d7f313cadb`
- `platform/plugins/advanced-cod/src/Supports/AdvancePayment.php`: `066ae65f71e2283d744f3b83575a7427632d199061e1975735a1f87924603333`
- `platform/plugins/advanced-cod/src/Supports/AdvancePaymentAction.php`: `27d8fef25dab25ae809eb7a5e37307b71945a89bf6ac1e4f7e16b6d97ddffb20`
- `platform/plugins/shipmozo/src/Supports/CollectionAmount.php`: `24efb512fbf7f9177f47cb68595b5f36b5aa89c0874ee145678ed08f6839055b`
- `platform/plugins/farmart-helper/src/Http/Controllers/PartialPaymentRazorpayController.php`: `44c3fd2189cca8144122ca6460d9cc29c9248240289bbdc7a215e4515a78a7b9`
- `platform/plugins/farmart-helper/src/Http/Middleware/RestartChangedCheckout.php`: `ba4e63373827662c32a1b5364c8af6dac720c4fc874a96e2a6be4b27ca9a1cb7`
- `platform/plugins/farmart-helper/src/Supports/FinalizeCheckoutDiscount.php`: `05b914e6f076c6de28216b8270b0a0f873e1dea036d7857bbe4bd3feb9a273b3`
- `platform/plugins/farmart-helper/src/Listeners/FinalizeLoyaltySnapshot.php`: `115d4e8507bfb8ce4da0bb72ce4af903e5c9867611cd3b9075e25a3cd0d0e854`
- `platform/plugins/farmart-helper/src/Listeners/PreserveCheckoutWholesalePrices.php`: `f5f95dbb99e4288669808d52681864bb862d090d2e67e9a60522b9f9d45e6fe5`

## Live verification
All 16 deployed SHA256 hashes match local files. PHP lint and application boot passed; views cleared.
Single-product live UI: item 249 + Shipmozo 64 + COD fee 12.52 = total 325.52; 30% advance 97.66, remaining 227.86.
Multi-vendor live UI: items 5798 + Shipmozo 64 + COD fee 234.48 = total 6096.48; advance 1828.94, remaining 4267.54. Includes another vendor's free delivery.
Unpaid verification orders under the test cart: 18929 and 18942; no payment rows or completion at audit time. Existing order 18929 belonged to this earlier test cart; no historical manual repair was performed.
Final external payment-page verification is pending user permission after the browser approval denial. No captured payment or live successful webhook was simulated.
Additional local Shipmozo model tests pass: partial balance, refunded advance blocked, regular COD full collection, normal prepaid zero collection.
Evidence: outputs/cod-checkout-20261019/live-checkout.jpg

Final local checks also pass: captured advance amount immutable under core recalculation; locked item deletion blocked by Advanced COD independently.
Existing Indian GST subtotal regression also passed unchanged (inclusive/exclusive tax, wholesale discount, mixed basket, vendor slice, address-save recalculation). Live application boot confirmed with COD percentage 30; recent application error scan returned zero new errors. External payment-page permission remains pending.

## Authorized Razorpay page verification completed
After the user explicitly approved opening Razorpay without paying, the live hosted page showed Amount payable ₹1,828.94 for orders 18929 and 18942. No payment fields, OTP, or UPI approval were entered. Cancel Payment returned to checkout with both items, shipping ₹64, COD fee ₹234.48, total ₹6,096.48, advance ₹1,828.94 and remaining ₹4,267.54 unchanged. Back to cart retained both items (merchandise total ₹5,798) and the same checkout link. This resolves the earlier external-page verification blocker; captured-payment/webhook verification remains covered by local tests, not an actual live payment.
Evidence: outputs/cod-checkout-20261019/live-razorpay-advance.jpg
