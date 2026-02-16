# Advanced COD Payment Method Plugin

**Developer:** Sparrowave Solutions  
**Version:** 1.0.0  
**Namespace:** `SparroWave\AdvancedCod`

## 📋 Overview

This plugin provides an advanced COD (Cash on Delivery) payment system for Botble CMS that:
- Offers product-specific COD eligibility control.
- Handles mixed cart scenarios intelligently.
- Supports partial prepayment logic for COD orders.
- Enhances the checkout UI with professional conflict resolution.

## 🚀 Features

1.  **Product-Level COD Control** - Enable/Disable COD for each product individually via the Admin panel or Vendor dashboard.
2.  **Dynamic Prepayment** - Configurable advance payment percentage (default 30%) for COD orders.
3.  **Smart Conflict Resolution** - Automatically detects mixed carts (eligible + ineligible items) and prompts the user to resolve the conflict or pay online.
4.  **"Undo" Functionality** - Allows users to revert their decision to "Pay Online" and return to the conflict resolution screen to remove items.
5.  **Premium UI/UX** - 
    - Professional badges ("COD Eligible" / "Not Eligible") on checkout and Admin "Incomplete Orders" pages.
    - Styled conflict alerts and a premium "Checkout" button with a shopping cart icon.
6.  **Admin & Marketplace Support** - Full control for Admins and Marketplace Vendors.

## 📦 Installation

1.  **Download/Copy:** Copy the `advanced-cod` folder to your project's `platform/plugins/` directory.
2.  **Admin Activation:**
    - Log in to the Admin Panel.
    - Go to **Plugins**.
    - Find **"Advanced COD Payment Method"** and click **Activate**.
3.  **Database Migration:** The plugin automatically updates the database tables upon activation.

## ✅ Requirements

- **Botble CMS Core**: Latest version recommended (Minimum 7.0.0)
- **Plugins Required**:
  - Ecommerce
  - Payment
- **Optional for Prepayment**:
  - Razorpay OR Instamojo (for handling advance payments)

## 📁 Documentation Files

The following documentation files are included with this plugin:

- **[ACTIVATION_GUIDE.md](./ACTIVATION_GUIDE.md)** - Complete guide on activation and debugging.
- **[DEVELOPMENT_TASKS.md](./DEVELOPMENT_TASKS.md)** - Checklist of development tasks and completion status.
- **[IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md)** - Original implementation plan and architecture.
- **[doc.md](./doc.md)** - Feature details and technical summary.

## ⚙️ Configuration

### Set Prepayment Percentage:

1.  Go to **Admin Panel → Payments → COD Settings**.
2.  Enter a value in the **"Prepayment Percentage (%)"** field (Default: 30).
3.  Click **Save**.

### Enable/Disable COD for Products:

**For Admins:**
1.  Go to **Products → Edit Product**.
2.  Look for the **"COD Eligibility"** meta box.
3.  Check/Uncheck the box and **Save**.

**For Marketplace Vendors:**
1.  Go to **Vendor Dashboard → Products → Edit**.
2.  The same "COD Eligibility" checkbox is available.

## 📝 Usage Scenarios

### Scenario 1: Fully Eligible Cart
- **User Action:** Customer adds only "COD Eligible" products to the cart.
- **Result:** The "Cash on Delivery" option is available at checkout. If prepayment is configured, the customer is notified to pay X% in advance.

### Scenario 2: Mixed or Ineligible Cart
- **User Action:** Customer adds at least one product that is NOT eligible for COD.
- **Result:** 
    - The COD payment method is automatically hidden.
    - A **Conflict Alert** appears, listing the ineligible items.
    - The customer can either **Remove** the items to enable COD or choose **"Pay Entire Order Online"**.
    - If they choose to pay online, an **"Undo" (Review Conflict)** option appears to revert the decision.

## 🗄️ Database Schema

The plugin adds the following columns:

**`ec_products` table:**
- `is_cod_eligible` (boolean) - Determines if a product is eligible for COD.

**`ec_orders` table:**
- `cod_prepayment_amount` (decimal) - The advance payment amount.
- `cod_remaining_amount` (decimal) - The amount to be paid on delivery.

## 🔧 Technical Details

### Main Files:
- `src/Providers/AdvancedCodServiceProvider.php` - Service provider and hook registration.
- `src/Hooks/AdvancedCodHookListener.php` - Product meta box and settings logic.
- `src/Hooks/AdvancedCodCheckoutListener.php` - Checkout logic, conflict resolution UI, and payment processing.
- `database/migrations/` - Database schema changes.

### Hooks Used:
- `BASE_ACTION_META_BOXES` - Adds meta box to product edit page.
- `ecommerce_checkout_body` - Injects conflict notices and scripts.
- `payment_methods_excluded` - Controls visibility of the COD payment method.
- `FILTER_ECOMMERCE_PROCESS_PAYMENT` - Intercepts payment processing for logic validation.
- `PAYMENT_ACTION_PAYMENT_PROCESSED` - Handles post-payment order updates.

## 🐛 Troubleshooting

If the plugin fails to activate, please refer to `ACTIVATION_GUIDE.md` for common issues and solutions.

**Common Checks:**
- Ensure the folder name is exactly `advanced-cod`.
- Check folder permissions.

## 📅 Changelog

### Version 1.0.0 (2026-02-14)
- Initial Release.
- Product-level COD eligibility.
- Advanced Prepayment Handling logic.
- Admin & Vendor Dashboard integration.
- UI Enhancements: Conflict Undo, Premium Checkout Button, and Admin Badges.

## 📞 Support

**Developer:** Sparrowave Solutions  
**Website:** https://www.sparrowave.com

---
**Note:** These documentation files are saved in the plugin folder for future reference. They remain accessible even if you switch your Antigravity account.
