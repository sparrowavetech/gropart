# Product Bundles (Combo & Mix-and-Match) — Botble Plugin

This plugin adds **product bundles** (combos) to Botble eCommerce:

- **Fixed bundle**: predefined items and quantities.
- **Mix-and-match**: groups of items where the customer can choose *N out of M*.
- **Pricing rules**: fixed total price, percent discount, or amount discount.
- **Bundle display name** is stored per cart line so it can be shown in cart/checkout/order item options.

## Install

1. Copy folder `product-bundles` into `platform/plugins/product-bundles`
2. Run `composer dump-autoload` (if needed)
3. Go to **Admin → Plugins** and activate **Product Bundles**

Migrations will run automatically when activating.

## Admin

Admin menu: **Ecommerce → Bundles**

- Create a bundle, set type/pricing, attach it to product IDs where you want it to appear.
- For fixed bundles: add items (product ID + qty).
- For mix-and-match: create groups, set min/max, then add group items.

## Frontend integration (MVP)

The plugin ships a Blade component you can place in your product detail template:

```blade
@include('plugins/product-bundles::front.box', ['productId' => $product->id])
```

Also include the JS once on the page (or in your theme bundle):

```blade
{!! app('product-bundles')->assets() !!}
```

> Note: themes differ. This MVP intentionally avoids hard-binding to a theme hook.

## Notes / Limitations (MVP scope)

- Inventory sync is handled by normal product stock logic because the bundle adds *real products* to cart.
- Pricing rule **fixed total price** is implemented by distributing price across items at add-to-cart time.
- Analytics tables are included (for future), but automatic revenue attribution on order placement may require wiring to your project’s order events.

## License

MIT
