<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

$taxEngine = app(TaxEngine::class);

echo "======================================================================\n";
echo "DETAILED AUDIT: TAX MODE = AUTO with EXCLUSIVE vs INCLUSIVE\n";
echo "======================================================================\n\n";

// Let's test with a standard test scenario:
// Order Amount = ₹1000 base + ₹100 shipping
// Platform Fee = 8% = ₹80
// Commission Fee = 2% = ₹20
// GST Rate = 18%

// 1. Test intra-state (Rajasthan -> Rajasthan)
// 2. Test inter-state (Rajasthan -> Maharashtra)

$testCases = [
    'Intra-State (Rajasthan -> Rajasthan)' => [
        'seller_state' => 'Rajasthan',
        'sub_amount' => 1000.00,
        'platform_fee' => 80.00,
        'commission_fee' => 20.00,
        'shipping_cost' => 100.00,
    ],
    'Inter-State (Rajasthan -> Maharashtra)' => [
        'seller_state' => 'Maharashtra',
        'sub_amount' => 1000.00,
        'platform_fee' => 80.00,
        'commission_fee' => 20.00,
        'shipping_cost' => 100.00,
    ],
];

setting()->set('seller_invoicing_tax_mode', 'auto');
setting()->set('seller_invoicing_tax_rate', 18);
setting()->set('seller_invoicing_platform_fee', 8);

foreach ($testCases as $label => $tc) {
    echo "----------------------------------------------------------------------\n";
    echo "SCENARIO: $label\n";
    echo "Base Subtotal: ₹{$tc['sub_amount']}, Platform Fee (8%): ₹{$tc['platform_fee']}, Comm Fee (2%): ₹{$tc['commission_fee']}, Shipping: ₹{$tc['shipping_cost']}\n";
    echo "----------------------------------------------------------------------\n";

    // Create mock order & revenue
    $mockStore = new Store();
    $mockStore->state = $tc['seller_state'];
    $mockStore->country = 'India';

    $mockOrder = new Order();
    $mockOrder->id = 99999;
    $mockOrder->sub_total = $tc['sub_amount'];
    $mockOrder->amount = $tc['sub_amount'] + $tc['shipping_cost'];
    $mockOrder->shipping_amount = $tc['shipping_cost'];
    $mockOrder->setRelation('store', $mockStore);

    $mockRevenue = new Revenue();
    $mockRevenue->order_id = 99999;
    $mockRevenue->sub_amount = $tc['sub_amount'];
    $mockRevenue->platform_fee = $tc['platform_fee'];
    $mockRevenue->commission_fee = $tc['commission_fee'];
    $mockRevenue->shipping_cost = $tc['shipping_cost'];
    $mockRevenue->fee_tax_rate = 18;
    $mockRevenue->fee = 118.00; // placeholder

    foreach (['exclusive', 'inclusive'] as $mode) {
        setting()->set('seller_invoicing_fee_tax_type', $mode);
        $res = $taxEngine->resolve($mockOrder, $mockRevenue, $mockStore);

        echo "\n>>> MODE: " . strtoupper($mode) . "\n";
        echo "Taxes: IsIndia=" . ($res['taxes']['is_india'] ? 'Yes' : 'No') . 
             ", IsIGST=" . ($res['taxes']['is_igst'] ? 'Yes' : 'No') . 
             ", RateLabel=" . $res['taxes']['rate_label'] . 
             ", PlaceOfSupply=" . $res['taxes']['place_of_supply'] . "\n";
        echo "INVOICE SUMMARY:\n";
        echo "  Taxable Subtotal: ₹" . number_format($res['subtotal'], 2) . "\n";
        echo "  GST Tax Amount:   ₹" . number_format($res['tax_amount'], 2) . " (CGST: ₹" . number_format($res['taxes']['cgst_amount'], 2) . ", SGST: ₹" . number_format($res['taxes']['sgst_amount'], 2) . ", IGST: ₹" . number_format($res['taxes']['igst_amount'], 2) . ")\n";
        echo "  Grand Total:      ₹" . number_format($res['grand_total'], 2) . "\n";
        echo "LINE ITEMS BREAKDOWN:\n";
        foreach ($res['lines'] as $line) {
            echo sprintf(
                "  - %-26s | Taxable: ₹%7.2f | Rate: %2d%% | CGST: ₹%5.2f | SGST: ₹%5.2f | IGST: ₹%5.2f | Total: ₹%7.2f\n",
                $line['name'],
                $line['taxable_amount'],
                $line['tax_rate'],
                $line['cgst_amount'],
                $line['sgst_amount'],
                $line['igst_amount'],
                $line['total_amount']
            );
        }
    }
    echo "\n";
}

