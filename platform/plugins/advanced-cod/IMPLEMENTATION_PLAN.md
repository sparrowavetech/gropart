# Advanced COD Payment Method Plan

This plan outlines the creation of a custom plugin to manage product-specific COD eligibility and require a dynamic partial prepayment for COD orders.

## [NEW] Renaming Requirements
- Rename plugin from "Partial COD Prepayment" to "Advanced COD Payment Method".
- Update folder name to `advanced-cod`.
- Update namespace to `Antigravity\AdvancedCod`.

## User Review Required

> [!IMPORTANT]
> **Constraint Check**: Does the user have a specific preference for how the "Cart Reset" should look? (e.g., a simple popup or a page redirect).
> **Payment Flow**: Handling two payment gateways simultaneously is technically complex. My proposed approach is to treat the 30% deposit as the primary transaction that triggers the creation of a "COD-Balanced" order.

## Proposed Changes

---

### 1. [Component] Database & Metadata
*   **[NEW] Migration**: Add a boolean column `is_cod_eligible` (default: false) to the `ec_products` table.
*   **[MODIFY] Product Repo**: Ensure the new field is fillable and handled during product saving.

---

### 2. [Component] Admin & Seller Interface
#### [MODIFY] Admin Product Form
*   **Hook**: `add_action(BASE_ACTION_META_BOXES, [PartialCodHookListener::class, 'addProductMetaBox'], 120, [BASE_FILTER_BEFORE_RENDER_FORM, $model])`
*   **Target**: `Botble\Ecommerce\Forms\ProductForm`
*   **Logic**: Inject a "COD Eligibility" checkbox in a new Meta Box. Visible only if the `cod` payment method is active in settings.

#### [MODIFY] Marketplace (Seller) Product Form
*   **Target**: `Botble\Marketplace\Forms\ProductForm`
*   **Method**: Since this form extends the base Ecommerce product form, the hook added above will automatically apply to the seller dashboard as well, ensuring consistency.

---

### 3. [Component] Checkout Gatekeeping
#### [FILTER] Payment Method Visibility
*   **Hook**: `add_filter(PAYMENT_FILTER_METHODS, [PartialCodCheckoutListener::class, 'filterPaymentMethods'], 20, $methods)`
*   **Logic**:
    *   Scan `Cart::instance('cart')->content()`.
    *   If any product's `is_cod_eligible` is false, `unset($methods['cod'])`.

#### [VIEW] Cart Conflict Notice & Resolution
*   **Awareness**: If the cart is mixed, display a "COD Conflict" alert at the top of the checkout payment section.
*   **Content**: 
    > "Some items in your cart are not eligible for Cash on Delivery. To use COD, you must either remove the non-eligible items or pay for the entire order in advance."
*   **Offending List**: List the specific products that are causing the conflict.
*   **Action Buttons**:
    1.  **"Keep only COD items"**: Automatically removes non-COD products and reloads the checkout.
    2.  **"Keep only Prepaid items"**: Automatically removes COD products and reloads the checkout.
    3.  **"Proceed with Full Prepayment"**: Hides the COD option entirely and encourages the use of Razorpay.

---

### 4. [Component] Dynamic Partial Prepayment Logic
*   **[NEW] Admin Setting**: Add a "Prepayment Percentage" field to the COD payment settings in the admin panel.
*   **Hook**: `add_filter(PAYMENT_FILTER_PAYMENT_DATA, [AdvancedCodCheckoutListener::class, 'adjustPaymentAmount'], 10, $data)`
*   **Workflow**:
    1.  If `payment_method == 'cod'` AND partial payment is enabled.
    2.  Get percentage from settings (default to 30%).
    3.  Calculate `amount = total * (percentage / 100)`.
    4.  Redirect to the secondary active gateway (Razorpay/Instamojo).
    5.  **On Successful Callback**:
        *   Create the Order with `is_confirmed = true`.
        *   Store the prepayment and remaining balance.
        *   Update Invoice to display the breakdown.

---

### 5. [Component] Frontend Labels
#### [VIEW] Product Cards & Detail Page
*   **Hook (Detail Page)**: `add_filter(ECOMMERCE_PRODUCT_DETAIL_RENDER_AFTER_PRICE, [PartialCodHookListener::class, 'renderCodLabel'], 20, $product)`
*   **Hook (Product Card)**: Since product cards in Farmart are often partials, I will check the theme's `product-item.blade.php` or use a view composer/hook if available to inject the "COD Available" badge.
*   **Style**: A small, clean badge (e.g., green text with a checkmark) that only appears if:
    1.  The product `is_cod_eligible` is true.
    2.  The COD Payment method is globally active.

---

## Verification Plan

### Automated Tests
*   **Unit Tests**: Verify the `PAYMENT_FILTER_METHODS` logic with various cart combinations (All COD, No COD, Mixed).
*   **Database Tests**: Ensure the `is_cod_eligible` flag persists correctly for both Admin and Sellers.

### Manual Verification
*   **Scenario A**: Add a non-COD product to the cart. Verify COD is hidden at checkout.
*   **Scenario B**: Add only COD products. Verify COD is visible.
*   **Scenario C**: Select COD. Proceed to payment. Verify the amount sent to Razorpay is exactly 30% of the total.
*   **Scenario D**: Verify the generated PDF invoice reflects the correct balance.
