<?php

namespace Botble\LoyaltyPoints\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Illuminate\Support\Arr;
use Throwable;

class LoyaltyEmailService
{
    public function isEmailNotificationEnabled(): bool
    {
        return (bool) get_loyalty_setting('enable_email_notification', true);
    }

    public function getAdminNotificationEmails(): string|array|null
    {
        $receiverEmails = get_loyalty_setting('notification_emails', '');

        if (empty($receiverEmails)) {
            return get_admin_email()->first() ?: setting('admin_email');
        }

        $receiverEmails = trim($receiverEmails);

        if (str_contains($receiverEmails, '[')) {
            $receiverEmails = collect(json_decode($receiverEmails, true))
                ->pluck('value')
                ->all();
        } else {
            $receiverEmails = array_map('trim', explode(',', $receiverEmails));
        }

        $receiverEmails = array_filter($receiverEmails);

        if (count($receiverEmails) === 1) {
            return Arr::first($receiverEmails);
        }

        return $receiverEmails ?: null;
    }

    public function sendPointsEarnedEmail(
        Customer $customer,
        int $pointsEarned,
        Order $order,
        CustomerPointBalance $balance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        try {
            $levelName = '';
            if ($balance->level_id) {
                $level = LoyaltyLevel::find($balance->level_id);
                $levelName = $level?->name ?? '';
            }

            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'points_earned' => $pointsEarned,
                    'order_code' => $order->code,
                    'current_balance' => $balance->total_points,
                    'level_name' => $levelName,
                ])
                ->sendUsingTemplate('points_earned', $customer->email);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendPointsRedeemedEmail(
        Customer $customer,
        int $pointsRedeemed,
        string $discountAmount,
        Order $order,
        int $remainingBalance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        try {
            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'points_redeemed' => $pointsRedeemed,
                    'discount_amount' => $discountAmount,
                    'order_code' => $order->code,
                    'remaining_balance' => $remainingBalance,
                ])
                ->sendUsingTemplate('points_redeemed', $customer->email);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendLevelUpgradedEmail(
        Customer $customer,
        LoyaltyLevel $newLevel,
        ?LoyaltyLevel $oldLevel,
        CustomerPointBalance $balance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        try {
            $benefits = $this->formatBenefits($newLevel->benefits);

            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'new_level_name' => $newLevel->name,
                    'old_level_name' => $oldLevel?->name ?? '',
                    'level_benefits' => $benefits,
                    'earning_rate' => $newLevel->earning_rate,
                    'current_balance' => $balance->total_points,
                    'lifetime_points' => $balance->lifetime_points,
                ])
                ->sendUsingTemplate('level_upgraded', $customer->email);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendPointsExpiringReminderEmail(
        Customer $customer,
        int $expiringPoints,
        string $expiryDate,
        int $currentBalance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        try {
            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'expiring_points' => $expiringPoints,
                    'expiry_date' => $expiryDate,
                    'current_balance' => $currentBalance,
                ])
                ->sendUsingTemplate('points_expiring_reminder', $customer->email);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendAdminPointsEarnedEmail(
        Customer $customer,
        int $pointsEarned,
        Order $order,
        CustomerPointBalance $balance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        $adminEmails = $this->getAdminNotificationEmails();

        if (empty($adminEmails)) {
            return false;
        }

        try {
            $levelName = '';
            if ($balance->level_id) {
                $level = LoyaltyLevel::find($balance->level_id);
                $levelName = $level?->name ?? '';
            }

            $orderUrl = route('orders.edit', $order->id);

            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'points_earned' => $pointsEarned,
                    'order_code' => $order->code,
                    'current_balance' => $balance->total_points,
                    'level_name' => $levelName,
                    'order_url' => $orderUrl,
                ])
                ->sendUsingTemplate('admin_points_earned', $adminEmails);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendAdminPointsRedeemedEmail(
        Customer $customer,
        int $pointsRedeemed,
        string $discountAmount,
        Order $order,
        int $remainingBalance
    ): bool {
        if (! $this->isEmailNotificationEnabled()) {
            return false;
        }

        $adminEmails = $this->getAdminNotificationEmails();

        if (empty($adminEmails)) {
            return false;
        }

        try {
            $orderUrl = route('orders.edit', $order->id);

            EmailHandler::setModule(LOYALTY_POINTS_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'points_redeemed' => $pointsRedeemed,
                    'discount_amount' => $discountAmount,
                    'order_code' => $order->code,
                    'remaining_balance' => $remainingBalance,
                    'order_url' => $orderUrl,
                ])
                ->sendUsingTemplate('admin_points_redeemed', $adminEmails);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    protected function formatBenefits(?string $benefits): string
    {
        if (empty($benefits)) {
            return '';
        }

        $lines = array_filter(array_map('trim', explode("\n", $benefits)));

        if (empty($lines)) {
            return '';
        }

        $html = '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($lines as $line) {
            $html .= '<li style="margin-bottom: 5px;">' . e($line) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}
