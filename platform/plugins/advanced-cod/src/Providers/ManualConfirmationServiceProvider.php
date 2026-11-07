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
            add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
                if ($class === PaymentMethodEnum::class) {
                    $values['ADVANCED_COD_MANUAL'] = ManualConfirmation::CHANNEL;
                }
                return $values;
            }, 30, 2);

            add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
                if ($class === PaymentMethodEnum::class) {
                    if ($value === ManualConfirmation::CHANNEL) {
                        return trans('plugins/advanced-cod::advanced-cod.manual_receipt_channel');
                    }
                }
                return $value;
            }, 30, 2);

            add_filter('ecommerce_order_table_filters', function (array $filters, $table) {
                if (isset($filters['payment_method']['choices'])) {
                    $filters['payment_method']['choices']['advanced_cod'] = trans('plugins/advanced-cod::advanced-cod.advanced_cod_online');
                }
                return $filters;
            }, 30, 2);

            add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) {
                if ($table instanceof \Botble\Ecommerce\Tables\OrderTable) {
                    $filterValues = request()->input('filter_values', []);
                    $filterColumns = request()->input('filter_columns', []);
                    $hasAdvancedCod = false;
                    foreach ($filterColumns as $idx => $col) {
                        if ($col === 'payment_method' && ($filterValues[$idx] ?? '') === 'advanced_cod') {
                            $hasAdvancedCod = true;
                            break;
                        }
                    }
                    if ($hasAdvancedCod) {
                        $wheres = $query->getQuery()->wheres;
                        foreach ($wheres as $k => $w) {
                            if (($w['type'] ?? '') === 'Exists') {
                                unset($wheres[$k]);
                            }
                        }
                        $query->getQuery()->wheres = array_values($wheres);
                        $query->getQuery()->bindings['where'] = array_values(array_filter(
                            $query->getQuery()->bindings['where'],
                            fn($b) => $b !== 'advanced_cod'
                        ));
                        $query->where(function ($q) {
                            $q->whereNotNull('ec_orders.cod_prepayment_amount')
                              ->where('ec_orders.cod_prepayment_amount', '>', 0)
                              ->orWhereHas('payment', fn($p) => $p->whereIn('payment_channel', ['advanced_cod', ManualConfirmation::CHANNEL]));
                        });
                    }
                }
                return $query;
            }, 50, 2);
            add_filter('ecommerce_admin_order_extra_info',function($html,$order){
                if(!\Botble\Base\Facades\AdminHelper::isInAdmin() || !ManualConfirmation::available($order))return $html;
                return $html.view('plugins/advanced-cod::manual.tags',['order'=>$order,'flags'=>OrderEligibility::read($order)])->render();
            },190,2);
            add_filter('ecommerce_order_product_item_extra_info', function ($html, $item, $order) {
                if (!\Botble\Base\Facades\AdminHelper::isInAdmin()) return $html;
                $product = $item->product?->original_product;
                if (!$product?->id) return $html;
                $badges = app(\SparroWave\AdvancedCod\Hooks\AdvancedCodHookListener::class)->addCodLabelToProductCard('', $product, true);
                return $html . ($badges ? '<div class="mt-1 order-product-delivery-labels">' . $badges . '</div>' : '');
            }, 190, 3);

            add_filter('core_layout_before_content', function ($html) {
                if (! request()->routeIs('orders.create') && ! request()->routeIs('orders.reorder')) {
                    return $html;
                }
                $config = [
                    'percentage' => (float) \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage(),
                    'fee_percentage' => (float) get_payment_setting('fee', 'cod', 4),
                    'fee_type' => get_payment_setting('fee_type', 'cod', 'percentage'),
                    'fee_fixed' => (float) get_payment_setting('fee_fixed', 'cod', 0),
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
