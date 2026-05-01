<?php

namespace Botble\EcommerceWholesale\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;

class VendorWholesaleController extends BaseController
{
    public function index()
    {
        if (! WholesaleHelper::isVendorDashboardEnabled()) {
            abort(404);
        }

        $store = auth('customer')->user()->store;

        if (! $store->id) {
            abort(404);
        }

        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.wholesale_products'));

        $products = Product::query()
            ->where('store_id', $store->id)
            ->where('is_variation', false)
            ->whereHas('groupPricingRules', function ($query) use ($store): void {
                $query->where('store_id', $store->id);
            })
            ->with(['groupPricingRules' => function ($query) use ($store): void {
                $query->where('store_id', $store->id)->with('customerGroup');
            }])
            ->latest()
            ->paginate(20);

        return WholesaleHelper::view('vendor-dashboard.wholesale-products', compact('products'));
    }
}
