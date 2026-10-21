<?php

return [
    'name' => 'Advanced Cash on Delivery',
    'cod_available' => 'COD Available',
    'cod_eligible' => 'COD Eligible',
    'not_eligible_for_cod' => 'Not Eligible for COD',

    // Product Meta Box
    'product_cod_eligible_label' => 'Eligible for Cash on Delivery (COD)?',
    'product_cod_eligible_help' => 'If enabled, this product will be available for Cash on Delivery. If the cart contains any product where this is disabled, COD will be hidden at checkout.',
    'cod_enabled' => 'COD enabled',
    'cod_disabled' => 'COD disabled',

    // Breakdown & Checkout
    'smart_cod_available' => 'Smart COD Available (:percentage% Advance)',
    'pay_just_amount_now' => 'Pay just :amount now to confirm your order, and the remaining on delivery.',
    'pay_now_online' => 'Pay Now (Online):',
    'pay_on_delivery' => 'Pay on Delivery:',
    'calculated_on_total' => '* Calculated on final order total including shipping & taxes.',
    'advance_line_item_name' => 'COD Advance Prepayment (:percentage%) :order',

    // Conflicts
    'conflict_title' => 'COD Availability Conflict',
    'conflict_body' => 'Your cart contains items that are <strong>not eligible for Cash on Delivery</strong>. To enable COD, please remove the following items below, or proceed to pay online for the entire order.',
    'remove' => 'Remove',
    'pay_entire_online' => 'Pay Entire Order Online',
    'secure_online_payment' => 'Secure payment via Online Gateway',
    'conflict_minimized' => 'You included non-COD items in your cart.',
    'review_conflict' => 'Review Conflict',
    'no_online_method_found' => 'No online payment method found. Please contact support.',
    'no_gateway_available_for_advance' => 'No online payment gateway is currently available for the COD advance prepayment.',
    'choose_prepaid_for_all' => 'Or choose an online payment method to proceed with all items.',

    // Settings
    'prepayment_percentage' => 'Prepayment Percentage (%)',
    'prepayment_percentage_help' => 'Enter the percentage of the total amount that customers must pay in advance via online gateways when choosing COD.',
    'advance_gateway' => 'Advance Payment Online Gateway',
    'advance_gateway_help' => 'Select which active online gateway should collect the COD advance payment. Choose Auto to use the first available online gateway.',
    'advance_gateway_auto' => 'Auto-detect (First available active online gateway)',
    'admin_override' => 'Allow authorized admins to place full COD without advance',
    'admin_override_help' => 'Applies only to unpaid incomplete orders without a locked payment attempt. Customer checkout advance requirements stay unchanged.',
    'admin_override_details' => 'Separate override permission and reason required',

    // Settlements & Admin
    'settlement_title' => 'Advanced COD Settlement',
    'manual_confirmation_title' => 'Confirm & Place Order',
    'confirm_and_place' => 'Confirm & Place Order',
    'review_and_place_order' => 'Review Advanced COD & place order',
    'manual_receipt_channel' => 'Manual COD advance receipt',
    'balance_pending' => 'Balance pending',
    'fully_paid' => 'Fully paid',
];
