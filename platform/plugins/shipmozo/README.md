# ShipMozo Shipping for Botble Ecommerce

ShipMozo rates, courier assignment, pickup scheduling, labels, tracking, cancellation, returns, NDR actions, pincode checks, and optional Marketplace warehouse synchronization for Botble Ecommerce.

## Requirements

- Botble CMS 7.6 or newer
- Ecommerce plugin
- PHP 8.2 or newer
- Marketplace plugin only when multi-vendor warehouse support is needed

## Installation

1. Copy `shipmozo` to `platform/plugins/shipmozo`.
2. Activate Ecommerce, then activate ShipMozo from Admin > Plugins.
3. Run pending migrations if plugin activation does not run them automatically.
4. Open Ecommerce shipping settings and configure the ShipMozo public and private keys.
5. Keep webhooks disabled unless ShipMozo has supplied and confirmed a callback contract for your account. Webhooks and NDR APIs are retained as integration extensions but are not described in the supplied 19-page API guide.

The same credentials can be configured without changing catalog or location data:

```bash
php artisan cms:shipmozo:init \
  --public-key=PUBLIC_KEY \
  --private-key=PRIVATE_KEY \
  --webhook-secret=WEBHOOK_SECRET
```

## Webhook

The supplied API guide does not document webhooks. The optional compatibility endpoint is `/shipmozo/webhooks`; only enable it after ShipMozo confirms the payload and authenticate with one of:

- `Authorization: Bearer WEBHOOK_SECRET`
- `X-Shipmozo-Token: WEBHOOK_SECRET`
- `X-Webhook-Token: WEBHOOK_SECRET`

The legacy `_token` request field is also accepted for compatibility.

## Documented order flow

The plugin follows the API guide's required sequence: `push-order`, then `assign-courier` for a checkout-selected courier (or `auto-assign-order` when no courier ID exists), followed by `get-order-detail` and `schedule-pickup` when manual pickup is required. Only an actual `awb_number` is stored as the Botble tracking ID.

Labels are decoded from the documented base64 PNG response and served through a signed, rate-limited application URL. Cancellation sends both the original ShipMozo `order_id` and scalar `awb_number`; tracking uses the documented `track-order?awb_number=` query.

## Assets

The plugin includes both the project Vite descriptor and the legacy Laravel Mix descriptor. In this project, install root Node dependencies and run `npm run prod` to rebuild plugin assets.

## Support

Sparrowave Solutions: https://www.sparrowave.com
