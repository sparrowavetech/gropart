# ShipMozo Multi-Vendor Delivery Plugin

**Author:** Sparrowave Solutions
**Version:** 1.0.3
**Minimum Core Version:** 7.3.0
**Website:** [Sparrowave Solutions](https://www.sparrowave.com)

## Overview

Welcome to the **ShipMozo Multi-Vendor Delivery** plugin, exclusively developed by Sparrowave Solutions for the Botble & Farmart eCommerce ecosystems.

Unlike basic shipping modules, this complete enterprise-grade package natively supports **Multi-Vendor Carts**, **Dynamic Prepaid/COD Calculations**, and **Real-Time Pincode Serviceability** locks directly embedded into your product pages and checkout flow.

## 🚀 Key Features

*   **Multi-Vendor Architecture:** Accurately segments cart products by Vendor/Store IDs. Calculates separate ShipMozo courier limits and prices for *each* vendor simultaneously.
*   **Pincode Serviceability Validation:** Product pages dynamically query the ShipMozo API based on the user's PIN to actively permit or block the Add-to-Cart / Checkout actions.
*   **Split COD / Prepaid Returns:** Dynamically queries different courier methods arrays and displays eligible options natively based on whether the customer selects "Cash on Delivery" or standard prepayments.
*   **Bulletproof Checkout UI Locks:** Advanced DOM observers ensure a user physically cannot force a checkout submission unless they have explicitly selected an active carrier method for *every individual vendor's package* in their cart.
*   **Automated Grand Totals:** Injects additive logic into Botble's core checkout services so that Multi-Vendor cart shipping subtotals securely aggregate into a master 100% accurate grand total.

## 🛠️ Installation

1. Upload the `shipmozo` plugin directory to your Botble installation's `platform/plugins/` directory.
2. Go to your **Admin Dashboard > Plugins**.
3. Locate **ShipMozo Multi-Vendor Delivery** and click **Activate**.
4. Go to **Settings > Shipmozo API** in the admin sidebar.
5. Enter your **Shipmozo Username**, **Password**, and select your default Courier Service priorities.
6. Check your Farmart/Botble product layouts to confirm the "Check PIN" widget is active.

## Need Support or Customization?

Have a complex business scenario or need a new courier algorithm integrated securely? Our dedicated team at Sparrowave Solutions is available for advanced system integrations and long-term Botble architecture maintenance.

Contact us via [www.sparrowave.com](https://www.sparrowave.com).
