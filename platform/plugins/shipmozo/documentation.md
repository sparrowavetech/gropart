# ShipMozo Architecture

## Checkout rates

`HookServiceProvider::handleShippingFee()` adds ShipMozo rates through Botble's `handle_shipping_fee` filter. Existing Botble rates remain available when ShipMozo is disabled, unavailable, or returns no serviceable rates.

Rate requests use the destination pincode, Ecommerce origin, cart product value, dimensions, weight, and payment method. Botble kilogram values are converted to grams. API `courier_id` values and automatic-pickup flags are preserved in Botble's shipping option so the selected courier can be assigned later. Rates can receive a fixed or percentage adjustment.

## Order lifecycle

Only orders whose stored shipping method is `shipmozo` are handled.

- Order confirmation creates a missing Botble shipment and pushes it once under a cache lock.
- A selected courier follows `push-order -> assign-courier -> get-order-detail`; manual pickups additionally call `schedule-pickup`.
- Orders without a numeric selected courier use the documented `auto-assign-order` fallback, which requires auto assignment in the ShipMozo panel.
- Only `data.awb_number` from assignment/pickup/detail responses becomes the Botble tracking ID. A ShipMozo order ID is never treated as an AWB.
- Manual shipment creation uses the same order lock to prevent concurrent AWB requests.
- Cancellation and completed-return events are sent only for ShipMozo orders.
- Cancellation includes the original order ID and scalar AWB. Returns include ShipMozo's required date, pickup fields, payment type, kilogram weight, reason ID, and customer request.
- Label responses are base64 PNG data; a signed proxy route decodes and serves them without storing oversized data URIs in the shipment row.
- Webhooks map carrier states to Botble shipment states and ignore duplicate state updates, but remain disabled by default because they are absent from the supplied API guide.

Non-idempotent order, assignment, pickup, cancellation, return, and warehouse POST requests use timeouts but are not automatically retried. Read-only requests and rate/serviceability requests use bounded retries.

NDR management remains available through the plugin's existing `get-ndr-all` and `ndr-action` integration. These endpoints are not defined by the supplied API guide and require account-level verification with ShipMozo.

## Marketplace integration

Marketplace is optional. When it is active, the migration adds nullable `warehouse_id` to `mp_stores`. Every Marketplace hook checks that the table and column exist before use. Store forms can link an existing ShipMozo warehouse or create one from store data. Without Marketplace, the active default ShipMozo warehouse is used; if none exists, the Ecommerce origin is registered through the documented warehouse payload.

The plugin does not patch Botble Ecommerce or Marketplace core files.

## Frontend integration

The pincode checker uses Botble's `ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML` hook and the selected product's actual shipping attributes. Tracking is injected on Botble's public tracking and customer order-detail routes, with generic fallback containers for themes that retain the standard Ecommerce views.

Public tracking requires the same order code plus email/phone verification used by Botble. Customer-account tracking requires ownership of the requested order.

## Security

- Settings accept an explicit whitelist with validation.
- Webhooks require a dedicated secret and constant-time comparison.
- Public endpoints are rate limited.
- Log viewing is limited to escaped `shipmozo*.log` files.
- API logs are disabled by default and redact credential and customer fields.
