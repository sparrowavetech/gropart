<?php

namespace Botble\LoyaltyPoints\Providers;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Events\LevelUpgraded;
use Botble\LoyaltyPoints\Events\PointsEarned;
use Botble\LoyaltyPoints\Events\PointsRedeemed;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Listeners\AwardPointsForCompletedOrder;
use Botble\LoyaltyPoints\Listeners\AwardPointsForReferral;
use Botble\LoyaltyPoints\Listeners\AwardPointsForReview;
use Botble\LoyaltyPoints\Listeners\ProcessAdminOrderLoyaltyPoints;
use Botble\LoyaltyPoints\Listeners\ReversePointsForCancelledOrder;
use Botble\LoyaltyPoints\Listeners\ReversePointsForDeletedReview;
use Botble\LoyaltyPoints\Listeners\SaveOrderLoyaltyPoints;
use Botble\LoyaltyPoints\Listeners\SendLevelUpgradedEmail;
use Botble\LoyaltyPoints\Listeners\SendPointsEarnedEmail;
use Botble\LoyaltyPoints\Listeners\SendPointsRedeemedEmail;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPlacedEvent::class => [
            SaveOrderLoyaltyPoints::class,
        ],
        OrderCreated::class => [
            ProcessAdminOrderLoyaltyPoints::class,
        ],
        OrderCompletedEvent::class => [
            AwardPointsForCompletedOrder::class,
            AwardPointsForReferral::class,
        ],
        OrderCancelledEvent::class => [
            ReversePointsForCancelledOrder::class,
        ],
        CreatedContentEvent::class => [
            AwardPointsForReview::class,
        ],
        UpdatedContentEvent::class => [
            AwardPointsForReview::class,
        ],
        DeletedContentEvent::class => [
            ReversePointsForDeletedReview::class,
        ],
        PointsEarned::class => [
            SendPointsEarnedEmail::class,
        ],
        PointsRedeemed::class => [
            SendPointsRedeemedEmail::class,
        ],
        LevelUpgraded::class => [
            SendLevelUpgradedEmail::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        Event::listen(Registered::class, function (Registered $event): void {
            if (! $event->user instanceof Customer) {
                return;
            }

            $customer = $event->user;

            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return;
            }

            $existingTransaction = PointTransaction::query()
                ->where('customer_id', $customer->id)
                ->where('type', PointTransaction::TYPE_EARN)
                ->where('note', 'LIKE', '%registration%')
                ->exists();

            if ($existingTransaction) {
                return;
            }

            $points = $loyaltyHelper->getPointsForRegistration();

            if ($points <= 0) {
                return;
            }

            $referralCode = strtoupper(Str::random(8));

            while (Customer::query()->where('referral_code', $referralCode)->exists()) {
                $referralCode = strtoupper(Str::random(8));
            }

            $customerData = ['referral_code' => $referralCode];

            // Integration with Affiliate Pro: Link referred_by from affiliate cookie
            if (is_plugin_active('affiliate-pro')) {
                $affiliateCode = Cookie::get('affiliate_code') ?? request()->cookie('affiliate_code');

                if ($affiliateCode) {
                    $affiliateCustomerId = $this->getCustomerIdFromAffiliateCode($affiliateCode);

                    if ($affiliateCustomerId && $affiliateCustomerId !== $customer->id) {
                        $customerData['referred_by'] = $affiliateCustomerId;
                    }
                }
            }

            $customer->forceFill($customerData)->save();

            app(LoyaltyPointService::class)->awardBonusPoints(
                $customer->id,
                $points,
                trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_registration')
            );
        });

        Event::listen(UpdatedContentEvent::class, function (UpdatedContentEvent $event): void {
            if (! $event->data instanceof Order) {
                return;
            }

            $order = $event->data;

            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return;
            }

            $loyaltyPointService = app(LoyaltyPointService::class);

            $customerId = $order->user_id;

            // For guest orders, check if there's a customer_id from loyalty member ID
            if (! $customerId) {
                $orderLoyaltyPoints = OrderLoyaltyPoints::query()
                    ->where('order_id', $order->id)
                    ->first();

                if ($orderLoyaltyPoints && $orderLoyaltyPoints->customer_id) {
                    $customerId = $orderLoyaltyPoints->customer_id;
                }
            }

            if (! $customerId) {
                return;
            }

            if (in_array($order->status->getValue(), $loyaltyHelper->getEligibleOrderStatuses())) {
                $loyaltyPointService->awardPointsForOrder($order, $customerId);
            }

            if (in_array($order->status->getValue(), ['canceled', 'refunded', 'failed'])) {
                $loyaltyPointService->reversePointsForOrder($order);
            }
        });

        Customer::deleted(function ($customer): void {
            $customer->pointBalance()->delete();
            $customer->pointTransactions()->delete();
        });

        Order::deleted(function ($order): void {
            $order->loyaltyPoints()->delete();
            PointTransaction::where('order_id', $order->id)->delete();
        });
    }

    protected function getCustomerIdFromAffiliateCode(string $affiliateCode): int|string|null
    {
        if (! class_exists(Affiliate::class)) {
            return null;
        }

        $affiliate = Affiliate::query()
            ->where('affiliate_code', $affiliateCode)
            ->where('status', 'published')
            ->first();

        return $affiliate?->customer_id;
    }
}
