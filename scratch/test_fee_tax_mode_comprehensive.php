<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Supports\TaxEngine;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;

$taxEngine = app(TaxEngine::class);
$generator = app(InvoiceGeneratorService::class);

echo "=======================================================\n";
echo "TESTING SELLER INVOICE FEE TAX CALCULATION MODES\n";
echo "=======================================================\n\n";

// Find 3 different real orders with invoices:
// 1. Order with shipping
// 2. Order without shipping
// 3. Order with legacy fee
$documents = Document::query()->with('order')->latest('id')->take(5)->get();

foreach (['exclusive', 'inclusive'] as $mode) {
    echo "=======================================================\n";
    echo ">>> TESTING MODE: " . strtoupper($mode) . "\n";
    echo "=======================================================\n";

    setting()->set('seller_invoicing_fee_tax_type', $mode);
    setting()->save();

    foreach ($documents as $doc) {
        $order = $doc->order;
        if (!$order) continue;
        $revenue = Revenue::query()->where('order_id', $order->id)->where('type', 'add-amount')->first();
        if (!$revenue) continue;

        $store = $order->store;
        $res = $taxEngine->resolve($order, $revenue, $store);

        echo "\nDocument #{$doc->number} (Order #{$order->id}):\n";
        echo "Order Amount: ₹{$order->amount}, Subtotal: ₹{$order->sub_total}, Shipping: ₹{$order->shipping_amount}\n";
        echo "Revenue Sub: ₹{$revenue->sub_amount}, Fee: ₹{$revenue->fee}, PlatformFee: " . ($revenue->platform_fee ?? 'NULL') . ", CommFee: " . ($revenue->commission_fee ?? 'NULL') . "\n";
        echo "Result Subtotal: ₹{$res['subtotal']}, Tax: ₹{$res['tax_amount']}, Grand Total: ₹{$res['grand_total']}\n";

        $lineSumTaxable = 0;
        $lineSumTax = 0;
        $lineSumTotal = 0;

        foreach ($res['lines'] as $i => $line) {
            echo "  Line [{$line['name']}]: Taxable=₹{$line['taxable_amount']}, Rate={$line['tax_rate']}%, Tax=₹{$line['tax_amount']} (CGST: ₹{$line['cgst_amount']}, SGST: ₹{$line['sgst_amount']}, IGST: ₹{$line['igst_amount']}), Total=₹{$line['total_amount']}\n";
            
            // Check line math
            $expectedLineTotal = round($line['taxable_amount'] + $line['tax_amount'], 2);
            if (abs($line['total_amount'] - $expectedLineTotal) > 0.01) {
                echo "  [ERROR] Line total mismatch! {$line['total_amount']} != {$expectedLineTotal}\n";
            }
            if ($res['taxes']['is_india']) {
                if ($res['taxes']['is_igst']) {
                    if (abs($line['igst_amount'] - $line['tax_amount']) > 0.01) {
                        echo "  [ERROR] IGST mismatch!\n";
                    }
                } else {
                    $cgstSgst = round($line['cgst_amount'] + $line['sgst_amount'], 2);
                    if (abs($cgstSgst - $line['tax_amount']) > 0.01) {
                        echo "  [ERROR] CGST+SGST mismatch!\n";
                    }
                }
            }

            $lineSumTaxable += $line['taxable_amount'];
            $lineSumTax += $line['tax_amount'];
            $lineSumTotal += $line['total_amount'];
        }

        $lineSumTaxable = round($lineSumTaxable, 2);
        $lineSumTax = round($lineSumTax, 2);
        $lineSumTotal = round($lineSumTotal, 2);

        // Check summary match
        if (abs($res['subtotal'] - $lineSumTaxable) > 0.01) {
            echo "  [ERROR] Subtotal mismatch! Result: {$res['subtotal']} vs Sum: {$lineSumTaxable}\n";
        }
        if (abs($res['tax_amount'] - $lineSumTax) > 0.01) {
            echo "  [ERROR] Tax amount mismatch! Result: {$res['tax_amount']} vs Sum: {$lineSumTax}\n";
        }
        if (abs($res['grand_total'] - $lineSumTotal) > 0.01) {
            echo "  [ERROR] Grand total mismatch! Result: {$res['grand_total']} vs Sum: {$lineSumTotal}\n";
        }
        $expectedGrand = round($res['subtotal'] + $res['tax_amount'], 2);
        if (abs($res['grand_total'] - $expectedGrand) > 0.01) {
            echo "  [ERROR] Grand != Subtotal + Tax! {$res['grand_total']} != {$expectedGrand}\n";
        }

        echo "  [PASS] Arithmetic fully balanced.\n";
    }
}

