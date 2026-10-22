# Product Free Shipping & Weight Deduction for Botble Ecommerce

[![Author](https://img.shields.io/badge/Author-SparroWave%20Technologies-blue.svg)](https://sparrowavetech.com)
[![Platform](https://img.shields.io/badge/Botble%20CMS-7.x%20%7C%208.x-success.svg)](https://marketplace.botble.com)
[![License](https://img.shields.io/badge/License-Commercial-orange.svg)](https://marketplace.botble.com)

A high-performance, production-ready extension for **Botble Ecommerce** (and optional **Botble Multi-Vendor Marketplace**). This plugin allows store owners and vendors to mark specific individual products with a **Free Shipping** policy, deduct free product weights from courier quotes on mixed orders, offer zero-cost delivery when all cart items qualify, and highlight promotional badges across product catalog cards, detail pages, shopping carts, and admin orders.

---

## 🌟 Key Features

1. **Product-Level Free Shipping Toggle**:
   - Easily enable or disable free shipping on any product via an On/Off toggle directly in the product creation and editing forms.
   - Fully supports both **Admin Products** and **Marketplace Vendor Products** (`VendorProductForm`).
   - Clean, lightweight database column (`ec_products.product_free_shipping`) indexed for high throughput.

2. **Automatic Variation Inheritance**:
   - Product variations automatically inherit the free shipping status of their configurable parent product.
   - Backward-compatible fallback: variations created prior to activation safely inherit their parent's setting at runtime.

3. **Smart Mixed-Cart Billable Weight Deduction**:
   - In mixed carts containing both regular and free shipping products, the plugin automatically subtracts the weight of the free shipping items from the total billable weight.
   - Courier integrations (such as ShipMozo or custom API carriers) quote rates based only on the non-free items, preventing customers from being unfairly charged shipping for free delivery items.

4. **100% Free Shipping Order Handling**:
   - When all products in the cart qualify for free delivery, an automatic zero-price shipping method is added.
   - Optional setting to hide all paid courier options when an order qualifies for 100% free delivery.

5. **Universal Theme Badges (Multi-Theme Compatible)**:
   - **Catalog / Product Cards**: Displays a compact, eye-catching badge with a delivery truck icon on grid and list cards.
   - **Product Detail Pages**: Renders a dedicated promotional badge with flexible placement that integrates seamlessly with COD badges and buy boxes.
   - **Shopping Cart & Checkout**: Adds an aesthetic shimmer badge under qualified cart items to remind customers of their savings.
   - **Admin & Vendor Order Views**: Clear badge displayed under order items in the order details view for warehouse packing teams.
   - **Admin Product DataTable**: Quick visual indicator column showing which products have free delivery enabled.

6. **Fully Theme Agnostic**:
   - Zero hardcoded theme styles. Works out of the box on **Farmart**, **Nest**, **Shofy**, **Martfury**, or any custom Botble theme.
   - Built-in duplicate injection guard prevents multiple badges from rendering even if themes invoke multiple badge hooks.

7. **100% Decoupled & Multi-Lingual (i18n)**:
   - Zero hardcoded vendor brands, domains, or payment gateway requirements.
   - Complete translations provided for **English** and **Hindi** out of the box with 100% key parity.
   - Canonical Botble ACL permission tree (`ecommerce.settings.product-free-shipping`).

---

## 📋 System Requirements

| Requirement | Minimum Supported |
|---|---|
| **PHP** | 8.2 / 8.3 / 8.4 |
| **Botble CMS** | 7.x or 8.x |
| **Required Core Plugin** | `ecommerce` (Botble Ecommerce) |
| **Optional Core Plugin** | `marketplace` (Botble Multi-Vendor Marketplace) |
| **Supported Themes** | Farmart, Nest, Shofy, Martfury, and all standard Botble themes |

---

## 🚀 Installation

1. Upload the `product-free-shipping` folder into your project's `platform/plugins/` directory:
   ```text
   platform/plugins/product-free-shipping/
   ```
2. Navigate to your Botble Admin Panel: **Admin > Plugins** (`/admin/plugins`).
3. Locate **Product Free Shipping** and click **Activate**.
4. Publish assets (automatic upon activation or via artisan):
   ```bash
   php artisan vendor:publish --tag=cms-plugins --force
   ```

---

## ⚙️ Configuration & Settings

Navigate to **Admin > Ecommerce > Settings > Product Free Shipping** (`/admin/ecommerce/settings/product-free-shipping`):

| Setting | Type | Description | Default |
|---|---|---|---|
| **Enable Product Free Shipping** | Toggle | Globally enable or disable product-level free delivery calculation. | `Enabled` |
| **Product Page Badge Text** | Text | Label shown inside badges across catalog and single product pages. | `Free Delivery` |
| **Checkout Shipping Method Title** | Text | The name of the free delivery shipping option presented at checkout. | `Free Delivery` |
| **Hide Paid Methods on 100% Free** | Toggle | When all cart items are free, hide paid courier rates and offer only free delivery. | `Disabled` |

---

## 🧑‍💻 Developer API & Extension Hooks

### Eloquent Model Macros
The plugin adds convenient Eloquent macro accessors to the `Botble\Ecommerce\Models\Product` model:

```php
// Check if a product qualifies for free shipping (handles variations & parents)
$isFree = $product->isFreeShipping();

// Or access via magic attribute
$isFree = $product->is_free_shipping;
```

### Free Shipping Policy Filter
Other plugins (such as courier quotation engines or checkout discount checkers) can check if any product has free shipping via the standard Botble filter:

```php
$isFree = apply_filters('ecommerce_product_has_free_shipping', false, $product);
```

### Helper Utility Methods
Direct helper calls available via `SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper`:

```php
use SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper;

// 1. Check free shipping on ID, Product model, or array
$isFree = ProductFreeShippingHelper::isProductFreeShipping($product);

// 2. Calculate remaining billable weight for courier quotes (deducts free items)
$billableWeight = ProductFreeShippingHelper::calculateBillableWeight($cartItems);

// 3. Check if an entire cart qualifies for 100% free delivery
$isAllFree = ProductFreeShippingHelper::isAllItemsFreeShipping($cartItems);

// 4. Retrieve configured badge text with locale fallback
$badgeText = ProductFreeShippingHelper::getBadgeText();
```

---

## 📂 File Structure

```text
platform/plugins/product-free-shipping/
├── config/
│   └── permissions.php           # Canonical ACL permission definitions
├── database/
│   └── migrations/               # ec_products table schema extension
├── public/
│   └── css/                      # Shimmer and theme-agnostic badge styles
├── resources/
│   ├── lang/
│   │   ├── en/                   # English translations (100% symmetrical)
│   │   └── hi/                   # Hindi translations (100% symmetrical)
│   └── views/                    # Blade badge partial templates
├── routes/
│   └── web.php                   # Guarded admin settings routes
├── src/
│   ├── Forms/Settings/           # Admin settings form definition
│   ├── Http/
│   │   ├── Controllers/Settings/ # Settings controller
│   │   └── Requests/             # Request validation rules
│   ├── Providers/
│   │   ├── HookServiceProvider.php # Core hooks & filters
│   │   └── ProductFreeShippingServiceProvider.php # Package registration & macros
│   └── Supports/
│       └── ProductFreeShippingHelper.php # Weight calculation & policy engine
├── plugin.json                   # Botble plugin manifest
└── README.md                     # Technical documentation & guide
```

---

## 🔒 Security & Data Integrity

- **Idempotent Migrations**: Uses `Schema::hasColumn` guards so running migrations multiple times is completely safe.
- **Granular Permissions**: Settings routes are strictly protected by `ecommerce.settings.product-free-shipping` permissions.
- **Strict Data Extraction**: Uses `data_get()` for safe traversal across arrays, collections, and Eloquent models.

---

## 📄 License & Commercial Support

- **Author**: SparroWave Technologies
- **Website**: [https://sparrowavetech.com](https://sparrowavetech.com)
- **Marketplace**: [https://marketplace.botble.com](https://marketplace.botble.com)
- **License**: Commercial — All rights reserved.
