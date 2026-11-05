<?php

use SparroWave\SellerInvoicing\Supports\DocumentArithmetic;
use SparroWave\SellerInvoicing\Supports\Money;
use SparroWave\SellerInvoicing\Supports\RevenueReconciler;

require dirname(__DIR__) . '/src/Supports/Money.php';
require dirname(__DIR__) . '/src/Supports/RevenueReconciler.php';
require dirname(__DIR__) . '/src/Supports/DocumentArithmetic.php';

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function rejects(callable $action, string $message): void
{
    try {
        $action();
    } catch (InvalidArgumentException|DomainException) {
        return;
    }

    throw new RuntimeException($message);
}

check(Money::toMinor('23.9200', 2) === 2392, 'Trailing zero precision failed.');
check(Money::toMinor('-1.205', 3) === -1205, 'Three-decimal currency failed.');
check(Money::toMinor('10', 0) === 10, 'Zero-decimal currency failed.');
check(Money::fromMinor(5, 3) === '0.005', 'Minor-unit display failed.');
rejects(fn () => Money::toMinor('23.921', 2), 'Excess precision was accepted.');
rejects(fn () => Money::toMinor('1e3', 2), 'Scientific notation was accepted.');

$reconciler = new RevenueReconciler();
$balanced = $reconciler->assess([
    'seller_merchandise_base' => '100.00',
    'existing_marketplace_fee' => '5.25',
    'existing_seller_net' => '94.75',
], 2);
check($balanced['status'] === 'balanced' && $balanced['difference_minor'] === 0, 'Revenue balance failed.');
$mismatch = $reconciler->assess([
    'seller_merchandise_base' => '100.00',
    'existing_marketplace_fee' => '5.25',
    'existing_seller_net' => '94.74',
], 2);
check($mismatch['status'] === 'needs_review' && $mismatch['difference_minor'] === 1, 'Revenue mismatch was missed.');

$arithmetic = new DocumentArithmetic();
$lines = [
    ['taxable_minor' => 2392, 'taxes_minor' => ['CGST' => 215, 'SGST' => 215], 'total_minor' => 2822],
    ['taxable_minor' => 598, 'taxes_minor' => ['CGST' => 54, 'SGST' => 54], 'total_minor' => 706],
];
$summary = ['taxable_minor' => 2990, 'taxes_minor' => ['CGST' => 269, 'SGST' => 269], 'total_minor' => 3528];
$arithmetic->assertBalanced($lines, $summary);
$oldPrintedLines = $lines;
$oldPrintedLines[0]['total_minor'] = 2823;
rejects(fn () => $arithmetic->assertBalanced($oldPrintedLines, $summary), 'Old sample one-paisa error was accepted.');

echo "Money, revenue and document reconciliation: PASS\n";
