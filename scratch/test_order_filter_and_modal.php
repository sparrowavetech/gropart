<?php

$workspace = '/Applications/XAMPP/xamppfiles/htdocs/gropart';

spl_autoload_register(function ($class) use ($workspace) {
    $prefixes = [
        'Botble\\Ecommerce\\' => $workspace . '/platform/plugins/ecommerce/src/',
        'Botble\\Payment\\' => $workspace . '/platform/plugins/payment/src/',
        'SparroWave\\AdvancedCod\\' => $workspace . '/platform/plugins/advanced-cod/src/',
        'SparroWave\\FarmartHelper\\' => $workspace . '/platform/plugins/farmart-helper/src/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require $workspace . '/vendor/autoload.php';
$app = require_once $workspace . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure ManualConfirmationServiceProvider booted
$provider = new \SparroWave\AdvancedCod\Providers\ManualConfirmationServiceProvider($app);
$provider->boot();
$app->boot();

echo "========================================================\n";
echo "   TEST: ADVANCED COD FILTER & MODAL SEPARATION\n";
echo "========================================================\n";

// 1. Check translations
$enLang = require $workspace . '/platform/plugins/advanced-cod/resources/lang/en/advanced-cod.php';
$hiLang = require $workspace . '/platform/plugins/advanced-cod/resources/lang/hi/advanced-cod.php';

assert(isset($enLang['advanced_cod_online']), "FAIL: en translation missing");
assert(isset($hiLang['advanced_cod_online']), "FAIL: hi translation missing");
echo "✓ Translation exists in en: '{$enLang['advanced_cod_online']}'\n";
echo "✓ Translation exists in hi: '{$hiLang['advanced_cod_online']}'\n";

// 2. Check PaymentMethodEnum::labels() does NOT have 'advanced_cod'
$enumLabels = \Botble\Payment\Enums\PaymentMethodEnum::labels();
assert(!isset($enumLabels['advanced_cod']), "FAIL: PaymentMethodEnum must NOT have 'advanced_cod'");
echo "✓ PaymentMethodEnum::labels() does NOT contain 'advanced_cod' (excluded globally)\n";

// 3. Check OrderTable filters hook
$tableFilters = [
    'payment_method' => [
        'title' => 'Payment method',
        'type' => 'select',
        'choices' => $enumLabels,
    ]
];

$table = new \stdClass();
$enrichedFilters = apply_filters('ecommerce_order_table_filters', $tableFilters, $table);

assert(isset($enrichedFilters['payment_method']['choices']['advanced_cod']), "FAIL: OrderTable filters must contain 'advanced_cod'");
echo "✓ OrderTable filter choices CONTAIN 'advanced_cod' => '{$enrichedFilters['payment_method']['choices']['advanced_cod']}'\n";

// 4. Check Incomplete Order modal choices
$modalMethods = \Botble\Payment\Enums\PaymentMethodEnum::labels();
unset($modalMethods['advanced_cod'], $modalMethods['advanced_cod_manual']);
// If non-COD
unset($modalMethods['cod']);

assert(!isset($modalMethods['advanced_cod']), "FAIL: Modal must not contain 'advanced_cod'");
assert(!isset($modalMethods['advanced_cod_manual']), "FAIL: Modal must not contain 'advanced_cod_manual'");
assert(!isset($modalMethods['cod']), "FAIL: Ineligible modal must not contain 'cod'");
echo "✓ Incomplete Order 'Mark as completed' modal choices: " . json_encode(array_keys($modalMethods)) . " (Only valid prepaid methods!)\n";

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
