<?php

use Illuminate\Contracts\Console\Kernel;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Running Tax Engine & PDF Generation tests...\n";

// 1. Test TaxEngine State Normalization
$engine = new class extends TaxEngine {
    public function testNormalize(string $state): string {
        return $this->normalizeState($state);
    }
};

assert($engine->testNormalize('Rajasthan') === 'rajasthan', 'Normalize Rajasthan failed');
assert($engine->testNormalize('Uttar Pradesh') === 'uttar', 'Normalize Uttar Pradesh failed');
assert($engine->testNormalize('Madhya Pradesh') === 'madhya', 'Normalize Madhya Pradesh failed');
echo "1. State normalization: PASS\n";

// 2. Test PDF Service Template & Variables
$service = new GenerateSellerInvoiceService();
$content = $service->getContent();
assert(! empty($content), 'Template content is empty');
assert(str_contains($content, 'TAX INVOICE'), 'Template content missing TAX INVOICE header');
assert(str_contains($content, 'invoice.number'), 'Template missing invoice.number');

$vars = $service->getVariables();
assert(isset($vars['company.name']), 'Variables missing company.name');
assert(isset($vars['seller.name']), 'Variables missing seller.name');
assert(isset($vars['invoice.grand_total']), 'Variables missing invoice.grand_total');
echo "2. Template & Variables: PASS\n";

// 3. Test In-Memory Preview PDF Generation
$response = $service->preview();
$binary = method_exists($response, 'getFile') ? file_get_contents($response->getFile()->getPathname()) : $response->getContent();
assert(str_starts_with($binary, '%PDF-'), 'Preview output is not a valid PDF binary');
assert(strlen($binary) > 1000, 'Preview PDF is suspiciously small');
echo "3. Preview PDF generation (%PDF binary " . strlen($binary) . " bytes): PASS\n";

echo "ALL TAX & PDF TESTS PASSED!\n";
