<?php

return [
    'name' => 'Loyalty Points',
    'description' => 'Email notifications for loyalty points program',

    'customer_name' => 'Customer name',
    'customer_email' => 'Customer email',
    'points_earned' => 'Points earned',
    'points_redeemed' => 'Points redeemed',
    'order_code' => 'Order code',
    'order_url' => 'Order URL in admin panel',
    'current_balance' => 'Current points balance',
    'remaining_balance' => 'Remaining points balance',
    'discount_amount' => 'Discount amount',
    'level_name' => 'Membership level name',
    'new_level_name' => 'New membership level name',
    'old_level_name' => 'Previous membership level name',
    'level_benefits' => 'Level benefits',
    'earning_rate' => 'Points earning rate multiplier',
    'lifetime_points' => 'Lifetime points earned',
    'expiring_points' => 'Points about to expire',
    'expiry_date' => 'Expiry date',

    'points_earned_title' => 'Points Earned Notification',
    'points_earned_description' => 'Send email to customer when they earn loyalty points from an order',
    'points_earned_subject' => 'You\'ve earned loyalty points!',

    'points_redeemed_title' => 'Points Redeemed Notification',
    'points_redeemed_description' => 'Send email to customer when they redeem loyalty points for a discount',
    'points_redeemed_subject' => 'You\'ve redeemed your loyalty points!',

    'level_upgraded_title' => 'Level Upgrade Notification',
    'level_upgraded_description' => 'Send email to customer when they reach a new loyalty level',
    'level_upgraded_subject' => 'Congratulations! You\'ve reached a new loyalty level!',

    'points_expiring_reminder_title' => 'Points Expiring Reminder',
    'points_expiring_reminder_description' => 'Send reminder email to customer when their points are about to expire',
    'points_expiring_reminder_subject' => 'Your loyalty points are expiring soon!',

    'admin_points_earned_title' => 'Admin: Points Earned Notification',
    'admin_points_earned_description' => 'Send email to admin when a customer earns loyalty points from an order',
    'admin_points_earned_subject' => 'Customer earned loyalty points - Order #{{ order_code }}',

    'admin_points_redeemed_title' => 'Admin: Points Redeemed Notification',
    'admin_points_redeemed_description' => 'Send email to admin when a customer redeems loyalty points for a discount',
    'admin_points_redeemed_subject' => 'Customer redeemed loyalty points - Order #{{ order_code }}',
];
