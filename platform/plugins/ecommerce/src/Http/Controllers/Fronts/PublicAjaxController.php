<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Http\Controllers\BaseController;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PublicAjaxController extends BaseController
{
    public function ajaxSearchProducts(Request $request, GetProductService $productService)
    {

        $request->merge(['num' => 12]);

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $products = $productService->getProduct($request, null, null, $with,[],['is_enquiry'=>0]);

        $queries = $request->input();

        foreach ($queries as $key => $query) {
            if (! $query || $key == 'num' || (is_array($query) && ! Arr::get($query, 0))) {
                unset($queries[$key]);
            }
        }

        $total = $products->count();

        return $this
            ->httpResponse()
            ->setData(view(EcommerceHelper::viewPath('includes.ajax-search-results'), compact('products', 'queries'))->render())
            ->setMessage($total != 1 ? __(':total Products found', compact('total')) : __(':total Product found', compact('total')));
    }

    public function ajaxGetCategoriesDropdown()
    {
        $categoriesDropdownView = Theme::getThemeNamespace('partials.product-categories-dropdown');

        return $this
            ->httpResponse()
            ->setData([
                'select' => ProductCategoryHelper::renderProductCategoriesSelect(),
                'dropdown' => view()->exists($categoriesDropdownView)
                    ? view($categoriesDropdownView)->render()
                    : null,
            ]);
    }

    public function ajaxCheckPincodeShiprocket(Request $request) {
        $fromPincode = $request->input('from_pincode');
        $toPincode = $request->input('to_pincode');

        $apiEmail = config('plugins.ecommerce.general.shiprocket.email');
        $apiPassword = config('plugins.ecommerce.general.shiprocket.password');

        $loginResponse = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
            'email' => $apiEmail,
            'password' => $apiPassword
        ]);

        if (!$loginResponse->ok()) {
            return response()->json(['serviceable' => false, 'message' => 'Login failed'], 500);
        }

        $token = $loginResponse['token'];

        $checkResponse = Http::withToken($token)->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability/', [
            'pickup_postcode'    => $fromPincode,
            'delivery_postcode'  => $toPincode,
            'cod'                => 0,
            'weight'             => $request->input('product_weight'),
            'declared_value'     => 500,  // Some carriers need this
            'mode'               => 'Surface', // Optional, but more accurate
            'qc_check'           => 1
        ]);

        if (!$checkResponse->ok()) {
            return response()->json(['serviceable' => false, 'message' => 'Unauthorized! You do not have the required permissions.'], 500);
        }

        $data = $checkResponse->json();

        if (isset($data['data']['available_courier_companies']) && count($data['data']['available_courier_companies']) > 0) {
            $etd = $data['data']['available_courier_companies'][0]['etd'] ?? 'N/A';
            return response()->json([
                'serviceable' => true,
                'estimated_delivery_days' => $etd
            ]);
        } else {
            return response()->json(['serviceable' => false, 'message' => 'No courier available']);
        }
    }
}
