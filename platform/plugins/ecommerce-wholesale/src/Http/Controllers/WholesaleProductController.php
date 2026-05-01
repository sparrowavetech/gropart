<?php

namespace Botble\EcommerceWholesale\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;

class WholesaleProductController extends BaseController
{
    public function index(Request $request)
    {
        page_title()->setTitle(trans('plugins/ecommerce-wholesale::wholesale.wholesale_products'));

        $tab = $request->input('tab', 'all');

        $query = Product::query()
            ->whereHas('groupPricingRules')
            ->where('is_variation', false)
            ->with(['groupPricingRules.customerGroup']);

        if (is_plugin_active('marketplace')) {
            $query->with('store');
            if ($tab === 'inhouse') {
                $query->where(function ($q): void {
                    $q->whereNull('store_id')->orWhere('store_id', 0);
                });
            } elseif ($tab === 'seller') {
                $query->where('store_id', '>', 0);
            }
        }

        $products = $query->latest()->paginate(20);

        $inhouseCount = 0;
        $sellerCount = 0;
        if (is_plugin_active('marketplace')) {
            $baseQuery = Product::query()
                ->whereHas('groupPricingRules')
                ->where('is_variation', false);
            $inhouseCount = (clone $baseQuery)->where(function ($q): void {
                $q->whereNull('store_id')->orWhere('store_id', 0);
            })->count();
            $sellerCount = (clone $baseQuery)->where('store_id', '>', 0)->count();
        }

        return view('plugins/ecommerce-wholesale::admin.wholesale-products', compact(
            'products',
            'tab',
            'inhouseCount',
            'sellerCount'
        ));
    }
}
