<?php
namespace SparroWave\AdvancedCod\Providers;
use Illuminate\Support\ServiceProvider;
use Botble\Payment\Enums\PaymentMethodEnum;
use SparroWave\AdvancedCod\Supports\{ManualConfirmation,OrderEligibility};
class ManualConfirmationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web',\SparroWave\AdvancedCod\Http\Middleware\GuardManualCod::class);
        $this->app->booted(function(){
            add_filter(BASE_FILTER_ENUM_ARRAY,function($values,$class){if($class===PaymentMethodEnum::class)$values['ADVANCED_COD_MANUAL']=ManualConfirmation::CHANNEL;return $values;},30,2);
            add_filter(BASE_FILTER_ENUM_LABEL,function($value,$class){return $class===PaymentMethodEnum::class && $value===ManualConfirmation::CHANNEL?'Manual COD advance receipt':$value;},30,2);
            add_filter('ecommerce_admin_order_extra_info',function($html,$order){
                if(!\Botble\Base\Facades\AdminHelper::isInAdmin() || !ManualConfirmation::available($order))return $html;
                return $html.view('plugins/advanced-cod::manual.tags',['order'=>$order,'flags'=>OrderEligibility::read($order)])->render();
            },190,2);
            add_filter('ecommerce_order_product_item_extra_info', function ($html, $item, $order) {
                if (!\Botble\Base\Facades\AdminHelper::isInAdmin()) return $html;
                $product = $item->product?->original_product;
                if (!$product?->id) return $html;
                $badges = app(\SparroWave\AdvancedCod\Hooks\AdvancedCodHookListener::class)->addCodLabelToProductCard('', $product);
                return $html . ($badges ? '<div class="mt-1 order-product-delivery-labels">' . $badges . '</div>' : '');
            }, 190, 3);
        });
    }
}
