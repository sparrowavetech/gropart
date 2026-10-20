<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Ecommerce\Http\Controllers\Fronts\PublicUpdateCheckoutController as BaseCheckoutController;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Services\HandleCheckoutOrderData;
use Botble\Ecommerce\Services\HandleTaxService;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UpdateCheckoutController extends BaseCheckoutController
{
    public function __invoke(Request $request, HandleCheckoutOrderData $handleCheckoutOrderData)
    {
        $sessionCheckoutData = OrderHelper::getOrderSessionData(
            $token = OrderHelper::getOrderSessionToken()
        );

        // Quotes must use this request's destination, even before contact details
        // are complete. An explicitly cleared location must not reuse a stale PIN.
        $location = $request->validate([
            'address.country' => ['nullable', 'string', 'max:120'],
            'address.state' => ['nullable', 'string', 'max:120'],
            'address.city' => ['nullable', 'string', 'max:120'],
            'address.zip_code' => ['nullable', 'string', 'max:20'],
        ])['address'] ?? [];
        $addressId = $request->input('address.address_id');
        if ($addressId && $addressId !== 'new') {
            abort_unless(auth('customer')->check(), 403);
            $address = Address::query()->where('customer_id', auth('customer')->id())->findOrFail($addressId);
            $location = Arr::only($address->toArray(), ['country', 'state', 'city', 'zip_code']);
        }
        if ($request->has('address.address_id')) {
            $sessionCheckoutData['address_id'] = $addressId === 'new' ? null : $addressId;
            $sessionCheckoutData['is_new_address'] = $addressId === 'new';
        }
        $sessionCheckoutData = array_replace($sessionCheckoutData, $location);
        OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

        /**
         * @var Collection $products
         */
        $products = Cart::instance('cart')->products();

        app(HandleTaxService::class)->execute($products, $sessionCheckoutData);

        $checkoutOrderData = $handleCheckoutOrderData->execute(
            $request,
            $products,
            $token,
            $sessionCheckoutData
        );

        add_filter('payment_order_total_amount', function () use ($checkoutOrderData) {
            return $checkoutOrderData->orderAmount - $checkoutOrderData->paymentFee;
        }, 120);

        $hideCODPayment = $this->cartContainsOnlyDigitalProducts($products);

        if ($hideCODPayment) {
            PaymentMethods::excludeMethod(PaymentMethodEnum::COD);
        }

        return $this
            ->httpResponse()
            ->setData([
                'amount' => view('plugins/ecommerce::orders.partials.amount', [
                    'products' => $products,
                    'rawTotal' => $checkoutOrderData->rawTotal,
                    'orderAmount' => $checkoutOrderData->orderAmount,
                    'shipping' => $checkoutOrderData->shipping,
                    'sessionCheckoutData' => $sessionCheckoutData,
                    'shippingAmount' => $checkoutOrderData->shippingAmount,
                    'promotionDiscountAmount' => $checkoutOrderData->promotionDiscountAmount,
                    'couponDiscountAmount' => $checkoutOrderData->couponDiscountAmount,
                    'paymentFee' => $checkoutOrderData->paymentFee,
                    'shippingTaxAmount' => $checkoutOrderData->shippingTaxAmount,
                ])->render(),
                'payment_methods' => view('plugins/ecommerce::orders.partials.payment-methods', [
                    'orderAmount' => $checkoutOrderData->orderAmount,
                ])->render(),
                'shipping_methods' => view('plugins/ecommerce::orders.partials.shipping-methods', [
                    'shipping' => $checkoutOrderData->shipping,
                    'defaultShippingOption' => $checkoutOrderData->defaultShippingOption,
                    'defaultShippingMethod' => $checkoutOrderData->defaultShippingMethod,
                ])->render(),
                'checkout_button' => view('plugins/ecommerce::orders.partials.checkout-button')->render(),
                'checkout_warnings' => apply_filters('ecommerce_checkout_form_before', '', $products),
            ]);
    }

    protected function cartContainsOnlyDigitalProducts(Collection $products): bool
    {
        if (! EcommerceHelper::isEnabledSupportDigitalProducts()) {
            return false;
        }

        if ($products->isEmpty()) {
            return false;
        }

        $digitalProductsCount = EcommerceHelper::countDigitalProducts($products);

        return $digitalProductsCount > 0 && $digitalProductsCount === $products->count();
    }
}
