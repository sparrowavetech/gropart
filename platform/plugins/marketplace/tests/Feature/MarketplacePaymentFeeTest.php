<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Marketplace\Providers\OrderSupportServiceProvider;
use Botble\Payment\Enums\PaymentFeeTypeEnum;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

/**
 * Drives the real OrderSupportServiceProvider::processPaymentMethodPostCheckout() so the amount
 * handed to the payment gateway is verified against the production code path.
 *
 * Regression cover for the double-charge: the vendor sub-orders already carry their payment fee
 * in `amount`, so recomputing the fee on their sum billed the customer more than the orders
 * recorded. This is the seam where that happened, and it previously had no tests at all.
 */
class MarketplacePaymentFeeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function seedFee(string $method, float $fee, string $type, float $feeFixed = 0): void
    {
        Setting::forceSet("payment_{$method}_fee", (string) $fee)
            ->forceSet("payment_{$method}_fee_type", $type)
            ->forceSet("payment_{$method}_fee_fixed", (string) $feeFixed)
            ->save();
    }

    protected function gatewayAmountFor(string $method, float $ordersTotal): float
    {
        $provider = app(OrderSupportServiceProvider::class, ['app' => app()]);

        $request = Request::create('/checkout', 'POST', ['payment_method' => $method]);

        $paymentData = $provider->processPaymentMethodPostCheckout($request, $ordersTotal);

        return (float) $paymentData['amount'];
    }

    public function test_gateway_amount_equals_orders_total_and_is_not_fee_inflated(): void
    {
        // 3.5% already applied per vendor: a $100 order was stored as $103.50 across sub-orders.
        $this->seedFee('stripe', 3.5, PaymentFeeTypeEnum::PERCENTAGE);

        $ordersTotal = 103.50;

        // Must hand the gateway exactly what the orders recorded. The double-charge produced
        // 103.50 + 3.62 = 107.12 here.
        $this->assertSame($ordersTotal, $this->gatewayAmountFor('stripe', $ordersTotal));
    }

    public function test_gateway_amount_not_inflated_by_fixed_fee(): void
    {
        $this->seedFee('stripe', 5, PaymentFeeTypeEnum::FIXED);

        $ordersTotal = 105.00;

        $this->assertSame($ordersTotal, $this->gatewayAmountFor('stripe', $ordersTotal));
    }

    public function test_gateway_amount_not_inflated_by_percentage_plus_fixed(): void
    {
        // 2.9% + 0.30 on a $100 order => stored sub-orders total 103.20.
        $this->seedFee('stripe', 2.9, PaymentFeeTypeEnum::PERCENTAGE, 0.30);

        $ordersTotal = 103.20;

        $this->assertSame($ordersTotal, $this->gatewayAmountFor('stripe', $ordersTotal));
    }

    public function test_gateway_amount_unchanged_when_no_fee_configured(): void
    {
        $this->seedFee('stripe', 0, PaymentFeeTypeEnum::FIXED);

        $ordersTotal = 100.00;

        $this->assertSame($ordersTotal, $this->gatewayAmountFor('stripe', $ordersTotal));
    }

    public function test_payment_method_is_still_reported_to_the_gateway(): void
    {
        // The fee block used to be the only reader of payment_method in this method; guard
        // against it being dropped along with the recomputation.
        $this->seedFee('stripe', 2.9, PaymentFeeTypeEnum::PERCENTAGE);

        $provider = app(OrderSupportServiceProvider::class, ['app' => app()]);
        $request = Request::create('/checkout', 'POST', ['payment_method' => 'stripe']);

        $paymentData = $provider->processPaymentMethodPostCheckout($request, 100.0);

        $this->assertSame('stripe', $paymentData['type']);
    }
}
