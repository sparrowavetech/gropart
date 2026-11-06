<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Illuminate\View\View;

class ProductAdminViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['view']->composer('plugins/ecommerce::products.view', function (View $view): void {
            $view->setPath(plugin_path('farmart-helper/resources/views/products/view.blade.php'));

            $data = $view->getData();
            $product = $data['product'] ?? null;

            if ($product instanceof Product) {
                $productIds = [$product->id];
                $variantIds = $product->variations()->pluck('product_id')->toArray();
                if (! empty($variantIds)) {
                    $productIds = array_merge($productIds, $variantIds);
                }

                $finishedOrders = OrderProduct::query()
                    ->whereIn('ec_order_product.product_id', $productIds)
                    ->join('ec_orders', 'ec_orders.id', '=', 'ec_order_product.order_id')
                    ->where('ec_orders.is_finished', 1)
                    ->with(['order.user', 'order.address'])
                    ->select('ec_order_product.*')
                    ->latest('ec_orders.created_at')
                    ->limit(10)
                    ->get();

                $incompleteOrders = OrderProduct::query()
                    ->whereIn('ec_order_product.product_id', $productIds)
                    ->join('ec_orders', 'ec_orders.id', '=', 'ec_order_product.order_id')
                    ->where('ec_orders.is_finished', 0)
                    ->with(['order.user', 'order.address'])
                    ->select('ec_order_product.*')
                    ->latest('ec_orders.created_at')
                    ->limit(10)
                    ->get();

                $view->with([
                    'finishedOrders' => $finishedOrders,
                    'incompleteOrders' => $incompleteOrders,
                    'store' => $product->original_product->store ?? null,
                ]);
            }
        });
    }
}

