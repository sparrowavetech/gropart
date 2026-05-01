<?php

namespace Botble\LoyaltyPoints\Tests\Unit;

use Botble\Base\Supports\BaseTestCase;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyHelperTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = app(LoyaltyHelper::class);

        $this->setDefaultSettings();
    }

    protected function setDefaultSettings(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_earning_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_currency', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
        setting()->forceSet('loyalty_points_min_redeemable_points', 50)->save();
        setting()->forceSet('loyalty_points_max_redeemable_points', 1000)->save();
        setting()->forceSet('loyalty_points_max_redemption_percentage', 20)->save();
        setting()->forceSet('loyalty_points_points_for_registration', 100)->save();
        setting()->forceSet('loyalty_points_points_for_review', 50)->save();
        setting()->forceSet('loyalty_points_points_for_photo_review', 100)->save();
        setting()->forceSet('loyalty_points_points_for_referral', 300)->save();
        setting()->forceSet('loyalty_points_points_for_birthday', 200)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
        setting()->forceSet('loyalty_points_points_exchange_rate', 100)->save();
        setting()->forceSet('loyalty_points_eligible_order_statuses', json_encode(['completed']))->save();
    }

    public function test_is_enabled(): void
    {
        $this->assertTrue($this->helper->isEnabled());

        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $helper = app(LoyaltyHelper::class);
        $this->assertFalse($helper->isEnabled());
    }

    public function test_get_earning_rate(): void
    {
        $this->assertEquals(1, $this->helper->getEarningRate());
    }

    public function test_get_earning_currency(): void
    {
        $this->assertEquals(100, $this->helper->getEarningCurrency());
    }

    public function test_get_redemption_rate(): void
    {
        $this->assertEquals(100, $this->helper->getRedemptionRate());
    }

    public function test_get_redemption_currency(): void
    {
        $this->assertEquals(1, $this->helper->getRedemptionCurrency());
    }

    public function test_get_min_redeemable_points(): void
    {
        $this->assertEquals(50, $this->helper->getMinRedeemablePoints());
    }

    public function test_get_max_redeemable_points(): void
    {
        $this->assertEquals(1000, $this->helper->getMaxRedeemablePoints());
    }

    public function test_get_max_redemption_percentage(): void
    {
        $this->assertEquals(20, $this->helper->getMaxRedemptionPercentage());
    }

    public function test_get_points_for_registration(): void
    {
        $this->assertEquals(100, $this->helper->getPointsForRegistration());
    }

    public function test_get_points_for_review(): void
    {
        $this->assertEquals(50, $this->helper->getPointsForReview());
    }

    public function test_get_points_for_photo_review(): void
    {
        $this->assertEquals(100, $this->helper->getPointsForPhotoReview());
    }

    public function test_get_points_for_referral(): void
    {
        $this->assertEquals(300, $this->helper->getPointsForReferral());
    }

    public function test_get_points_for_birthday(): void
    {
        $this->assertEquals(200, $this->helper->getPointsForBirthday());
    }

    public function test_get_points_expiry_months(): void
    {
        $this->assertEquals(12, $this->helper->getPointsExpiryMonths());
    }

    public function test_get_points_exchange_rate(): void
    {
        $this->assertEquals(100, $this->helper->getPointsExchangeRate());
    }

    public function test_get_eligible_order_statuses(): void
    {
        $statuses = $this->helper->getEligibleOrderStatuses();

        $this->assertIsArray($statuses);
        $this->assertContains('completed', $statuses);
    }

    public function test_calculate_points_from_amount(): void
    {
        // With default settings: earning_currency=100, earning_rate=1
        // Formula: floor($amount / $currency) * $rate = floor(100 / 100) * 1 = 1
        $points = $this->helper->calculatePointsFromAmount(100);

        $this->assertEquals(1, $points);
    }

    public function test_calculate_points_from_amount_larger(): void
    {
        // With default settings: earning_currency=100, earning_rate=1
        // Formula: floor($amount / $currency) * $rate = floor(500 / 100) * 1 = 5
        $points = $this->helper->calculatePointsFromAmount(500);

        $this->assertEquals(5, $points);
    }

    public function test_calculate_points_from_amount_zero(): void
    {
        $points = $this->helper->calculatePointsFromAmount(0);

        $this->assertEquals(0, $points);
    }

    public function test_calculate_discount_from_points(): void
    {
        $discount = $this->helper->calculateDiscountFromPoints(100);

        $this->assertEquals(0.01, $discount);
    }

    public function test_calculate_discount_from_points_larger(): void
    {
        $discount = $this->helper->calculateDiscountFromPoints(500);

        $this->assertEquals(0.05, $discount);
    }

    public function test_calculate_max_points_from_amount(): void
    {
        $maxPoints = $this->helper->calculateMaxPointsFromAmount(100);

        $this->assertIsInt($maxPoints);
        $this->assertGreaterThanOrEqual(0, $maxPoints);
    }

    public function test_validate_redeemable_points_valid(): void
    {
        $errors = $this->helper->validateRedeemablePoints(100, 500, 100);

        $this->assertIsArray($errors);
    }

    public function test_validate_redeemable_points_below_minimum(): void
    {
        $errors = $this->helper->validateRedeemablePoints(10, 500, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_redeemable_points_above_maximum(): void
    {
        $errors = $this->helper->validateRedeemablePoints(2000, 5000, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_redeemable_points_insufficient_balance(): void
    {
        $errors = $this->helper->validateRedeemablePoints(500, 100, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_redeemable_points_discount_exceeds_total(): void
    {
        setting()->forceSet('loyalty_points_points_redemption_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();

        $helper = app(LoyaltyHelper::class);

        $errors = $helper->validateRedeemablePoints(500, 1000, 10);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_redeemable_points_negative(): void
    {
        $errors = $this->helper->validateRedeemablePoints(-100, 500, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_redeemable_points_zero(): void
    {
        $errors = $this->helper->validateRedeemablePoints(0, 500, 100);

        $this->assertNotEmpty($errors);
    }
}
