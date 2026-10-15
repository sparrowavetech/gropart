# Enquiry Products

SparroWave's dedicated equipment enquiry plugin for Botble Ecommerce. No Farmart child-theme or FOB Request Quote dependency. All frontend templates, shortcodes, styles, controllers and schema ownership live in this directory. The surrounding layout is supplied by the active Botble theme.

## Customer entry point

`/equipments-enquiry-products` matches the existing Gropart menu link. It provides:

- Name search, category selection, combined filtering, clear filters and 12-item pagination.
- Plugin-owned product cards, product detail and enquiry submission form.
- Private attachment uploads (JPG/PNG/PDF, 5 MB), session-bound submission tokens and retry protection.
- A confirmation page that requires both the random token and the submitting browser session.

## Classification

The existing `ec_products.is_enquiry` and `ec_product_categories.is_enquiry` fields remain authoritative. No duplicate product catalog or competing metadata flags are introduced.

A product is enquiry-only if its own flag is enabled **or it is directly assigned to any flagged category**. A normal category assignment does not override an enquiry assignment. Variations inherit the original product's purchase restriction. Category ancestors are not implicitly inherited; enable the relevant category or assign products to it explicitly.

Admin and vendor product forms and the admin category form receive an **Enquiry only** switch through Form Builder hooks. Save actions preserve the fields even though upstream models do not include them in `$fillable`.

Normal storefront discovery, search, featured/collection queries using Product models, and category menus exclude enquiry items through request-scoped model filters. Filters also apply to lazy shortcode POST rendering and supported ecommerce catalog API controllers. Admin/vendor management, order history and background jobs remain unfiltered. Enquiry-owned queries explicitly opt out of only this plugin's visibility scope.

The category filter includes published flagged categories and published categories attached to enquiry products. The selection includes the selected category and its published descendants. Hierarchy labels distinguish categories with identical names. Search treats `%` and `_` literally. Draft products and variation records are excluded from the enquiry listing.

A category helper adapter gives public category widget queries a separate cache key. Normal product/category slug routes redirect enquiry items to the plugin catalog. Core cart-add hooks and the API cart middleware reject enquiry products; the checkout guard handles products converted after they were placed in a website cart.

## Shortcodes

```text
[enquiry-products title="Equipment enquiries" limit="12"][/enquiry-products]
[enquiry-products category_id="123" limit="8"][/enquiry-products]
[enquiry-categories][/enquiry-categories]
```

The products shortcode has an editor configuration for title, category and limit (1–48). Shortcode output links to the searchable catalog. Existing Farmart shortcode names are not replaced.

## Enquiry management

Admin: **Equipment Enquiries**, route `/admin/enquiry-products` (uses the configured admin prefix).

Permissions: `enquiry-products.index`, `enquiry-products.edit`.

Statuses: `pending`, `contacted`, `not_available`, `rejected`. Status transitions are recorded in `ep_enquiry_history`; internal notes stay in the admin screen.

When Marketplace is active, the vendor dashboard gains an Equipment Enquiries link. The vendor list, detail and attachment routes all enforce store ownership. Vendor access is read-only; status updates remain with the admin.

Email templates are registered through Botble EmailHandler. The received notification is sent separately to the customer, configured admin emails and the product's store email. Email failures are logged without deleting the saved enquiry. Tests replace the notification service and do not send messages.

Optional Location integration supplies country/state/city dropdowns. Without Location, the free-form address and PIN fields remain available. Legacy uploads are resolved on the configured media disk; new attachments use private local storage and permission-checked downloads.

## Migration ownership and legacy adoption

`2026_10_15_000001_adopt_enquiry_products_schema` is schema-aware and repeat-safe. It preserves the existing enquiry table, IDs, reference codes, statuses and store associations, adds missing fields, normalizes null enquiry flags to false, and creates status history storage. Rollback deliberately does not destroy adopted business data.

These old ecommerce migration files have been removed from the source tree after local backup and adoption verification:

- `2022_11_12_001725_add_is_enquiry_to_ec_products.php`
- `2022_11_18_182222_enquiry.php`
- `2022_11_20_140445_add_column_to_enquiry_table.php`
- `2022_12_03_215933_add_column_to_ec_product_enquiry_table.php`
- `2023_01_05_215855_add_column_to_ec_product_table.php`
- `2023_02_26_190105_add_column_to_ec_product_categories_is_enquiry.php`

Historical migration ledger entries remain intact. Two old store-column migrations used the same PHP class name; the replacement uses an anonymous migration. No other ecommerce source files need modification.

For another installation: back up data, install/activate this plugin, verify its adoption migration and only then remove the six legacy files if present. Do not run a legacy rollback or `migrate:fresh` on existing data. On a fresh installation, activate Ecommerce before this plugin.

## Installation and deployment

```sh
php artisan cms:plugin:activate enquiry-products
php artisan migrate --path=platform/plugins/enquiry-products/database/migrations --force
php artisan cms:publish:assets
```

Activation publishes `public/enquiry-products.css` under `public/vendor/core/plugins/enquiry-products/`. Clear the installation's storefront/page cache after deployment. A stale rendered page cache may continue showing old content until invalidated.

Deactivation is blocked while product/category enquiry flags remain enabled, to prevent unintended purchases. Convert the relevant entries first if intentionally retiring the feature. Uninstall retains enquiry records, flags, history and documents.

## Verification

Run without using the real database or sending mail:

```sh
php platform/plugins/enquiry-products/tests/run.php
```

The checks use an in-memory SQLite database and the real Botble models/provider/controller. They cover catalog separation, category-only classification, search + category combinations, literal wildcard searches, admin/vendor/order-history access, variation purchase protection, duplicate submissions, forged store/status inputs, vendor ownership and data-preserving migration reruns.

Custom future code that bypasses Eloquent with raw SQL must explicitly use the plugin classification rule. Upstream CMS changes to catalog controllers, category helper signatures or checkout flow require compatibility testing; no source-file patching is used as a fallback.

## Portability and translations

Ecommerce must be installed **and active** before activation. This is enforced by the manifest dependency and the activation guard. Marketplace is optional: its store routes, menus, seller notifications and store links are only enabled when active. The optional SparroWave vendor badge plugin enriches seller badges; it is not required. Without it, seller names and native verification flags still work.

The plugin owns its views, assets, migrations and `screenshot.png`; copy the complete plugin directory when installing elsewhere. No Gropart domain, database credentials or child-theme templates are required. The store catalogue button has a generic main/footer fallback for themes without Farmart's store-search selector.

Interface translations live in `resources/lang/en/messages.php`, including settings labels, status badges and autocomplete announcements. Add the same file under another locale folder (for example `hi`) to translate it. Botble's published translation overrides also apply. Email templates are editable using Botble's email settings. Catalogue title/subtitle, route slug and reference prefix/suffix remain configurable.

Verified on this project's Botble/Farmart installation and the isolated Ecommerce-only test configuration (Marketplace inactive). Nest and Shofy have not been installed or visually tested here. Test activation, storefront layout, checkout exclusion, enquiry submission, admin tables and optional vendor integration against the exact target CMS release before production use. Theme compatibility does not imply compatibility with every Botble core version.
