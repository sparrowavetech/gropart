<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReferralPointsTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyPointService $service;

    protected LoyaltyHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LoyaltyPointService::class);
        $this->helper = app(LoyaltyHelper::class);
        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_for_referral', 300)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        static $emailCounter = 0;
        $emailCounter++;

        return Customer::query()->create(array_merge([
            'name' => 'Test Customer',
            'email' => "customer{$emailCounter}@example.com",
            'password' => bcrypt('password'),
        ], $attributes));
    }

    public function test_referral_points_setting_configured(): void
    {
        $this->assertEquals(300, $this->helper->getPointsForReferral());
    }

    public function test_referral_points_can_be_customized(): void
    {
        setting()->forceSet('loyalty_points_points_for_referral', 500)->save();

        $helper = app(LoyaltyHelper::class);

        $this->assertEquals(500, $helper->getPointsForReferral());
    }

    public function test_referral_points_can_be_disabled(): void
    {
        setting()->forceSet('loyalty_points_points_for_referral', 0)->save();

        $helper = app(LoyaltyHelper::class);

        $this->assertEquals(0, $helper->getPointsForReferral());
    }

    public function test_can_award_referral_bonus_points(): void
    {
        $referrer = $this->createCustomer([
            'name' => 'John Referrer',
            'referral_code' => 'REF12345',
        ]);

        $referredCustomer = $this->createCustomer([
            'name' => 'Jane Referred',
        ]);

        $result = $this->service->awardBonusPoints(
            $referrer->id,
            300,
            trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_referral', [
                'name' => $referredCustomer->name,
            ])
        );

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $referrer->id)->first();

        $this->assertNotNull($balance);
        $this->assertEquals(300, $balance->total_points);
    }

    public function test_referral_bonus_creates_transaction(): void
    {
        $referrer = $this->createCustomer([
            'name' => 'John Referrer',
        ]);

        $referredCustomer = $this->createCustomer([
            'name' => 'Jane Referred',
        ]);

        $this->service->awardBonusPoints(
            $referrer->id,
            300,
            "Earned from referral: {$referredCustomer->name}"
        );

        $transaction = PointTransaction::query()
            ->where('customer_id', $referrer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(300, $transaction->points);
        $this->assertStringContainsString($referredCustomer->name, $transaction->note);
    }

    public function test_referral_points_have_expiry(): void
    {
        $referrer = $this->createCustomer();

        $this->service->awardBonusPoints(
            $referrer->id,
            300,
            'Referral bonus'
        );

        $transaction = PointTransaction::query()
            ->where('customer_id', $referrer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction->expires_at);
    }

    public function test_no_referral_points_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $service = app(LoyaltyPointService::class);
        $referrer = $this->createCustomer();

        $result = $service->awardBonusPoints(
            $referrer->id,
            300,
            'Referral bonus'
        );

        $this->assertFalse($result);
    }

    public function test_multiple_referrals_accumulate_points(): void
    {
        $referrer = $this->createCustomer(['name' => 'John Referrer']);

        $referredCustomer1 = $this->createCustomer(['name' => 'Jane Referred 1']);
        $referredCustomer2 = $this->createCustomer(['name' => 'Bob Referred 2']);

        $this->service->awardBonusPoints(
            $referrer->id,
            300,
            "Referral: {$referredCustomer1->name}"
        );

        $this->service->awardBonusPoints(
            $referrer->id,
            300,
            "Referral: {$referredCustomer2->name}"
        );

        $balance = CustomerPointBalance::query()->where('customer_id', $referrer->id)->first();

        $this->assertEquals(600, $balance->total_points);
    }

    public function test_referral_points_update_lifetime_points(): void
    {
        $referrer = $this->createCustomer();

        $this->service->awardBonusPoints($referrer->id, 300, 'Referral bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $referrer->id)->first();

        $this->assertEquals(300, $balance->lifetime_points);
    }

    public function test_custom_referral_points_amount_awarded(): void
    {
        setting()->forceSet('loyalty_points_points_for_referral', 500)->save();

        $referrer = $this->createCustomer();
        $referralPoints = app(LoyaltyHelper::class)->getPointsForReferral();

        $this->service->awardBonusPoints($referrer->id, $referralPoints, 'Referral bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $referrer->id)->first();

        $this->assertEquals(500, $balance->total_points);
    }

    public function test_referral_listener_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Botble\LoyaltyPoints\Listeners\AwardPointsForReferral::class)
        );
    }

    public function test_referral_points_accumulate_with_other_points(): void
    {
        $referrer = $this->createCustomer();

        $this->service->awardBonusPoints($referrer->id, 100, 'Registration bonus');

        $this->service->awardBonusPoints($referrer->id, 300, 'Referral bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $referrer->id)->first();

        $this->assertEquals(400, $balance->total_points);
    }

    public function test_referral_code_generation(): void
    {
        $customer = $this->createCustomer();

        $referralCode = strtoupper(substr(md5(uniqid()), 0, 8));
        $customer->forceFill(['referral_code' => $referralCode])->save();

        $customer = $customer->fresh();

        $this->assertNotNull($customer->referral_code);
        $this->assertEquals(8, strlen($customer->referral_code));
    }

    public function test_referred_by_relationship(): void
    {
        $referrer = $this->createCustomer(['name' => 'Referrer']);

        $referredCustomer = $this->createCustomer(['name' => 'Referred']);
        $referredCustomer->forceFill(['referred_by' => $referrer->id])->save();
        $referredCustomer = $referredCustomer->fresh();

        $this->assertEquals($referrer->id, $referredCustomer->referred_by);
    }

    public function test_referral_reward_given_flag(): void
    {
        $referrer = $this->createCustomer();

        $referredCustomer = $this->createCustomer();
        $referredCustomer->forceFill([
            'referred_by' => $referrer->id,
            'referral_reward_given' => false,
        ])->save();

        $referredCustomer = $referredCustomer->fresh();
        $this->assertFalse((bool) $referredCustomer->referral_reward_given);

        $referredCustomer->forceFill(['referral_reward_given' => true])->save();

        $referredCustomer = $referredCustomer->fresh();

        $this->assertTrue((bool) $referredCustomer->referral_reward_given);
    }
}
