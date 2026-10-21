<?php
namespace SparroWave\AdvancedCod\Providers;
use Illuminate\Support\ServiceProvider;
use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Payment\Enums\PaymentMethodEnum;
use SparroWave\AdvancedCod\Listeners\ProcessAdminManualCodOrder;
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

            add_filter('core_layout_before_content', function ($html) {
                if (! request()->routeIs('orders.create') && ! request()->routeIs('orders.reorder')) {
                    return $html;
                }
                $config = [
                    'percentage' => (float) \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage(),
                    'currency_symbol' => get_application_currency()->symbol,
                    'currency_title' => get_application_currency()->title,
                    'admin_full_cod_override' => (bool) get_payment_setting('admin_full_cod_override', 'cod', 0),
                ];
                return $html . '<script>window.advancedCodConfig = ' . json_encode($config) . ';</script>';
            }, 99);

            add_action(BASE_ACTION_ENQUEUE_SCRIPTS, function (): void {
                if (! request()->routeIs('orders.create') && ! request()->routeIs('orders.reorder')) {
                    return;
                }
                $v = file_exists(public_path('vendor/core/plugins/advanced-cod/js/admin-order-advanced-cod.js'))
                    ? filemtime(public_path('vendor/core/plugins/advanced-cod/js/admin-order-advanced-cod.js'))
                    : time();
                Assets::addScriptsDirectly('vendor/core/plugins/advanced-cod/js/admin-order-advanced-cod.js?v=' . $v);
            }, 99);

            $this->app['events']->listen(
                OrderCreated::class,
                ProcessAdminManualCodOrder::class
            );
        });
    }
}
