# ShipMozo checkout integration

Ownership: all checkout customizations live in this plugin. Do not edit Botble
Ecommerce, its assets, or the parent theme.

- The provider binds the quote controller and shipping service only when ShipMozo
  is enabled. Destination validation, saved-address ownership and tax-before-quote
  live in `UpdateCheckoutController`. `CheckoutShippingFeeService` overrides only
  `execute`; upstream calculation/cache methods remain inherited.
- The checkout GET route adds `CheckoutAssets` middleware. It replaces only the
  Ecommerce checkout script URL in the rendered HTML with this plugin's versioned
  asset; it does not replace the upstream Blade layout or run two checkout scripts.
- The shipping-option view override applies only to ShipMozo options.
- `resources/js/checkout.js` is an owned integration derived from Ecommerce at
  commit `6eb99883c`, retaining its normal checkout behavior and importing its
  address/discount helpers. It adds serialized destination refresh, stale-response
  protection, unavailable-state blocking and retry.
- Build `checkout` through this plugin's `vite.build.mjs`; publish its own
  `public/js/checkout.js` to `public/vendor/core/plugins/shipmozo/js/checkout.js`.

Upgrades will not overwrite these files. They still require compatibility review:
compare the upstream quote controller, shipping service `execute`, checkout runtime
and helper interfaces after a Botble upgrade, then run the focused regressions.
Do not assume update compatibility merely because overrides survive.

Regression scripts are in `tests/checkout-{destination,serviceability}.php` and
`tests/checkout-refresh.cjs`. They use isolated fixtures, not live orders/payments.
