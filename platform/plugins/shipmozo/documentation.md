# ShipMozo Technical Documentation & Architecture Overview

**Author:** Sparrowave Solutions
**Version:** 1.0.3

This document explains the advanced architectural integrations bridging Botble's Core Ecommerce flow, the Farmart theme, and ShipMozo courier APIs.

---

## 1. Pincode Eligibility Hook (Product Page)
The ShipMozo architecture overrides Farmart theme product layouts and listens to pincode input boxes via an asynchronous API script.

**Workflow:**
Upon pressing "Check", a `fetch()` payload containing the product's defined dimensions (Length, Width, Height, Weight) and the active destination PIN is POSTed to the `\SparroWave\Shipmozo\Http\Controllers\ShipmozoController->getRate()`.
- If ShipMzo responds with a 404/Not Serviceable error, Botble natively blocks the `add_to_cart` button using frontend Javascript class appending (`.disabled-btn`).

## 2. Multi-Vendor Cart Array Calculation
Botble's native `Marketplace` plugin historically experienced bugged shipping session arrays because the native `ServiceProvider` overwrote `$sessionCheckoutData['marketplace']` inside of loops for each vendor ID.

**The Sparrowave Fix:**
Our package relies on patching Botble's `OrderSupportServiceProvider.php`:
```php
Arr::set($sessionCheckoutData, "marketplace.$storeId", $vendorSessionData);
```
This forces the session container to act additively. ShipMozo receives individual shipment requests containing dimensions *strictly* filtered by the products matching the specific vendor store ID. Instead of rendering one global list of couriers, ShipMozo pushes distinct multidimensional arrays (`shipping_option[STORE_1]... shipping_option[STORE_2]`) directly back into the cart checkout framework.

## 3. Checkout Button & "Select Options" Javascript Validation Lock
Botble's native JS does not enforce vendor-level selection requirements.

**Our Override:**
Our engine embeds a `DOMContentLoaded` script via `products.blade.php`.
1. The script observes `MutationObserver` AJAX reloads.
2. It queries Botble's `input[type="radio"]` counts inside the `list_payment_method` wrapper for each generated vendor.
3. If `OptionCountRequired !== CheckboxesSelected`, it forcefully adds `disabled` property arrays and CSS locks onto the checkout submit button.
4. *Visual Cue:* Our logic actively queries the string class `.vendor-shipping-price[data-store-id]`. Until the user actively engages the specific vendor's courier options, our JS injects a `— Select Option —` state rather than allowing Botble to assume a `Free Delivery/0.00` fallback parameter.

## 4. Final Aggregated `handleCheckoutOrderData` Processing
In Botble Core's native eCommerce service `\Botble\Ecommerce\Services\HandleCheckoutOrderData`, we explicitly patched the internal loop array.
Because our ShipMozo API returns isolated vendor subtotals, Botble's main `$shippingAmount` was reverting to zero at checkout.

We introduced an active summation parser:
```php
$shippingAmount = 0;
foreach (Arr::get($sessionCheckoutData, 'marketplace', []) as $storeData) {
   if (isset($storeData['shipping_amount'])) {
       $shippingAmount += $storeData['shipping_amount'];
   }
}
$orderAmount = max($rawTotal - $promotionDiscountAmount - $couponDiscountAmount, 0) + (float) $shippingAmount;
```
This guarantees the ShipMozo API output scales infinitely with however many Marketplace blocks natively generate inside the cart array.
