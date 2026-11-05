# Advanced COD collection and settlement — 19 October 2026

Implemented locally, tested, and deployed to gropart.com. Core, theme, Ecommerce, Razorpay, Wholesale, Loyalty, Indian GST and Farmart Helper files were not modified for this change.

## Behaviour

- Advanced COD orders (positive saved advance) show an order/incomplete-order/invoice/PDF summary: total, included cash collection charge, required advance, actual net online receipts, cash collected/refunded, net received and customer outstanding.
- Before successful advance, the full total remains outstanding; the planned COD balance is not presented as money received.
- Partial advance makes the invoice `processing`, with no paid timestamp. Full customer payment makes it `completed`. Refunds/cancellation require reconciliation. The online gateway transaction amount/status is not changed by cash settlement.
- Customer collection and courier remittance are separate. Record net remittance plus courier deductions against money actually collected by the courier. Deductions never increase the customer fee or total. Direct merchant collections do not create courier receivables.
- Admin permission `advanced-cod.settle` AND `orders.edit` is required to view/use the settlement management page. Super administrators have their existing full access. No user/role permissions were granted during deployment.
- Use **Manage COD collection / remittance** in the Advanced COD summary. Record a collection only after receiving evidence, then courier remittance when received. A reference, evidence note and occurrence time are mandatory. The history records actor and timestamps.
- Entries are append-only. Correct mistakes with reversals; dependent remittances/refunds must be reversed first. Submission UUID and reference uniqueness prevent duplicates. Order-row locks serialize balance changes.
- Delivered/completed fulfilment never automatically fabricates a cash receipt. An Advanced COD adapter replaces the core invoice-completion listener through the container, leaving its original behaviour for other orders.
- Shipmozo's collection resolver calls a generic filter so already-collected cash is excluded. All settlement rules remain in Advanced COD. No courier API remittance import is enabled; settlement uses evidence entered by authorized staff.
- Duplicate advance callbacks preserve the settled remaining balance and cannot erase a recorded refund status.
- Cached generated invoice PDFs are invalidated when receipt/settlement state changes.

## Changed deployment files

Advanced COD: permissions config, web routes, main provider, new SettlementServiceProvider, AdvancePayment, new Settlement service, SettlementEntry model, SettlementController, completion-listener adapter, summary/manage Blade views, and the `advanced_cod_entries` migration.
Shipmozo: `src/Supports/CollectionAmount.php` (generic collection-balance filter only).
13 deployed files; SHA-256 verified against local manifest.

## Verification

- Transaction-rolled-back settlement tests: unpaid/partial/full, overcollection/overremittance, duplicate submission/reference, reversals/dependencies, cash refunds, online refunds, immutable ledger, Shipmozo remaining, duplicate callbacks, unaffected regular invoices, fulfilment listener and permission denial.
- Existing advance/multi-vendor allocation, cart retry, wholesale/loyalty, checkout integrity, payment snapshot, Shipmozo collection regression scripts passed.
- Local browser: actual settlement POST recorded 227.86 customer collection against 97.66 online advance on a 325.52 fixture, then 220.00 remittance + 7.86 deductions. Both balances reconciled. The disposable local fixture was removed afterward.
- Actual custom invoice PDFs generated for partial and fully paid states. Both are one page for the fixture; partial invoice has fee 12.52, advance 97.66, balance 227.86, partial-payment label and no paid timestamp.
- Live browser: incomplete order 18929 and its settlement management page show fee 12.52, required advance 97.66, received 0, outstanding 325.52, planned COD 227.86.
- Live order 18942: total 5770.96, fee 221.96, required advance 1731.28, received 0, outstanding 5770.96, planned COD 4039.68.
- Live invoice/PDF hook rendering checked with an unsaved invoice reference; there were no successfully paid Advanced COD orders/invoices available for a real live paid-invoice test.
- Live cash ledger remains empty. No live payment, cash collection, refund, order status or historical amount was changed. Compared all 9 pre-existing Advanced COD orders' monetary/payment/status fields to backup: zero differences. No new error lines after deployment in the checked daily log.

## Backup and rollback

Server backup: `/home/u834473639/codex-backups/cod-settlement-20261019-0940/`

Contains `plugins-before.tgz`, `data-before.json`, deployment archive, SHA-256 manifest and staging files. The migration was run before enabling its provider. Views/routes were cleared after deployment.

Rollback code only from the backup if required; preserve `advanced_cod_entries` and any settlement records entered after deployment. Do not blindly restore monetary snapshots or drop the ledger. Reconcile any post-deployment receipts before disabling this feature.

Evidence: `outputs/cod-settlement-20261019/` (local partial/full PDFs, local settlement screenshot, live incomplete-order and settlement screenshots).
