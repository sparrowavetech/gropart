<?php

namespace Botble\Ecommerce\Supports;

use Cart;
use Html;
use RvMedia;
use Exception;
use Throwable;
use Validator;
use BaseHelper;
use EmailHandler;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Botble\Sms\Enums\SmsEnum;
use Illuminate\Http\Response;
use Botble\Base\Models\BaseModel;
use Illuminate\Support\Collection;
use Botble\Ecommerce\Models\Order;
use Botble\Sms\Supports\SmsHandler;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Models\Option;
use Illuminate\Support\Facades\Log;
use Botble\Ecommerce\Models\Enquiry;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\Auth;
use Botble\Ecommerce\Models\Shipment;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Illuminate\Database\Eloquent\Model;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\OrderHistory;
use InvoiceHelper as InvoiceHelperFacade;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Ecommerce\Models\ShipmentHistory;
use EcommerceHelper as EcommerceHelperFacade;
use Psr\Container\NotFoundExceptionInterface;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Psr\Container\ContainerExceptionInterface;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Botble\Ecommerce\Repositories\Interfaces\OrderInterface;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Repositories\Interfaces\AddressInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Repositories\Interfaces\OrderAddressInterface;
use Botble\Ecommerce\Repositories\Interfaces\OrderHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\OrderProductInterface;
use Botble\Ecommerce\Repositories\Interfaces\ShippingRuleInterface;

class OrderHelper
{
    public function processOrder(string|array|null $orderIds, ?string $chargeId = null): bool|Collection|array|Model
    {
        $orderIds = (array)$orderIds;

        $orders = app(OrderInterface::class)->allBy([['id', 'IN', $orderIds]]);

        if (! $orders->count()) {
            return false;
        }

        foreach ($orders as $order) {
            if ($order->histories()->where('action', 'create_order')->count()) {
                return false;
            }
        }

        if ($chargeId) {
            $payments = app(PaymentInterface::class)->allBy([
                ['charge_id', '=', $chargeId],
                ['order_id', 'IN', $orderIds],
            ]);

            if ($payments) {
                foreach ($orders as $order) {
                    $payment = $payments->firstWhere('order_id', $order->id);
                    if ($payment) {
                        $order->payment_id = $payment->id;
                        $order->save();
                    }
                }
            }
        }

        Cart::instance('cart')->destroy();
        session()->forget('applied_coupon_code');

        session(['order_id' => Arr::first($orderIds)]);

        if (is_plugin_active('marketplace')) {
            apply_filters(SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA, $orders);
        } else {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('admin_new_order')) {
                $this->setEmailVariables($orders->first());
                $mailer->sendUsingTemplate('admin_new_order', get_admin_email()->toArray());
            }

            // Temporarily only send emails with the first order
            $this->sendOrderConfirmationEmail($orders->first(), true);
        }

        session(['order_id' => $orders->first()->id]);

        foreach ($orders as $order) {
            app(OrderHistoryInterface::class)->createOrUpdate([
                'action' => 'create_order',
                'description' => trans('plugins/ecommerce::order.new_order_from', [
                    'order_id' => $order->code,
                    'customer' => BaseHelper::clean($order->user->name ?: $order->address->name),
                ]),
                'order_id' => $order->id,
            ]);
        }

        foreach ($orders as $order) {
            foreach ($order->products as $orderProduct) {
                $product = $orderProduct->product->original_product;

                $flashSale = $product->latestFlashSales()->first();
                if (! $flashSale) {
                    continue;
                }

                $flashSale->products()->detach([$product->id]);
                $flashSale->products()->attach([
                    $product->id => [
                        'price' => $flashSale->pivot->price,
                        'quantity' => (int)$flashSale->pivot->quantity,
                        'sold' => (int)$flashSale->pivot->sold + $orderProduct->qty,
                    ],
                ]);
            }
        }

        return $orders;
    }

    public function setEmailVariables(Order $order): EmailHandlerSupport
    {
        $paymentMethod = $order->payment->payment_channel->label();

        if ($order->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER && $order->payment->status == PaymentStatusEnum::PENDING) {
            $paymentMethod .= '<div>' . trans('plugins/ecommerce::order.payment_info') . ': <strong>' .
                BaseHelper::clean(get_payment_setting('description', $order->payment->payment_channel)) .
                '</strong</div>';
        }

        return EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'store_address' => get_ecommerce_setting('store_address'),
                'store_phone' => get_ecommerce_setting('store_phone'),
                'order_id' => $order->code,
                'order_token' => $order->token,
                'order_note' => $order->description,
                'customer_name' => BaseHelper::clean($order->user->name ?: $order->address->name),
                'customer_email' => $order->user->email ?: $order->address->email,
                'customer_phone' => $order->user->phone ?: $order->address->phone,
                'customer_address' => $order->full_address,
                'product_list' => view('plugins/ecommerce::emails.partials.order-detail', compact('order'))
                    ->render(),
                'shipping_method' => $order->shipping_method_name,
                'payment_method' => $paymentMethod,
                'order_delivery_notes' => view(
                    'plugins/ecommerce::emails.partials.order-delivery-notes',
                    compact('order')
                )
                    ->render(),
            ]);
    }

    public function sendOrderConfirmationEmail(Order $order, bool $saveHistory = false): bool
    {
        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_new_order')) {
                $this->setEmailVariables($order);

                EmailHandler::send(
                    $mailer->getTemplateContent('customer_new_order'),
                    $mailer->getTemplateSubject('customer_new_order'),
                    $order->user->email ?: $order->address->email
                );

                if ($saveHistory) {
                    app(OrderHistoryInterface::class)->createOrUpdate([
                        'action' => 'send_order_confirmation_email',
                        'description' => trans('plugins/ecommerce::order.confirmation_email_was_sent_to_customer'),
                        'order_id' => $order->id,
                    ]);
                }
            }
            if (is_plugin_active('sms')) {
                $sms = new  SmsHandler;
                $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                if ($sms->templateEnabled(SmsEnum::ORDER_CONFIRMATION())) {
                    self::setSmsVariables($order, $sms);
                    $sms->sendUsingTemplate(
                        SmsEnum::ORDER_CONFIRMATION(),
                        $order->user->phone ?: $order->address->phone
                    );
                }
            }

            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return false;
    }

    /**
     * @deprecated
     */
    public function makeInvoicePDF(Order $order): PDFHelper
    {
        return InvoiceHelperFacade::makeInvoicePDF($order);
    }

    /**
     * @deprecated
     */
    public function generateInvoice(Order $order): string
    {
        return InvoiceHelperFacade::generateInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function downloadInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::downloadInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function streamInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::streamInvoice($order->invoice);
    }

    public function getShippingMethod(string $method, array|string|null $option = null): array|string|null
    {
        $name = null;

        if ($method == ShippingMethodEnum::DEFAULT) {
            if ($option) {
                $rule = app(ShippingRuleInterface::class)->findById($option);
                if ($rule) {
                    $name = $rule->name;
                }
            }

            if (empty($name)) {
                $name = trans('plugins/ecommerce::order.default');
            }
        }

        if (! $name && ShippingMethodEnum::search($method)) {
            $name = ShippingMethodEnum::getLabel($method);
        }

        return $name ?: $method;
    }

    public function processHistoryVariables(OrderHistory|ShipmentHistory $history): ?string
    {
        if (empty($history)) {
            return null;
        }

        $variables = [
            'order_id' => Html::link(
                route('orders.edit', $history->order->id),
                $history->order->code . ' <i class="fa fa-external-link-alt"></i>',
                ['target' => '_blank'],
                null,
                false
            )
                ->toHtml(),
            'user_name' => $history->user_id === 0 ? trans('plugins/ecommerce::order.system') :
                BaseHelper::clean($history->user ? $history->user->name : (
                    $history->order->user->name ?:
                        $history->order->address->name
                )),
        ];

        $content = $history->description;

        foreach ($variables as $key => $value) {
            $content = str_replace('% ' . $key . ' %', $value, $content);
            $content = str_replace('%' . $key . '%', $value, $content);
            $content = str_replace('% ' . $key . '%', $value, $content);
            $content = str_replace('%' . $key . ' %', $value, $content);
        }

        return $content;
    }

    public function setOrderSessionData(?string $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_replace_recursive($this->getOrderSessionData($token), $data);

        $data = $this->cleanData($data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $data;
    }

    public function getOrderSessionToken(): string
    {
        if (session()->has('tracked_start_checkout')) {
            $token = session()->get('tracked_start_checkout');
        } else {
            $token = md5(Str::random(40));
            session(['tracked_start_checkout' => $token]);
        }

        return $token;
    }

    public function getOrderSessionData(?string $token = null): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = [];
        $sessionKey = md5('checkout_address_information_' . $token);
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
        }

        return $this->cleanData($data);
    }

    protected function cleanData(array $data): array
    {
        foreach ($data as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            $data[$key] = BaseHelper::clean($item);
        }

        return $data;
    }

    public function mergeOrderSessionData(?string $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_merge($this->getOrderSessionData($token), $data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $this->cleanData($data);
    }

    public function clearSessions(?string $token): void
    {
        Cart::instance('cart')->destroy();
        session()->forget('applied_coupon_code');
        session()->forget('order_id');
        session()->forget(md5('checkout_address_information_' . $token));
        session()->forget('tracked_start_checkout');
    }

    public function handleAddCart(Product $product, Request $request): array
    {
        $parentProduct = $product->original_product;

        $image = $product->image ?: $parentProduct->image;
        $options = [];
        if ($request->input('options')) {
            $options = $this->getProductOptionData($request->input('options'));
        }

        /**
         * Add cart to session
         */
        Cart::instance('cart')->add(
            $product->id,
            BaseHelper::clean($parentProduct->name ?: $product->name),
            $request->input('qty', 1),
            $product->original_price,
            [
                'image' => RvMedia::getImageUrl($image, 'thumb', false, RvMedia::getDefaultImage()),
                'attributes' => $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $parentProduct->total_taxes_percentage,
                'options' => $options,
                'extras' => $request->input('extras', []),
            ]
        );

        /**
         * prepare data for response
         */
        $cartItems = [];

        foreach (Cart::instance('cart')->content() as $item) {
            $cartItems[] = $item;
        }

        return $cartItems;
    }

    protected function getProductOptionData(array $data): array
    {
        $result = [];
        if (! empty($data)) {
            foreach ($data as $key => $option) {
                if (empty($option)) {
                    continue;
                }

                $optionValue = OptionValue::select(['option_value', 'affect_price', 'affect_type'])->where('option_id', $key);
                if ($option['option_type'] != 'field' && isset($option['values'])) {
                    if (is_array($option['values'])) {
                        $optionValue->whereIn('option_value', $option['values']);
                    } else {
                        $optionValue->whereIn('option_value', [0 => $option['values']]);
                    }
                }

                $result['optionCartValue'][$key] = $optionValue->get()->toArray();

                if ($option['option_type'] == 'field' && isset($option['values']) && count($result['optionCartValue']) > 0) {
                    $result['optionCartValue'][$key][0]['option_value'] = $option['values'];
                }
            }
        }

        $result['optionInfo'] = Option::whereIn('id', array_keys($data))->get()->pluck('name', 'id')->toArray();

        return $result;
    }

    public function processAddressOrder(int $currentUserId, array $sessionData, Request $request): array
    {
        $address = null;

        if ($currentUserId && ! Arr::get($sessionData, 'address_id')) {
            $address = app(AddressInterface::class)->getFirstBy([
                'customer_id' => auth('customer')->id(),
                'is_default' => true,
            ]);

            if ($address) {
                $sessionData['address_id'] = $address->id;
            }
        } elseif ($request->input('address.address_id') && $request->input('address.address_id') !== 'new') {
            $address = app(AddressInterface::class)->findById($request->input('address.address_id'));
            if (! empty($address)) {
                $sessionData['address_id'] = $address->id;
            }
        }

        if (Arr::get($sessionData, 'address_id') && Arr::get($sessionData, 'address_id') !== 'new') {
            $address = app(AddressInterface::class)->findById(Arr::get($sessionData, 'address_id'));
        }

        if (! empty($address)) {
            $addressData = [
                'name' => $address->name,
                'phone' => $address->phone,
                'email' => $address->email,
                'country' => $address->country,
                'state' => $address->state,
                'city' => $address->city,
                'address' => $address->address,
                'zip_code' => $address->zip_code,
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        } elseif ((array)$request->input('address', [])) {
            $addressData = array_merge(
                ['order_id' => Arr::get($sessionData, 'created_order_id', 0)],
                (array)$request->input('address', [])
            );
        } else {
            $addressData = [
                'name' => Arr::get($sessionData, 'name'),
                'phone' => Arr::get($sessionData, 'phone'),
                'email' => Arr::get($sessionData, 'email'),
                'country' => Arr::get($sessionData, 'country'),
                'state' => Arr::get($sessionData, 'state'),
                'city' => Arr::get($sessionData, 'city'),
                'address' => Arr::get($sessionData, 'address'),
                'zip_code' => Arr::get($sessionData, 'zip_code'),
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        }

        $addressData = $this->cleanData($addressData);

        if ($addressData && ! empty($addressData['name']) && ! empty($addressData['phone']) && ! empty($addressData['address'])) {
            if (! isset($sessionData['created_order_address'])) {
                $createdOrderAddress = $this->createOrderAddress($addressData);
                if ($createdOrderAddress) {
                    $sessionData['created_order_address'] = true;
                    $sessionData['created_order_address_id'] = $createdOrderAddress->id;
                }
            } elseif (Arr::get($sessionData, 'created_order_address_id')) {
                $createdOrderAddress = $this->createOrderAddress(
                    $addressData,
                    $sessionData['created_order_address_id']
                );
                $sessionData['created_order_address'] = true;
                $sessionData['created_order_address_id'] = $createdOrderAddress->id;
            }
        }

        return $sessionData;
    }

    protected function createOrderAddress(array $data, ?int $orderAddressId = null)
    {
        if ($orderAddressId) {
            return app(OrderAddressInterface::class)->createOrUpdate($data, ['id' => $orderAddressId]);
        }

        $rules = [
            'name' => 'required|max:255',
            'email' => 'email|nullable|max:60',
            'phone' => EcommerceHelperFacade::getPhoneValidationRule(),
            'state' => 'required|max:120',
            'city' => 'required|max:120',
            'address' => 'required|max:120',
        ];

        if (EcommerceHelperFacade::isZipCodeEnabled()) {
            $rules['zip_code'] = 'required|max:20';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return false;
        }

        return app(OrderAddressInterface::class)->create($data);
    }

    public function processOrderProductData(array|Collection $products, array $sessionData): array
    {
        $createdOrderProduct = Arr::get($sessionData, 'created_order_product');

        $cartItems = $products['products']->pluck('cartItem');

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        // Check latest updated at of cart
        if (! $createdOrderProduct || ! $createdOrderProduct->eq($lastUpdatedAt)) {
            $orderProducts = app(OrderProductInterface::class)->allBy(['order_id' => $sessionData['created_order_id']]);
            $productIds = [];
            foreach ($cartItems as $cartItem) {
                $productByCartItem = $products['products']->firstWhere('id', $cartItem->id);
                if(setting('ecommerce_display_product_price_including_taxes') == 1){
                    $price =  $cartItem->price - $cartItem->tax;
                }else{
                    $price = $cartItem->price;
                }
                $data = [
                    'order_id' => $sessionData['created_order_id'],
                    'product_id' => $cartItem->id,
                    'product_name' => $cartItem->name,
                    'product_image' => $productByCartItem->original_product->image,
                    'qty' => $cartItem->qty,
                    'weight' => $productByCartItem->weight * $cartItem->qty,
                    'price' => $price,
                    'tax_amount' => $cartItem->tax,
                    'options' => [],
                    'product_type' => $productByCartItem->product_type,
                ];

                if ($cartItem->options->extras) {
                    $data['options'] = $cartItem->options->extras;
                }

                if (isset($cartItem->options['options'])) {
                    $data['product_options'] = $cartItem->options['options'];
                }

                $orderProduct = $orderProducts->firstWhere('product_id', $cartItem->id);

                if ($orderProduct) {
                    $orderProduct->fill($data);
                    $orderProduct->save();
                } else {
                    app(OrderProductInterface::class)->create($data);
                }

                $productIds[] = $cartItem->id;
            }

            // Delete orderProducts not exists;
            foreach ($orderProducts as $orderProduct) {
                if (! in_array($orderProduct->product_id, $productIds)) {
                    $orderProduct->delete();
                }
            }

            $sessionData['created_order_product'] = $lastUpdatedAt;
        }

        return $sessionData;
    }

    /**
     * @param       $sessionData
     * @param       $request
     * @param       $cartItems
     * @param       $order
     * @param array $generalData
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processOrderInCheckout(
        $sessionData,
        $request,
        $cartItems,
        $order,
        array $generalData
    ): array {
        $createdOrder = Arr::get($sessionData, 'created_order');
        $createdOrderId = Arr::get($sessionData, 'created_order_id');

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        $data = array_merge([
            'amount' => Cart::instance('cart')->rawTotalByItems($cartItems),
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
        ], $generalData);

        if ($createdOrder && $createdOrderId) {
            if ($order && (is_string($createdOrder) || ! $createdOrder->eq($lastUpdatedAt))) {
                $order->fill($data);
            }
        }

        if (! $order) {
            $data = array_merge($data, [
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'is_finished' => false,
            ]);
            $order = app(OrderInterface::class)->createOrUpdate($data);
        }

        $sessionData['created_order'] = $lastUpdatedAt; // insert last updated at in here
        $sessionData['created_order_id'] = $order->id;

        return [$sessionData, $order];
    }

    /**
     * @param Request $request
     * @param int $currentUserId
     * @param string $token
     * @param CartItem[] $cartItems
     *
     * @return mixed
     */
    public function createOrder(Request $request, int $currentUserId, string $token, array $cartItems)
    {
        $request->merge([
            'amount' => Cart::instance('cart')->rawTotalByItems($cartItems),
            'user_id' => $currentUserId,
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'shipping_amount' => 0,
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'is_finished' => false,
            'token' => $token,
        ]);

        return app(OrderInterface::class)->createOrUpdate($request->input());
    }

    public function confirmPayment(Order $order): bool
    {
        $payment = $order->payment;

        if (! $payment) {
            return false;
        }

        $payment->status = PaymentStatusEnum::COMPLETED;
        $payment->amount = $payment->amount ?: 0;
        $payment->user_id = Auth::id();

        app(PaymentInterface::class)->createOrUpdate($payment);

        event(new OrderPaymentConfirmedEvent($order, Auth::user()));

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('order_confirm_payment')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'order_confirm_payment',
                $order->user->email ?: $order->address->email
            );
        }

        if (is_plugin_active('sms')) {
            $sms = new  SmsHandler;
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($sms->templateEnabled(SmsEnum::ORDER_CONFIRMATION())) {
                self::setSmsVariables($order, $sms);
                $sms->sendUsingTemplate(
                    SmsEnum::ORDER_CONFIRMATION(),
                    $order->user->phone ?: $order->address->phone
                );
            }
        }

        app(OrderHistoryInterface::class)->createOrUpdate([
            'action' => 'confirm_payment',
            'description' => trans('plugins/ecommerce::order.payment_was_confirmed_by', [
                'money' => format_price($order->amount),
            ]),
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return true;
    }

    public function cancelOrder(Order $order): Order
    {
        $order->status = OrderStatusEnum::CANCELED;
        $order->is_confirmed = true;
        $order->save();

        event(new OrderCancelledEvent($order));

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;
            $product->quantity += $orderProduct->qty;
            $product->save();

            if ($product->is_variation) {
                $originalProduct = $product->original_product;

                if ($originalProduct->id != $product->id) {
                    $originalProduct->quantity += $orderProduct->qty;
                    $originalProduct->save();
                }
            }

            event(new ProductQuantityUpdatedEvent($product));
        }

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('customer_cancel_order')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'customer_cancel_order',
                $order->user->email ?: $order->address->email
            );
        }
        if (is_plugin_active('sms')) {
            $sms = new  SmsHandler;
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($sms->templateEnabled(SmsEnum::ORDER_CANCELLATION())) {
                self::setSmsVariables($order, $sms);
                $sms->sendUsingTemplate(
                    SmsEnum::ORDER_CANCELLATION(),
                    $order->user->phone ?: $order->address->phone
                );
            }
        }

        return $order;
    }

    public function decreaseProductQuantity(Order $order): bool
    {
        foreach ($order->products as $orderProduct) {
            $product = app(ProductInterface::class)->findById($orderProduct->product_id);

            if ($product) {
                if ($product->with_storehouse_management || $product->quantity >= $orderProduct->qty) {
                    $product->quantity = $product->quantity >= $orderProduct->qty ? $product->quantity - $orderProduct->qty : 0;
                    $product->save();

                    event(new ProductQuantityUpdatedEvent($product));
                }
            }
        }

        return true;
    }
    /**
     * @param Enquiry $order
     *
     * @return Enquiry
     */
    public static function sendEnquiryMail(Enquiry $enquiry): Enquiry
    {
        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('customer_new_enquiry')) {
            self::setEmailVendorVariablesForEnquiry($enquiry);
            $mailer->sendUsingTemplate(
                'customer_new_enquiry',
                $enquiry->email
            );
        }
        if ($mailer->templateEnabled('admin_new_enquiry')) {
            self::setEmailVendorVariablesForEnquiry($enquiry);
            $mailer->sendUsingTemplate('admin_new_enquiry', get_admin_email()->toArray());
        }
        return $enquiry;
    }
     /**
     * @param EnquiryrModel $order
     * @return \Botble\Base\Supports\EmailHandler
     * @throws Throwable
     */
    public static function setEmailVendorVariablesForEnquiry(Enquiry $enquiry): EmailHandlerSupport
    {
        return EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'customer_name'    => $enquiry->name,
                'customer_email'   => $enquiry->email,
                'customer_phone'   => $enquiry->phone,
                'customer_address' => $enquiry->address.', '.$enquiry->cityName->name.', '.$enquiry->stateName->name.', '.$enquiry->zip_code,
                'product_list'     => view('plugins/ecommerce::emails.partials.enquiry-detail', compact('enquiry'))
                    ->render(),
                'enquiry_id'    => $enquiry->code,
                'enquiry_description'      => $enquiry->description,
                'store_name'       => $enquiry->product->store->name,
            ]);
    }

    /**
     * @param Enquiry $order
     *
     * @return Enquiry
     */
    public static function sendEnquirySms(Enquiry $enquiry): Enquiry
    {
        $sms = new  SmsHandler;
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($sms->templateEnabled(SmsEnum::ENQUIRY_CONFIRMATION())) {
            self::setSmsVendorVariablesForEnquiry($enquiry, $sms);
            $sms->sendUsingTemplate(
                SmsEnum::ENQUIRY_CONFIRMATION(),
                $enquiry->phone
            );
        }
        return $enquiry;
    }
      /**
     * @param EnquiryrModel $order
     * @return \Botble\Base\Supports\EmailHandler
     * @throws Throwable
     */
    public static function setSmsVendorVariablesForEnquiry(Enquiry $enquiry, SmsHandler $sms)
    {
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'customer_name'    => $enquiry->name,
                'customer_email'   => $enquiry->email,
                'customer_phone'   => $enquiry->phone,
                'customer_address' => $enquiry->address.', '.$enquiry->cityName->name.', '.$enquiry->stateName->name.', '.$enquiry->zip_code,
                'enquiry_id'    => $enquiry->code,
                'enquiry_description'  => $enquiry->description,
                'store_name'       => $enquiry->product->store->name,
            ]);

        return $sms;
    }

    /**
     * @param Order $order
     *
     * @return SmsHandler
     * @throws Throwable
     */
    public function setSmsVariables(Order $order, SmsHandler $sms)
    {
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'store_address' => get_ecommerce_setting('store_address'),
                'store_phone' => get_ecommerce_setting('store_phone'),
                'order_id' => $order->code,
                'order_token' => $order->token,
                'customer_name' => BaseHelper::clean($order->user->name ?: $order->address->name),
                'customer_email' => $order->user->email ?: $order->address->email,
                'customer_phone' => $order->user->phone ?: $order->address->phone,
                'customer_address' => $order->full_address,
                'shipping_method' => $order->shipping_method_name,
                'payment_method' => $order->payment->payment_channel->label(),
            ]);
    }

    public function shippingStatusDelivered(Shipment $shipment, Request $request, int $userId = 0): Order
    {
        // Update status and time order complete
        $order = app(OrderInterface::class)->createOrUpdate(
            [
                'status' => OrderStatusEnum::COMPLETED,
                'completed_at' => Carbon::now(),
            ],
            ['id' => $shipment->order_id]
        );

        event(new OrderCompletedEvent($order));

        do_action(ACTION_AFTER_ORDER_STATUS_COMPLETED_ECOMMERCE, $order, $request);

        app(OrderHistoryInterface::class)->createOrUpdate([
            'action' => 'update_status',
            'description' => trans('plugins/ecommerce::shipping.order_confirmed_by'),
            'order_id' => $shipment->order_id,
            'user_id' => $userId,
        ]);

        return $order;
    }
}
