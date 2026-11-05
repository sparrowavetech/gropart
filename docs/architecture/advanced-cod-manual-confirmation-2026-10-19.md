# Advanced COD manual confirmation and order tags

Implemented locally and deployed on 19 October 2026. Changes are limited to Advanced COD and Shipmozo's collection resolver. No core, theme, Farmart Helper, Razorpay, Wholesale, Loyalty or Indian GST files were edited for this release.

## Admin workflow

On incomplete orders, choosing COD in the existing completion modal replaces the generic submit/payment-status inputs with **Review Advanced COD & place order**. The review page offers:

- A customer checkout/retry link while keeping the order incomplete. It does not send an email or manufacture a gateway payment link. The customer selects COD and reviews checkout totals.
- Use an existing verified advance: matches the saved intent, gateway, currency, amount and refund state. Typing a transaction ID cannot mark money received.
- Record a manually received advance: cash, bank or UPI; exact required advance, receipt reference, received-at time and evidence note required. A separate `advanced_cod_manual` payment channel records actual manual money without impersonating Razorpay or another gateway. The payment/actor/source/evidence is recorded in order history and metadata.
- Full COD without advance: available only when the COD payment-method setting **Allow authorized admins to place full COD without advance** is enabled AND the actor has the override permission. This setting remains disabled locally/live by default. It changes neither customer checkout requirements nor other product eligibility settings.

Existing payment intents keep their saved economic fields. Unlocked conversion replaces the prior payment fee with the configured COD fee, leaving shipping, tax and discounts unchanged. Preview fingerprints detect stale order/settings data. Active/locked attempts block manual receipts and full-COD conversion until reconciled. Product COD eligibility and global COD enablement are checked server-side.

Permissions: `orders.edit` plus `advanced-cod.manual-confirm`; manual money additionally requires `advanced-cod.manual-receipt`; full-COD overrides additionally require `advanced-cod.override`. No roles/users were granted permissions during deployment. Existing super-admin access is honored.

Duplicate submissions are idempotent; changed duplicate payloads fail. Receipt references are serialized with database advisory locks and cannot be reused to pay another order. The legacy COD completion endpoint is guarded server-side while this plugin is active.

## Tags and balances

Incomplete-order and order-details screens show saved free/paid delivery status, current overall COD eligibility, and per-product COD/free-delivery eligibility. Variation COD eligibility follows the configurable parent, matching checkout. Tags explicitly distinguish current product settings from saved historical shipping charges; no historical eligibility is invented.

Settlement summary separates online receipts and manual advances. Shipmozo and default shipment COD amounts use the actual remaining collection balance. Default shipment status cannot be marked collected without actual settlement evidence. Full-COD orders remain unpaid until collection; gateway payment records are not rewritten as full receipts. Cash/manual receipt refunds use the settlement refund history, with reconciliation required.

Plugin deactivation removes its registered routes, middleware, enum extension, tags and modal adaptation. Ordinary COD/prepaid shipping fallback remains intact. Disabling a financial plugin does not erase its historical records.

## Validation

- Manual confirmation tests: fee/30% split, unpaid rejection, wrong manual amount, real manual receipt separation, verified gateway receipt, duplicate submission/payload/reference across orders, stale preview, pending-payment lock, disabled/authorized full COD, permission denial, ineligible products and legacy endpoint bypass protection.
- Settlement regressions: partial/full/refund/reversal, immutable history, invoice statuses, default shipment balance/status guard, Shipmozo remaining, duplicate callbacks and normal invoices.
- Existing advance payment, cart retry, wholesale/loyalty discount and normal COD/prepaid shipping tests passed.
- Local browser: incomplete-order tags, COD modal routing, manual receipt fields, actual received/outstanding preview, and wrong-amount rejection. Successful financial paths were tested in rolled-back local transactions with notifications faked. Disposable browser fixtures were removed.
- Live read-only browser: incomplete order 18929, its new confirmation screen, and order-details 18908 show the new flow/tags. Product 4095 currently has COD disabled (confirmed directly in live data), so 18929's confirmation is correctly disabled. Product settings were not changed.
- All 16 deployed file hashes match local. Nine pre-existing Advanced COD orders' monetary/payment/status fields compared to backup with zero changes. No live manual receipts or cash settlement entries were created. No new error lines were found in the checked daily log after deployment.

## Deployment and backup

Backup directory: `/home/u834473639/codex-backups/cod-manual-confirmation-20261019-1020/`.
Contains previous plugin archive, financial snapshot, deployed package, SHA-256 manifest and staging files. No schema migration needed. Views/routes were cleared. Source backup also covers the final default-shipping adapter.

Evidence: `outputs/cod-manual-20261019/live-confirmation.jpg` and `live-order-tags.jpg`.

For rollback, restore only backed-up code and remove newly added files from the deployment manifest as appropriate. Preserve post-deployment manual receipts/settlement history; do not blindly restore financial snapshots.

## UI correction (19 October)
- Removed separate Delivery/COD box. Existing enabled-product green badges now render within the product row, on both order details and the Farmart Helper incomplete view.
- Manual confirmation is restricted in UI, GET, and submission service to unfinished/non-canceled orders with saved Advanced COD intent or COD payment attempt. Ordinary incomplete orders keep their normal completion flow. Completed confirmations retain idempotent retry behavior.
- Manual receipt fields use a responsive two-column Botble grid.
- Local rollback regression suite passed; local browser verified both product screens and two-column field positions. Live verified paid order 18908 has no manual entry, COD attempt 18929 has entry, non-COD incomplete 18937 has green badges but no manual entry.
- Seven custom plugin files deployed with matching SHA-256 hashes. Backup: /home/u834473639/codex-backups/cod-ui-correction-20261019-1102/before.tgz. No live order/payment records changed.
