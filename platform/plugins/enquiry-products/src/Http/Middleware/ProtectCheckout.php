<?php
namespace SparroWave\EnquiryProducts\Http\Middleware;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SparroWave\EnquiryProducts\Support\Catalog;

class ProtectCheckout
{
    public function handle(Request $request, Closure $next)
    {
        $name = $request->route()?->getName() ?? '';
        if ($request->route()?->getActionName() === 'Botble\\Ecommerce\\Http\\Controllers\\API\\CartController@store') {
            $id = $request->input('product_id');
            $product = is_scalar($id) ? Product::withoutGlobalScope(Catalog::SCOPE)->find($id) : null;
            if ($product && app(Catalog::class)->isEnquiry($product)) {
                throw ValidationException::withMessages(['product_id' => __('plugins/enquiry-products::messages.this_equipment_is_enquiry_only_please_send_an_enquiry_instead')]);
            }
        }
        if (str_starts_with($name, 'public.checkout.') && ! str_contains($name, 'success')) {
            foreach (Cart::instance('cart')->content() as $item) {
                $product = Product::withoutGlobalScope(Catalog::SCOPE)->find($item->id);
                if ($product && app(Catalog::class)->isEnquiry($product)) {
                    if ($request->isMethod('GET') && ! $request->expectsJson()) {
                        return redirect()->route('public.cart')->withErrors(['cart' => __('plugins/enquiry-products::messages.remove_enquiry_only_equipment_from_your_cart_before_checking_out')]);
                    }
                    throw ValidationException::withMessages(['cart' => __('plugins/enquiry-products::messages.remove_enquiry_only_equipment_from_your_cart_before_checking_out')]);
                }
            }
        }
        return $next($request);
    }
}
