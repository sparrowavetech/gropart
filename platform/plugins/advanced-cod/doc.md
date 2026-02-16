# Advanced COD Payment Method - Documentation

Developed by **Sparrowave Solutions**, this plugin enhances the standard Botble Cash on Delivery (COD) method by adding product-level controls, partial prepayment requirements, and frontend transparency.

## 5 Key Features

### 1. Product-Specific COD Eligibility
Each product now has a dedicated "COD Eligibility" toggle in the Admin and Seller dashboards.
- **How it works**: Navigate to the product edit page. In the sidebar/advanced section, you can enable or disable COD for that specific product.
- **Impact**: If disabled, that product cannot be purchased using COD.

### 2. Dynamic Prepayment Percentage
Control the advance amount required from customers globally.
- **How it works**: Go to **Payments > Payment Methods > Cash on delivery (COD)**. A new field "Prepayment Percentage (%)" allows you to set the required advance (e.g., 30%).
- **Impact**: The plugin automatically calculates the advance amount based on this percentage during checkout.

### 3. Smart Cart Conflict Handling
The plugin ensures that COD is only available when it's safe to use for all items in the cart.
- **Logic**: If a customer adds both a COD-eligible and a non-COD-eligible product to their cart, the COD payment method is automatically hidden.
- **Notification**: A clear alert is displayed on the checkout page listing the specific items that are causing the conflict, helping the user either change products or choose a prepaid method (Razorpay/Instamojo).

### 4. Automated Partial Prepayment Flow
When COD is selected for eligible products, the plugin manages the redirection to online gateways for the advance payment.
- **Workflow**: Upon clicking "Checkout," the system redirects the customer to an active online gateway (like Razorpay) but only charges the configured percentage of the total.
- **Completion**: Once the online payment is successful, the order is confirmed, and the payment is marked as "Partial."

### 5. Theme Integration & Balance Tracking
Transparency for both customers and admins.
- **Visual Labels**: A "COD Available" badge is automatically displayed on product detail pages and catalog cards to increase customer trust.
- **Data Tracking**: The order record stores the exact `Prepayment Amount` and the `Remaining COD Balance`.
- **Order History**: Automatic logs are created in the order history section showing the partial payment breakdown.

---
© 2026 Sparrowave Solutions. All rights reserved.
