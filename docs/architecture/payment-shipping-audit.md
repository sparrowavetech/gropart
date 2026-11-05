# Payment and shipping customization audit

**Implementation update:** The migration below is now implemented locally inside the existing Farmart Helper plugin (1.1.0), not a new plugin. See its README. The three emergency vendor patches have been removed locally and on the live server. Deployment on 17 October 2026 passed production failed-webhook replay and shipping-total checks. Backup: `/home/u834473639/codex-backups/farmart-helper-20261017-100107`. The original audit findings below are retained as historical context.

Date: 17 October 2026. Scope: local source audit, local configuration read, official documentation research, and an in-memory reproduction. No live server access or functional source edits were made during this audit. Existing emergency patches remain in place.

## Conclusions

1. The failed-payment finalization defect is real in this installation. The earlier live investigation established a failed, uncaptured Razorpay payment followed by order finalization and an order-confirmation email history entry.
2. Editing all three vendor-owned files was not the only implementation option. The controller gate fixed the immediate hosted-webhook path. The helper and marketplace gates protect additional entry points. The shipping change addresses a separate total-consistency defect.
3. These are vendor-owned plugin files, even though they are outside platform/core. Vendor updates can overwrite the patches. A dedicated Gropart-owned plugin is a better permanent home. The emergency edits should have been described as temporary vendor patches with an explicit follow-up migration plan.
4. ShipMozo is not required to reproduce the shipping-total defect. No evidence found identifies its rate provider as the cause of the failed-payment finalization.
5. This does not establish that an untouched current Botble author release contains the same defects. The repository has a long update/customization history; a clean matching author package was not available for a baseline comparison.

## What each existing change does

| Vendor file | Change | Assessment |
|---|---|---|
| platform/plugins/razorpay/src/Http/Controllers/RazorpayController.php | Only dispatch PAYMENT_ACTION_PAYMENT_PROCESSED for COMPLETED in hosted webhook/callback paths | Direct fix for the observed failed-webhook behavior; equivalent prevention is necessary, but this file need not be permanently edited. |
| Same controller | For uncaptured attempts, use the payment entity amount rather than the order's amount_paid fallback | Accounting distinction: attempt amount and collected amount are not the same. A failed payment may have amount 304 while captured money is zero. Separate from order finalization. |
| platform/plugins/ecommerce/src/Supports/OrderHelper.php | Do not finalize linked Razorpay payments unless completed | Additional protection, not the sole fix for the specific hosted webhook. Also useful because the embedded Razorpay provider can dispatch the payment action with PENDING. COD and bank-transfer policy must remain separate. |
| Same helper | Include retained shipping/shipping tax when replacing total during marketplace cart refresh | Fixes an independently reproduced inconsistency. This belongs in checkout-total handling, not necessarily in ShipMozo. |
| platform/plugins/marketplace/src/Providers/OrderSupportServiceProvider.php | Guard unfinished Razorpay orders on the success-page path | Separate bypass: this provider directly sets is_finished and decreases inventory, without calling OrderHelper::processOrder. A helper-only replacement does not cover it. |

## Failed-payment path

Before the patch, the Razorpay controller could determine FAILED and still dispatch PAYMENT_ACTION_PAYMENT_PROCESSED. Ecommerce's action callback (HookServiceProvider, around lines 455–509) stores the payment, then invokes OrderHelper::processOrder without requiring a successful online payment. A payment_id satisfies the existing presence check. OrderPlacedEvent runs before is_finished is saved, and inventory is subsequently reduced.

A later observer that changes is_finished back to false is too late: email, invoice, other listeners and stock effects may already have run. Similarly, blocking an Eloquent save alone is insufficient when OrderPlacedEvent precedes it.

The additional marketplace success handler has its own direct finalization code. A permanent solution must protect it separately or redirect unsuccessful online payments before that handler runs.

## Shipping reproduction and confidence

Ran the repository HEAD version of OrderHelper under a temporary alternate class name and compared it with the patched version. Both used the same in-memory Order and stubbed Cart: product including tax 230, shipping 74, original total 304, changed cart timestamp.

Result:

```
Before patch: total=230, shipping=74
After patch:  total=304, shipping=74
No order saved; no ShipMozo rate method invoked.
```

The older processOrderInCheckout builds amount from rawTotalByItems + payment fee, then fills that amount onto an existing order without clearing its existing shipping_amount. Marketplace's caller subsequently saves the order. The patched method includes retained shipping and shipping tax.

The historical live order had subtotal 194.92 + tax 35.08 + shipping 74 = 304, while stored amount was 230. Razorpay had received 304. This reproduction explains that exact inconsistent state. There was no historical write trace proving which HTTP request performed the overwrite, so the exact original request/race remains unproven. The earlier claim of a fully traced historical cause should be read with that limitation.

## ShipMozo and adjacent custom plugins

- ShipMozo registers handle_shipping_fee at priority 11 and supplies rates. This method does not set is_finished and does not dispatch the payment-processed action.
- Its booking listener responds to OrderConfirmedEvent, not Razorpay payment.failed. The live affected order had is_confirmed=0 and no shipment, which does not support the booking listener being involved.
- ShipMozo's admin updateRate does change the order total using old/new shipping delta. It requires a shipment; no shipment existed for this incident. This path does not recompute shipping_tax_amount, so it merits separate testing if taxable shipping/rate reassignment is used; it is not evidence for this incident.
- Indian GST's shipping hook currently returns the supplied rates unchanged.
- Product Free Shipping runs later on handle_shipping_fee at priority 125 and can alter rate/weight results. Its interaction with ShipMozo must be covered by future integration tests. No evidence from the affected order shows a free-shipping reduction: shipping remained 74.
- Advanced COD hooks payment processing at priority 200, and its success handler explicitly returns for a non-completed status. Partial deposit flows must be preserved in any migration; a universal “all non-completed payments must never place orders” rule would break legitimate offline payment flows.
- Farmart Helper currently covers forms/phone handling/tracking, not payment guards. Prefer a distinct business plugin rather than hiding payment fixes in unrelated functionality.

## Verified extension points and an update-resilient design

Suggested plugin: platform/plugins/gropart-checkout-integrity, with its own provider, tests and declared ecommerce/payment/marketplace/razorpay dependencies. This is a design recommendation, not an installed or tested replacement plugin.

1. Bind Botble\Ecommerce\Supports\OrderHelper to a custom subclass in the plugin. The facade resolves that class through the container. Override processOrder to exclude failed/pending online-payment orders before any parent finalization work. Override processOrderInCheckout narrowly to preserve shipping after the parent calculation, respecting whether the order/cart was actually rebuilt. Avoid copying the entire 2,000-line helper.
2. Attach a narrow middleware to public.checkout.success. Before the controller/marketplace hook runs, route unsuccessful online-payment orders to an appropriate retry/pending response. Evaluate all sub-orders for a marketplace token. This avoids editing OrderSupportServiceProvider and prevents false success-page side effects.
3. If retaining exactly the current gateway behavior (failed attempts never dispatch the placement action, and exact attempt amount is saved), use a custom Razorpay controller adapter resolved through Laravel's container. The routes use RazorpayController::class; the webhook handler is protected and overridable. Callback handling needs deliberate implementation, not just a filter added after the fact. Preserve signature verification, order ownership/amount validation, duplicate-event handling, successful-payment protection and refunds. A controller adapter may need selected method overrides; document those upstream dependencies and test them after updates.
4. An alternative is a narrowly scoped decorator of the core.action service that handles only the Razorpay payment-processed action and delegates every other action. The accessor is literally core.action. This avoids vendor edits but touches a shared dispatch service, so it is a higher-risk alternative, not the default recommendation. It must preserve listener registration/state and the existing signature/security boundary.

There is no verified existing “cancel payment processing” filter at the exact finalization branch. Simply adding an action callback and returning false cannot cancel the other callbacks in this installation.

### Hook implementation details that matter here

- Action::fire uses krsort: higher priorities run first.
- Filter::fire uses ksort: lower priorities run first.
- Equal priorities are incremented until an unused slot is found.
- Action::fire ignores callback return values.
- remove_filter removes all listeners for a hook, not a named callback. The generic ActionHookEvent::removeListener also removes the whole hook. Do not remove/recreate a payment hook casually: Advanced COD and future plugins share it.
- All bindings and middleware must be installed at the appropriate provider lifecycle point, and any already-resolved facade instance must be handled. Plugin activation order is not a sufficient substitute for explicit lifecycle integration.

## Migration and verification sequence

Keep the current protection until the replacement passes on a local/staging copy. Save the exact current diff. Add and verify the custom plugin with the three vendor files restored only to their pre-session versions; do not restore whole folders or erase older project customizations. Deploy the replacement plugin and remove the emergency patches together. A clean author package comparison is a separate upstream-attribution exercise.

Required scenarios: failed and pending webhook/callback; captured payment; duplicate and out-of-order events; failed attempt after a successful different charge; COD; bank transfer; Advanced COD deposit; refunds; marketplace multiple stores; reopening the success URL; cart quantity changes; shipping switch; free shipping; shipping tax; coupons; wholesale pricing; loyalty discounts. Verify order/payment/invoice totals and that inventory, emails and invoices occur exactly as intended.

## Validation performed and limits

- This audit: old/new in-memory shipping reproduction, with no saved orders and no shipping API call.
- Earlier fix verification: six focused local regression assertions, PHP syntax checks, live failed-webhook replay preserving incomplete order/stock/history, and a homepage HTTP 200 check.
- Full PHPUnit execution was unavailable because the installed local dependency tree could not load PHPUnit\TextUI\Application. The standalone regression script was used instead; do not claim a full test-suite pass.
- All 61 plugin manifests and 2,427 source/route/helper PHP files were indexed for integration points. Manual deep reading focused on the critical paths listed above. Other plugin behaviors, every theme screen, mobile artifacts and all runtime integrations have not been exhaustively tested.
- Local timezone is UTC while the prior live inspection reported Asia/Kolkata. Local active-plugin and runtime settings are not a fresh inventory of production.

## Official documentation consulted

- [Farmart overview](https://docs.botble.com/farmart/)
- [Plugin structure and provider lifecycle](https://docs.botble.com/cms/plugin-structure.html)
- [Filters](https://docs.botble.com/cms/filters.html)
- [Actions](https://docs.botble.com/cms/actions.html)
- [Child theme development](https://docs.botble.com/cms/child-theme-development.html)
- [Theme customization best practices](https://docs.botble.com/cms/theme-development/theme-customization-best-practices.html)
- [Upgrade guide](https://docs.botble.com/cms/upgrade.html)

Documentation supports custom plugins and child themes as extension mechanisms. The exact feasibility and hook-order cautions above come from this project's source, not an assumption that every documented example matches this installed version.
