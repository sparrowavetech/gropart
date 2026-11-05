# Local functional test (development preview)

Use a local Botble install with Ecommerce, Marketplace and Seller Invoicing activated. Sign in as an admin with `seller-invoicing.view` and `seller-invoicing.manage` permissions.

1. Open **Marketplace → Seller fee library** (`/admin/seller-invoicing`). The current local test record for order `10814` should be listed as `needs_review`.
2. Open the order record (`/admin/seller-invoicing/orders/10814`). Confirm the captured merchandise base minus the existing Marketplace fee equals the existing seller net. Customer shipping is displayed separately with allocation pending. This is a review record, not a tax invoice.
3. Open `/admin/seller-invoicing/orders/11138`. This local order is `processing` and has no Marketplace revenue. The page should explain why preparation is unavailable and show no prepare button.
4. For another **completed marketplace vendor order that already has an `mp_customer_revenues` add-amount row**, open its seller fee record from the admin order page and click **Prepare review snapshot**. The button sends a POST request; do not paste the `/prepare` URL in the address bar. Confirm the result appears once in the library. Reopening the page should show the same snapshot.
5. Sign in as that seller and open the seller fee library in the vendor dashboard. Confirm the seller sees only their own order records. A different seller's order URL must return 404. Admins with view-only permission should see records but no prepare button.

Expected limits of version 0.1.0: no tax invoice or PDF download, no extra fee deduction, no shipping liability decision, no credit note, and no filing export. Preparing a historical order records the **current** Marketplace settings as review evidence; it does not establish the original order-time policy. Do not complete an order solely to create a test record, because normal completion can affect Marketplace revenue, seller balance and fulfillment workflows.

Focused developer checks from the project root:

```sh
php platform/plugins/seller-invoicing/tests/routes-smoke.php
php platform/plugins/seller-invoicing/tests/money-reconciliation.php
php platform/plugins/seller-invoicing/tests/snapshot-smoke.php
php platform/plugins/seller-invoicing/tests/blade-compile.php
```

The snapshot smoke test uses a database transaction and rolls it back. The UI preparation action persists a **local** review record; it does not change Ecommerce orders, Marketplace revenue, seller balance, payment or shipping rows.
