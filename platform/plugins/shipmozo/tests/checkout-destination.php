<?php
namespace Botble\Base\Http\Controllers { class BaseController { public function httpResponse() { return new class { public function setData($data) { return $data; } }; } } }
namespace Botble\Ecommerce\Facades {
    class OrderHelper { public static array $data=[]; public static function getOrderSessionData($token){return self::$data;} public static function getOrderSessionToken(){return 'test';} public static function setOrderSessionData($token,$data){self::$data=$data;} }
    class Cart { public static function instance($name){return new self;} public function products(){return new \Illuminate\Database\Eloquent\Collection;} }
    class EcommerceHelper { public static function isEnabledSupportDigitalProducts(){return false;} }
}
namespace Botble\Ecommerce\Models {
    class Address { public static function query(){return new self;} public function where($key,$id){if($id!==42)throw new \RuntimeException('Wrong owner');return $this;} public function findOrFail($id){if($id!==7)throw new \RuntimeException('Address not owned');return $this;} public function toArray(){return ['country'=>'IN','state'=>'30','city'=>'36','zip_code'=>'313002','phone'=>'private'];} }
}
namespace Botble\Ecommerce\Services {
    class HandleTaxService { public static array $location; public function execute($products,$location){self::$location=$location;} }
    class HandleCheckoutOrderData { public array $received; public function execute($request,$products,$token,&$session){$this->received=$session;if(HandleTaxService::$location!==$session)throw new \RuntimeException('Tax must use current location before quote');return (object)array_fill_keys(['orderAmount','paymentFee','rawTotal','shipping','shippingAmount','promotionDiscountAmount','couponDiscountAmount','shippingTaxAmount','defaultShippingOption','defaultShippingMethod'],0);} }
}
namespace {
require dirname(__DIR__,4).'/vendor/autoload.php';
require dirname(__DIR__,2).'/ecommerce/src/Http/Controllers/Fronts/PublicUpdateCheckoutController.php';
require dirname(__DIR__).'/src/Http/Controllers/UpdateCheckoutController.php';
function auth($guard){return new class {public function check(){return true;}public function id(){return 42;}};}
function abort_unless($ok,$status){if(!$ok)throw new \RuntimeException('Unauthorized');}
function app($class){return new $class;}
function add_filter(...$args){}
function apply_filters($name,$value,...$args){return $value;}
function view($name,$data=[]){return new class {public function render(){return '';}};}
$validator=new \Illuminate\Validation\Factory(new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader,'en'));
\Illuminate\Http\Request::macro('validate',fn($rules)=>$validator->make($this->all(),$rules)->validate());
function check($ok,$message){if(!$ok)throw new \RuntimeException($message);echo "PASS $message\n";}
$controller=new \SparroWave\Shipmozo\Http\Controllers\UpdateCheckoutController;
$service=new \Botble\Ecommerce\Services\HandleCheckoutOrderData;
\Botble\Ecommerce\Facades\OrderHelper::$data=['country'=>'IN','state'=>'old','city'=>'old','zip_code'=>'111111','name'=>'preserve'];
$controller(\Illuminate\Http\Request::create('/','POST',['address'=>['state'=>'30','city'=>'36','zip_code'=>'313002','name'=>'overwrite','amount'=>1]]),$service);
check($service->received['zip_code']==='313002' && $service->received['state']==='30','current request destination used without contact fields');
check($service->received['name']==='preserve' && !isset($service->received['amount']),'quote does not accept contact/financial fields into session');
$controller(\Illuminate\Http\Request::create('/','POST',['address'=>['zip_code'=>'']]),$service);
check($service->received['zip_code']==='','cleared PIN cannot reuse previous serviceability');
$controller(\Illuminate\Http\Request::create('/','POST',['address'=>['address_id'=>7,'zip_code'=>'000000']]),$service);
check($service->received['zip_code']==='313002' && !isset($service->received['phone']),'saved address is resolved from authenticated owner');
try{$controller(\Illuminate\Http\Request::create('/','POST',['address'=>['address_id'=>8]]),$service);throw new \LogicException('Foreign address accepted');}catch(\RuntimeException $e){check($e->getMessage()==='Address not owned','foreign saved address rejected');}
echo "ALL DESTINATION TESTS PASSED\n";
}
