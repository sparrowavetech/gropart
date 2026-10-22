# ShipMozo Multi-Vendor Delivery for Botble Ecommerce

ShipMozo real-time shipping rates, courier booking, AWB generation, shipping labels, live tracking, NDR management, pincode availability checks, and multi-vendor warehouse synchronization for Botble Ecommerce.

Designed and developed by **Sparrowave Solutions** for standalone Botble Ecommerce stores and Multi-Vendor Marketplaces. Fully compatible with official Botble themes (**Farmart, Nest, Shofy, Martfury, etc.**) and ready for marketplace.botble.com.

---

## Key Features

- **Automated & Manual Courier Assignment**: Automatically books consignments with top Indian courier partners (Delhivery, BlueDart, DTDC, Xpressbees, etc.) or allows admins to assign couriers on demand.
- **Theme-Agnostic Design**: Standard Bootstrap 5 UI components, accessible modals, and dynamic responsive timeline scripts compatible across all modern Botble themes.
- **Standalone & Multi-Vendor Modes**: Works seamlessly with single-store Botble Ecommerce setups (using store origin location) as well as Multi-Vendor Marketplaces (with per-store pickup warehouses).
- **Payment Gateway Agnostic**: Independent of payment providers. Works cleanly with Cash on Delivery (COD), partial advance COD, bank transfers, or any active online payment gateway (Razorpay, Stripe, PhonePe, Cashfree, PayPal, etc.).
- **Dynamic Weight Calculation**: Accurately respects the store's configured weight unit (`ecommerce_weight_unit()` in grams or kilograms), eliminating parcel weight discrepancies.
- **Granular Permissions Tree**: Built-in Botble permission flags for settings, shipment creation, rate updating, and NDR actions.
- **Full Multilingual (i18n) Support**: Complete English and Hindi language dictionaries out-of-the-box with 100% string coverage across admin views, storefront modals, and API controllers.
- **ShipMozo NDR Management**: Dedicated portal to monitor Non-Delivery Reports (NDR) with 1-click Re-attempt and Return to Origin (RTO) workflow actions.
- **Front-End Pincode Checker**: Responsive AJAX delivery checker for product detail and quick-view pages.
- **Live Tracking Timeline**: Injects clean, responsive live shipment status directly on customer order details and public tracking pages.

---

## Requirements

- **Botble CMS**: 7.6 or newer
- **PHP**: 8.2, 8.3, 8.4, or newer
- **Ecommerce Plugin**: Required
- **Marketplace Plugin**: Optional (only needed for multi-vendor warehouse origins)
- **Active ShipMozo Account**: With API Public Key and Private Key

---

## Installation & Setup

1. Copy `shipmozo` into your project at `platform/plugins/shipmozo`.
2. Go to **Admin > Plugins** and activate **ShipMozo Multi-Vendor Delivery**.
3. Navigate to **Admin > Ecommerce > Settings > Shipping Methods** and open the **ShipMozo** card.
4. Enter your ShipMozo **Public Key** and **Private Key**, configure optional rate inflation, and toggle **Activate**.
5. Save settings. ShipMozo rates will now calculate automatically during customer checkout!

CLI initialization alternative:

```bash
php artisan cms:shipmozo:init \
  --public-key=YOUR_PUBLIC_KEY \
  --private-key=YOUR_PRIVATE_KEY \
  --webhook-secret=YOUR_WEBHOOK_SECRET
```

---

## Permissions System

Configure role permissions under **Platform Administration > Roles & Permissions**:

| Permission Flag | Description | Parent Flag |
|---|---|---|
| `shipmozo.settings` | Configure API keys and shipping rules | `shipping_methods.index` |
| `shipmozo.shipments.create` | Assign courier and generate ShipMozo AWB | `ecommerce.shipments.edit` |
| `shipmozo.rates.update` | Recheck and update rates on saved shipments | `ecommerce.shipments.edit` |
| `shipmozo.ndr.index` | View Non-Delivery Reports (NDR) dashboard | `orders.index` |
| `shipmozo.ndr.action` | Trigger NDR Re-attempt or RTO on carrier | `shipmozo.ndr.index` |

---

## Webhook Integration

ShipMozo can push real-time delivery and tracking updates back to your store.

1. Enable webhooks in ShipMozo shipping settings and configure a secure **Webhook Secret** (minimum 16 characters).
2. Copy your Webhook URL: `https://yourdomain.com/shipmozo/webhooks`
3. Add this URL to your ShipMozo account webhook settings.
4. Authenticate using either header:
   - `Authorization: Bearer YOUR_WEBHOOK_SECRET`
   - `X-Shipmozo-Token: YOUR_WEBHOOK_SECRET`
   - `X-Webhook-Token: YOUR_WEBHOOK_SECRET`

---

## Asset Compilation

Compile storefront and admin assets using Vite:

```bash
# In your project root
npm run prod
# Or publish assets directly
php artisan vendor:publish --tag=cms-plugins-shipmozo
```

---

## Support & Ownership

- **Author**: Sparrowave Solutions
- **Website**: https://www.sparrowave.com
- **License**: Proprietary / Botble Marketplace Commercial License
