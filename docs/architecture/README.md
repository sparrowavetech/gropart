# Gropart project map

**Migration update:** Farmart Helper 1.1.0 now owns the checkout guards and shipping preservation locally. Original inventory/counts below describe the preceding audit snapshot. See `platform/plugins/farmart-helper/README.md` for implementation and deployment guidance.

Audit date: 17 October 2026. Local project only for this review.

## Coverage and limits

Inventoried all 61 plugin manifests and indexed 2427 PHP source/route/helper files for integration points. 53 plugins are active in the local database. Manually traced checkout, Razorpay, order finalization, shipping, Advanced COD, GST, free-shipping, helper, and child-theme boundaries. This is an architecture and targeted bug audit, not an assertion that every line or every plugin behavior has been tested. Dependencies, uploads, generated assets, and mobile-app artifacts are not exhaustively reviewed.

## Setup

- Laravel requirement: ^13.0; PHP requirement: ^8.3|^8.4 (composer.json).
- Product version marker: 1.24.12 in platform/core/core.json; theme manifest version 1.1.0 is a separate identifier.
- Local active theme: farmart-child; config.php inherits farmart. Child folder contains 102 files.
- Local runtime: development; UTC; MySQL; sync queue; file sessions/cache. The live investigation previously reported Asia/Kolkata, so do not assume identical runtime settings.
- Local ShipMozo and Razorpay settings enabled. Present plugin folders do not imply activation.
- Root Composer merges plugin/theme composer.json files. Root frontend build uses vite-build.mjs and npm run prod.
- platform/core: shared CMS infrastructure; platform/packages: theme, plugin management, menus, pages, SEO, widgets, sitemap, etc.
- platform/plugins: business modules; platform/themes/farmart: vendor presentation; farmart-child: Gropart presentation overrides.
- app, bootstrap, routes, config: Laravel application setup. extra-data/Gropart-App-Build contains mobile-app artifacts and is a separate workstream.

## Main request path

Farmart child views → Ecommerce checkout controller → Marketplace store split and OrderHelper → shipping-rate filters → persisted order totals → Payment/Razorpay gateway → verified callback/webhook → payment processed action → OrderHelper finalization → order events, inventory and invoice/email listeners. ShipMozo booking listens to OrderConfirmedEvent, later than order placement.

## Local customization ownership

- shipmozo: rates, warehouses, booking, tracking, labels, NDR/returns.
- advanced-cod: eligibility, partial upfront payment and COD remainder; interacts with Razorpay.
- indian-gst: HSN/vendor GST, invoice variables and checkout integration; shipping filter currently returns rates unchanged.
- product-free-shipping: free-shipping product policy and adjusted rate/weight calculation.
- farmart-helper: phone fields, request sanitization, order-tracking query normalization. It is not currently a payment-protection plugin.
- enquiry-products: product enquiry flow; vendor-verified-badge: vendor presentation; sms: messaging/authentication integration.
- Metadata authorship is a useful ownership hint, not proof that vendor folders are pristine.

## Plugin inventory

| Folder | Active locally | Version | Author | Source files | Tests |
|---|---|---|---|---:|---:|
| ads | yes | 1.1.3 | Botble Technologies | 24 | 0 |
| advanced-cod | yes | 1.0.0 | Sparrowave Solutions | 7 | 0 |
| analytics | yes | 2.1.9 | Botble Technologies | 53 | 0 |
| audit-log | yes | 2.0.9 | Botble Technologies | 27 | 0 |
| backup | yes | 2.0.9 | Botble Technologies | 23 | 0 |
| block | yes | 2.0.0 | Botble Technologies | 14 | 0 |
| blog | yes | 2.0.9 | Botble Technologies | 70 | 3 |
| captcha | yes | 2.1.9 | Botble Technologies | 17 | 0 |
| contact | yes | 2.0.9 | Botble Technologies | 41 | 0 |
| contentinjector | yes | 1.0.0 | PHPMaster | 10 | 0 |
| cookie-consent | yes | 2.0.10 | Botble Technologies | 4 | 1 |
| custom-field | yes | 2.0.0 | Botble Technologies | 37 | 0 |
| ecommerce | yes | 3.11.10 | Botble Technologies | 892 | 70 |
| ecommerce-notification | yes | 1.0.4 | Archi Elite | 11 | 0 |
| ecommerce-wholesale | yes | 1.0.16 | Botble technologies | 65 | 28 |
| edit-lock | yes | 1.0.0 | Botble Technologies | 9 | 0 |
| enquiry-products | yes | 1.0.0 | SparroWave Technologies | 19 | 1 |
| faq | yes | 2.0.9 | Botble Technologies | 35 | 0 |
| farmart-helper | yes | 1.0.0 | SparroWave Technologies | 4 | 0 |
| fob-email-log | yes | 1.0.5 | Friends Of Botble | 9 | 0 |
| fob-facebook-catalog-feed | yes | 1.0.3 | FriendsOfBotble | 10 | 0 |
| fob-google-indexing | yes | 1.0.1 | Friends Of Botble | 15 | 0 |
| fob-honeypot | yes | 1.0.4 | Friends of Botble | 13 | 0 |
| fob-product-size-guide | yes | 1.0.2 | Friends Of Botble | 21 | 0 |
| fob-product-whatsapp-order | yes | 1.0.1 | Friends Of Botble | 6 | 0 |
| fob-request-quote | yes | 1.0.6 | Friends Of Botble | 16 | 0 |
| fob-seo-meta-keywords | yes | 1.0.1 | Friends of Botble | 2 | 0 |
| fob-ticksify | yes | 1.0.4 | Friends Of Botble | 33 | 0 |
| impersonate | yes | 1.2.1 | Sang Nguyen | 11 | 0 |
| indian-gst | yes | 1.0.0 | Sparrowave Solutions | 14 | 0 |
| language | yes | 2.2.9 | Botble Technologies | 53 | 13 |
| language-advanced | yes | 1.2.9 | Botble Technologies | 31 | 8 |
| llms-optimizer | yes | 1.1.1 | Shaqi (Ishtiaq Ahmed) | 13 | 0 |
| location | yes | 1.1.9 | Botble Technologies | 67 | 0 |
| log-viewer | yes | 1.3.0 | Botble Technologies | 33 | 0 |
| loyalty-points | yes | 1.0.18 | Botble Technologies | 63 | 22 |
| marketplace | yes | 2.3.0 | Botble technologies | 239 | 39 |
| mobile-command-bar | yes | 1.2.3 | Hamid Rezazadeh | 9 | 0 |
| mollie | no | 1.0.4 | Botble Technologies | 9 | 0 |
| newsletter | yes | 2.0.9 | Botble Technologies | 31 | 0 |
| notification-plus | yes | 2.0.0 | Archi Elite | 22 | 0 |
| payment | yes | 1.1.3 | Botble Technologies | 35 | 1 |
| paypal | no | 1.0.3 | Botble Technologies | 11 | 0 |
| paypal-payout | no | 1.0.3 | Botble Technologies | 20 | 0 |
| paystack | no | 1.0.3 | Botble Technologies | 13 | 1 |
| post-scheduler | yes | 1.3.4 | Botble Technologies | 6 | 1 |
| product-bundles | yes | 1.1.0 | Phung Huy | 21 | 0 |
| product-free-shipping | yes | 1.0.0 | SparroWave Technologies | 8 | 0 |
| razorpay | yes | 1.0.3 | Botble Technologies | 9 | 1 |
| shipmozo | yes | 1.2.0 | Sparrowave Solutions | 15 | 1 |
| shippo | no | 1.0.3 | Botble Technologies | 12 | 1 |
| simple-slider | yes | 2.0.10 | Botble Technologies | 28 | 0 |
| sms | yes | 1.0 | SparroWave Technologies | 30 | 0 |
| social-login | yes | 2.0.9 | Botble Technologies | 25 | 0 |
| sslcommerz | no | 1.0.3 | Botble Technologies | 14 | 0 |
| stripe | no | 1.0.3 | Botble Technologies | 11 | 0 |
| stripe-connect | no | 1.0.1 | Botble Technologies | 7 | 0 |
| testimonial | yes | 2.1.9 | Botble Technologies | 13 | 0 |
| translation | yes | 2.0.9 | Botble Technologies | 53 | 0 |
| vendor-verified-badge | yes | 1.0.0 | SparroWave Technologies | 10 | 0 |
| whatsapp-floating-button | yes | 1.0.6 | Ngo Quoc Dat | 4 | 0 |

Detailed providers, dependencies, source paths, hooks and migration counts: [plugin-inventory.json](plugin-inventory.json).

Payment/shipping findings and migration design: [payment-shipping-audit.md](payment-shipping-audit.md).

## Future work boundaries

Prefer Gropart-owned plugins for business logic and farmart-child for presentation. Do not use a child theme as the only home for payment/webhook logic. Before editing a vendor-owned file, inspect the existing hook, container binding and middleware alternatives. Keep verified payment completion, COD, bank transfer, partial COD and refunds separate. Do not infer that a payment row means paid.

Update compatibility still requires staging tests: custom files survive normal vendor-file replacement, but hook names, method signatures and behavior can change. Compare against a clean same-version author package before attributing a regression to upstream. Git commits titled “Farmart Update” are not a pristine vendor baseline.
