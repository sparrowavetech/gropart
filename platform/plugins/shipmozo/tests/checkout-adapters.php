<?php
namespace Botble\Ecommerce\Enums { class ShippingMethodEnum { const DEFAULT = "default"; } }
namespace {
// Isolated adapter tests: no Laravel boot, database, network or order writes.
require dirname(__DIR__, 4) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/ecommerce/src/Services/HandleShippingFeeService.php';
require dirname(__DIR__) . '/src/Services/CheckoutShippingFeeService.php';
require dirname(__DIR__) . '/src/Http/Middleware/CheckoutAssets.php';
function get_ecommerce_setting($key, $default = null) { return true; }
function apply_filters($key, $value, ...$args) { return $GLOBALS['rates']; }
function setting($key) { return $GLOBALS['enabled']; }
function public_path($path) { return dirname(__DIR__, 4) . '/public/' . $path; }
function asset($path) { return 'https://test.invalid/' . $path; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); echo "PASS $message\n"; }
$service = new class extends \SparroWave\Shipmozo\Services\CheckoutShippingFeeService {
    public function __construct() {}
    protected function getCacheKey(array $data): string { return 'fixture'; }
    protected function getCacheValue(string $key): array { return []; }
    protected function getShippingFee(array $data, string $method, ?string $option = null): array { return []; }
    protected function setCacheValue(string $key, mixed $value): bool { return true; }
};
$GLOBALS['rates'] = ['shipmozo' => ['no_service' => ['price' => 0, 'disabled' => true]], 'other' => ['paid' => ['price' => 87]]];
check(isset($service->execute([])['other']['paid']), 'unavailable zero quote does not hide a valid paid carrier');
check($service->execute([], 'shipmozo', 'no_service') === [], 'disabled selected quote rejected');
$GLOBALS['rates']['shipmozo'] = ['free' => ['price' => 0]];
check(empty($service->execute([])['other']), 'real available free quote retains hide-paid setting');
$GLOBALS['enabled'] = 1;
$middleware = new \SparroWave\Shipmozo\Http\Middleware\CheckoutAssets;
$html = '<script src="https://test.invalid/vendor/core/plugins/ecommerce/js/checkout.js?v=123"></script><script src="/other.js"></script>';
$next = fn () => new \Symfony\Component\HttpFoundation\Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
$result = $middleware->handle(\Illuminate\Http\Request::create('/'), $next)->getContent();
check(str_contains($result, '/plugins/shipmozo/js/checkout.js?v=') && !str_contains($result, '/plugins/ecommerce/js/checkout.js'), 'plugin replaces rather than duplicates checkout runtime');
check(str_contains($result, '<script src="/other.js"></script>'), 'unrelated layout assets preserved');
$GLOBALS['enabled'] = 0;
check($middleware->handle(\Illuminate\Http\Request::create('/'), $next)->getContent() === $html, 'disabled ShipMozo leaves core asset untouched');

}
