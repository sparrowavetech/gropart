<?php

use Botble\Marketplace\Forms\Settings\MarketplaceSettingForm;
use Botble\Setting\Facades\Setting;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SparroWave\SellerInvoicing\Hooks\MarketplaceSettingHookListener;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

echo "Running Marketplace Settings Extension & Format tests...\n";

DB::beginTransaction();

try {
    // 1. Test MarketplaceSettingForm has our fields
    $form = MarketplaceSettingForm::create();

    assert($form->has('default_fee_tax'), 'MarketplaceSettingForm is missing default_fee_tax');
    assert($form->has('authorised_signature_image'), 'MarketplaceSettingForm is missing authorised_signature_image');
    assert($form->has('seller_invoice_code_prefix'), 'MarketplaceSettingForm is missing seller_invoice_code_prefix');
    assert($form->has('seller_invoice_code_suffix'), 'MarketplaceSettingForm is missing seller_invoice_code_suffix');
    echo "1. MarketplaceSettingForm fields extended: PASS\n";

    // 2. Test saving via MarketplaceSettingHookListener
    $fakeRequest = Request::create('/admin/marketplaces/settings', 'PUT', [
        'default_fee_tax' => '12.5',
        'authorised_signature_image' => 'signatures/admin_sig.png',
        'seller_invoice_code_prefix' => 'GRO/SI/',
        'seller_invoice_code_suffix' => '/2026',
    ]);

    MarketplaceSettingHookListener::saveSettings($fakeRequest);

    assert(setting('marketplace_default_fee_tax') === '12.5', 'marketplace_default_fee_tax not saved');
    assert(setting('seller_invoice_code_prefix') === 'GRO/SI/', 'seller_invoice_code_prefix not saved');
    assert(setting('seller_invoice_code_suffix') === '/2026', 'seller_invoice_code_suffix not saved');
    assert(setting('marketplace_authorised_signature_image') === 'signatures/admin_sig.png', 'authorised_signature_image not saved');
    echo "2. Marketplace settings save hook: PASS\n";

    // 3. Test dynamic invoice code formatting with prefix and suffix
    $generator = app(InvoiceGeneratorService::class);
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('generateNextInvoiceNumber');
    $method->setAccessible(true);
    $number = $method->invoke($generator);

    echo "Generated formatted number: {$number}\n";
    assert(str_starts_with($number, 'GRO/SI/'), "Number does not start with prefix GRO/SI/: {$number}");
    assert(str_ends_with($number, '/2026'), "Number does not end with suffix /2026: {$number}");
    echo "3. Dynamic invoice number format (GRO/SI/.../2026): PASS\n";

    echo "ALL MARKETPLACE SETTINGS & FORMAT TESTS PASSED!\n";
} finally {
    DB::rollBack();
    echo "Database transaction rolled back cleanly.\n";
}
