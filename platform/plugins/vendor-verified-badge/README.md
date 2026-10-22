# Vendor Verified & Type Badge for Botble Marketplace

[![Author](https://img.shields.io/badge/Author-SparroWave%20Technologies-blue.svg)](https://sparrowavetech.com)
[![Platform](https://img.shields.io/badge/Botble%20CMS-7.x%20%7C%208.x-success.svg)](https://marketplace.botble.com)
[![License](https://img.shields.io/badge/License-Commercial-orange.svg)](https://marketplace.botble.com)

A premium, production-grade extension for **Botble Multi-Vendor Marketplace**. This plugin equips your marketplace with an official **Verified Seller Badge (Green Tick)**, **Vendor Shop Categorization** (Manufacturer, Wholesaler, Retailer), a **Dynamic Profile Completion Score Tracker**, and an optional **Dashboard Feature Gating System** to incentivize vendors to complete their business and tax verification before selling.

---

## 🌟 Key Features

1. **Official Verified Seller Badge (Green Tick)**:
   - Displays an authentic, hoverable verification badge next to store names across product cards, single product pages, store listings, and the vendor dashboard.
   - Customizable badge icon (upload custom SVG/PNG or use the built-in high-resolution icon).
   - Configurable badge text label (e.g., *"Verified Seller"*, *"Verified Store"*) and hover tooltip text with full HTML/SVG support.

2. **Vendor Type Badges (Shop Category)**:
   - Categorize vendors into **Manufacturer**, **Wholesaler**, or **Retailer**.
   - Distinct, color-coded badges rendered next to store names on storefronts and admin datatables.
   - Seamlessly integrated into the *"Become a Vendor"* registration form, Vendor Store Settings, and Admin Customer/Store edit forms.
   - Optional toggle to show/hide shop type badges on the public storefront while retaining admin records.

3. **Smart Profile Completion Score Tracker**:
   - Real-time percentage score (10% to 100%) tracking store setup: logo, contact email, company info, city, state, zip code, and tax/business details.
   - Responsive progress bar displayed directly in the Vendor Dashboard.
   - Dynamic prompt encouraging vendors to apply for green tick verification via a custom admin link.

4. **Vendor Dashboard Menu Gating (Anti-Spam & Compliance)**:
   - Prevent unverified vendors with incomplete profiles (e.g. < 80% completion) from creating products, accessing orders, or requesting payouts.
   - Gating can be enabled or disabled with a single toggle in settings, and the required score threshold is fully configurable.

5. **One-Click Quick Verification & Revocation**:
   - Quick-action buttons in Admin DataTables (`/admin/marketplaces/stores` and `/admin/customers`) to verify or revoke a store with full audit tracking (`verified_at`, `verified_by`).

6. **Multi-Theme Ready (Farmart, Nest, Shofy, Martfury)**:
   - Built with theme-agnostic DOM auto-detection. Automatically enriches vendor links and greeting headers across popular Botble themes without requiring manual Blade edits.
   - Also provides clean Blade directives and Eloquent Model macros for custom theme development.

7. **100% Multi-Lingual & Localization Ready**:
   - Complete language files for **English** and **Hindi** with zero hardcoded strings.
   - Supports multi-language switching out of the box via Botble's Translation module.

---

## 📋 System Requirements

| Requirement | Minimum Supported |
|---|---|
| **PHP** | 8.2 / 8.3 / 8.4 |
| **Botble CMS** | 7.x or 8.x |
| **Required Core Plugins** | `ecommerce` (Botble Ecommerce), `marketplace` (Botble Marketplace) |
| **Supported Themes** | Farmart, Nest, Shofy, Martfury, and any custom Botble theme |

---

## 🚀 Installation

1. Upload the `vendor-verified-badge` folder into your project's `platform/plugins/` directory:
   ```text
   platform/plugins/vendor-verified-badge/
   ```
2. In your Botble Admin Panel, navigate to **Plugins** (`/admin/plugins`).
3. Locate **Vendor Verified & Type Badge** and click **Activate**.
4. Run asset publication (automatic upon activation or via CLI):
   ```bash
   php artisan vendor:publish --tag=cms-plugins --force
   ```

---

## ⚙️ Configuration

Navigate to **Admin Panel &rarr; Marketplace &rarr; Settings &rarr; Vendor Verified & Type Badge Settings** (`/admin/vendor-verified-badges/settings`):

| Setting Key | Type | Description |
|---|---|---|
| `vendor_verified_badge_icon` | Media Image | Custom verified tick image. Leave blank to use the built-in icon. |
| `vendor_shop_type_visible` | On/Off | Show or hide shop type badges (Manufacturer, Wholesaler, Retailer) on public storefront. |
| `vendor_verified_label` | Text | Text label displayed beside the icon (e.g. *"Verified Seller"*). Leave blank for icon only. |
| `vendor_verified_tooltip_text` | Text | Tooltip description shown when hovering over the badge. |
| `vendor_verified_application_url` | URL | External or internal application link where unverified vendors can apply for verification. |
| `vendor_verified_menu_gating_enabled` | On/Off | Enable/disable menu gating for incomplete vendor profiles. |
| `vendor_verified_completion_threshold` | Number (%) | Profile completion percentage required to unlock vendor menus (Default: 80%). |

---

## 💻 Developer & Theme Integration

### Blade Directives
You can use the following Blade directives anywhere in your theme views:

```blade
{{-- Render both Verified Badge and Shop Type Badge --}}
@vendorBadges($store)

{{-- Render only the Verified Icon --}}
@vendorVerified($store)

{{-- Render only the Shop Type Badge --}}
@vendorShopType($store)

{{-- Render Vendor Profile Completion Progress Bar in Vendor Dashboard --}}
@vendorProfileProgress
```

### Eloquent Model Macros
The plugin registers macros on `Botble\Marketplace\Models\Store`:

```php
// Returns combined HTML string
$store->badge_html;

// Returns verified tick HTML
$store->verified_badge_html;

// Returns shop type badge HTML
$store->shop_type_badge_html;
```

---

## 🔒 Permissions Tree

The plugin cleanly integrates with Botble's Role & Permission Management:

| Permission Flag | Description | Parent Flag |
|---|---|---|
| `vendor-verified-badge.settings` | Access and update badge settings | `marketplace.settings` |
| `vendor-verified-badge.verify` | One-click store verification & revocation | `marketplace.store.index` |

---

## 📁 File Structure

```text
platform/plugins/vendor-verified-badge/
├── config/
│   └── permissions.php
├── helpers/
│   └── constants.php
├── public/
│   ├── css/vendor-badge.css
│   ├── js/vendor-badge.js
│   └── images/verified.png
├── resources/
│   ├── lang/
│   │   ├── en/vendor-badge.php
│   │   └── hi/vendor-badge.php
│   └── views/
│       ├── badges.blade.php
│       ├── customers/vendor-meta-box.blade.php
│       └── dashboard/progress-bar.blade.php
├── routes/
│   └── web.php
├── src/
│   ├── Enums/ShopTypeEnum.php
│   ├── Forms/Settings/VendorBadgeSettingForm.php
│   ├── Http/
│   │   ├── Controllers/VendorBadgeSettingController.php
│   │   └── Requests/VendorBadgeSettingRequest.php
│   ├── Plugin.php
│   ├── Providers/
│   │   ├── HookServiceProvider.php
│   │   └── VendorVerifiedBadgeServiceProvider.php
│   └── Supports/VendorBadgeHelper.php
├── composer.json
├── plugin.json
└── README.md
```

---

## 🛡️ License & Commercial Support

Developed and maintained by **SparroWave Technologies**. For support, custom integrations, or licensing inquiries, please contact [support@sparrowavetech.com](mailto:support@sparrowavetech.com).
