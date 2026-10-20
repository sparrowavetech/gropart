<?php
// Isolated regression: real quote/fee-hook code, fake HTTP, no CMS boot or DB.
namespace Botble\Ecommerce\Enums { class ShippingMethodEnum { const DEFAULT = 'default'; } }
namespace Botble\Payment\Enums { class PaymentMethodEnum { const COD = 'cod'; } }
namespace Botble\Marketplace\Models { class Store { public static $store; public static function find($id) { return self::$store; } } }
namespace {
require dirname(__DIR__, 4).'/vendor/autoload.php';
require dirname(__DIR__).'/src/Shipmozo.php';
require dirname(__DIR__).'/src/Providers/HookServiceProvider.php';
define('SHIPMOZO_SHIPPING_METHOD_NAME', 'shipmozo');
function setting($key, $default = null) { return $key === 'shipping_shipmozo_status' ? 1 : $default; }
function is_plugin_active($plugin) { return $plugin === 'marketplace'; }
function apply_filters($name, $value, ...$args) { return $name === 'ecommerce_product_has_free_shipping' ? ($args[0]['product_free_shipping'] ?? false) : $value; }
function __($value) { return $value; }
function app($class) { return $GLOBALS['carrier']; }
class QuoteCarrier extends \SparroWave\Shipmozo\Shipmozo {
    public $response = ['result' => 1, 'data' => [['courier_id' => 403, 'courier_name' => 'Courier', 'total_charge' => 87]]];
    public int $calls = 0;
    public bool $fail = false;
    public array $payload = [];
    public function __construct() {}
    public function log(string $message, array $context = []): void {}
    public function logError(string $message, array $context = []): void {}
    protected function httpClient(bool $retry = false): \Illuminate\Http\Client\PendingRequest {
        if ($this->fail) throw new \RuntimeException('Simulated timeout');
        $factory = new \Illuminate\Http\Client\Factory();
        $factory->preventStrayRequests();
        $factory->fake(function ($request) {
            $this->calls++; $this->payload = $request->data();
            return \Illuminate\Http\Client\Factory::response($this->response);
        });
        return $factory->asJson();
    }
}
function check($condition, $message) { if (!$condition) throw new \RuntimeException($message); echo "PASS $message\n"; }
$carrier = new QuoteCarrier();
$hook = new \SparroWave\Shipmozo\Providers\HookServiceProvider(new \Illuminate\Container\Container());
$data = ['address_to'=>['zip_code'=>'313002'],'origin'=>['zip_code'=>'312403'],'items'=>[42=>['weight'=>.45,'qty'=>1,'price'=>229]],'payment_method'=>'razorpay'];
$flat = ['default'=>['flat'=>['price'=>99,'disabled'=>false]]];
foreach (['', '313', '000000', 'abc123'] as $pin) {
    $r=$hook->handleShippingFee($flat, array_replace($data,['address_to'=>['zip_code'=>$pin]]));
    check(!isset($r['default']) && $r['shipmozo']['no_service']['disabled'], "invalid/incomplete destination blocked: $pin");
}
check($carrier->calls === 0, 'incomplete PINs never call API');
$r=$hook->handleShippingFee($flat,$data);
check(!isset($r['default']) && $r['shipmozo']['shipmozo_403']['price']===87.0, 'valid carrier quote replaces flat rate');
$r=$hook->handleShippingFee($flat,array_replace($data,['origin'=>['zip_code'=>'']]));
check($r['shipmozo']['no_service']['shipmozo_status']==='pickup', 'pickup error has seller-specific state');
$carrier->response=['result'=>1,'data'=>[]];
$r=$hook->handleShippingFee($flat,$data);
check(!isset($r['default']) && $r['shipmozo']['no_service']['disabled'], 'no couriers cannot fall back to flat rate');
$carrier->response=['result'=>0,'message'=>'Invalid API credentials'];
$r=$hook->handleShippingFee($flat,$data);
check($r['shipmozo']['no_service']['shipmozo_status']==='api_error','API failure is not blamed on delivery PIN');
$carrier->fail=true;
$r=$hook->handleShippingFee($flat,$data);
check(!isset($r['default']) && $r['shipmozo']['no_service']['shipmozo_status']==='api_error','timeout fails closed with retry state');
$carrier->fail=false;
$free=['default'=>['free_shipping'=>['price'=>0,'disabled'=>false]]];
$data['items'][42]['product_free_shipping']=true;
$before=$carrier->calls;
$carrier->response=['result'=>1,'data'=>[['courier_id'=>403,'total_charge'=>87]]];
$r=$hook->handleShippingFee($free,$data);
check($carrier->calls===$before+1 && $carrier->payload['weight']===450,'free delivery still checks physical package');
check(!isset($r['default']) && $r['shipmozo']['shipmozo_403']['price']===0,'serviceable free delivery keeps courier at zero customer cost');
$carrier->response=['result'=>1,'data'=>[]];
$r=$hook->handleShippingFee($free,$data);
check(!isset($r['default']) && $r['shipmozo']['no_service']['disabled'],'unserviceable free delivery cannot bypass check');
\Botble\Marketplace\Models\Store::$store=(object)['vendor_managed_shipping'=>true,'is_manage_shipping'=>false];
$data['extra']['store_id']=12;
check($hook->handleShippingFee($flat,$data)===$flat,'explicit vendor-managed shipping uses extra.store_id and retains its policy');
\Botble\Marketplace\Models\Store::$store=(object)['vendor_managed_shipping'=>false,'is_manage_shipping'=>true];
$data['items'][42]['product_free_shipping']=false;
$carrier->response=['result'=>1,'data'=>[['courier_id'=>403,'total_charge'=>87]]];
$before=$carrier->calls;
$r=$hook->handleShippingFee($flat,$data);
check($carrier->calls===$before+1 && !isset($r['default']) && $r['shipmozo']['shipmozo_403']['price']===87.0,'legacy shipping flag cannot bypass current carrier-managed policy');
check($hook->handleShippingFee($flat,['items'=>[]])===$flat,'nonphysical package unaffected');
echo "ALL CHECKOUT SERVICEABILITY TESTS PASSED\n";
}
