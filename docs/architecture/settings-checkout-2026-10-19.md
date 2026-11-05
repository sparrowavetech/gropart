# Settings-based Razorpay checkout and independent plugin integrations

Implemented locally and deployed 19 October 2026. Backup: `/home/u834473639/codex-backups/settings-checkout-20261019-1133/before.tgz`; manifest alongside it. 18 custom plugin files deployed, five unused Indian GST compatibility shims removed. All live before/after hashes verified. Core, Razorpay, wholesale and loyalty sources unchanged.

## Ownership
- Farmart Helper replaces only Razorpay's two checkout callbacks, retaining other hooks and the configured payment mode. Rendering choices no longer creates a gateway order. Standard mode creates the order after authoritative checkout/payment-data filters, then launches checkout.js using its order ID and existing signed callback. Embedded mode delegates to the original core provider. No payment-mode setting-key override remains.
- Advanced COD owns nearest-whole-unit advance arithmetic, exact remaining amount, existing frozen intent preservation, its gateway-attempt metadata and checkout UI. Multi-vendor allocations sum to the rounded checkout-wide payment and reconcile each order.
- Generic checkout lock/restart/payment-attempt hooks replace Helper references to Advanced COD metadata/session internals. Helper retains third-party gateway/core integrity adapters.
- Product Free Shipping owns its own order badges and free-shipping eligibility filter; Shipmozo consumes that optional filter. Advanced COD owns only its COD badge.
- Vendor Verified Badge owns the store-type filter; Enquiry Products consumes it with an empty fallback.
- Indian GST already owns its implementation. Five unregistered Helper compatibility subclasses were removed.

## Validation
Local rollback tests passed: COD arithmetic (including 884 @38% =336+548 and 898.56=341+557.56), multi-order capture and duplicate protection, manual confirmation, cash settlement/invoices, cart retry, wholesale and loyalty. Fake gateway Standard mode test verifies exact amount, order binding, configured mode, single callback replacement and existing-intent preservation; no real payment made locally.

Isolated application boots (activation settings unchanged): Advanced COD without Helper/Shipmozo/Razorpay/Free Shipping correctly processes an alternate gateway receipt; Shipmozo without Advanced COD/Helper/Free Shipping correctly processes regular COD. Seven SparroWave PHP source trees scanned: no direct foreign SparroWave class references remain. This is a bounded integration audit, not exhaustive testing of every theme/plugin configuration; normal ecommerce/payment prerequisites remain necessary.

Local browser checkout showed rounded split. Live browser fresh checkout showed total 898.56, online 341.00, COD 557.56. Saved order 18949 retained total 898.56 and its older locked split 341.45/557.11. No existing payment or order status was edited.

Live payment launch remains unverified: automatic approval review blocked the Checkout click because it creates a live order/payment attempt. Await explicit permission for an unpaid test attempt using the user-provided test contact details. No payment/OTP/UPI approval should be performed. Current verification checkout token: 4d686d99c85a44a9790325f7c6b2ef59. Test basket quantity restored to one. Screenshot: outputs/settings-checkout/live-rounded-cod.png.
