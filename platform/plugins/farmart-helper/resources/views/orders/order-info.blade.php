@php
    $isInAdmin = $isInAdmin ?? (is_in_admin(true) || request()->is('admin/*'));
@endphp
<x-core::table :hover="false" :striped="false" class="table-borderless text-end">
    <x-core::table.body>

        <x-core::table.body.row>
            <x-core::table.body.cell>{{ trans('plugins/ecommerce::order.quantity') }}</x-core::table.body.cell>
            <x-core::table.body.cell>
                {{ number_format($order->products->sum('qty')) }}
            </x-core::table.body.cell>
        </x-core::table.body.row>
        <x-core::table.body.row>
            <x-core::table.body.cell>
                {{ trans('plugins/ecommerce::order.sub_amount') }}</x-core::table.body.cell>
            <x-core::table.body.cell>
                {{ format_price($order->sub_total) }}
            </x-core::table.body.cell>
        </x-core::table.body.row>
        {!! apply_filters('ecommerce_admin_order_after_subtotal', null, $order) !!}
        <x-core::table.body.row>
            <x-core::table.body.cell>
                {{ trans('plugins/ecommerce::order.discount') }}
                @if ($order->coupon_code)
                    <p class="mb-0">
                        {!! BaseHelper::clean(trans('plugins/ecommerce::order.coupon_code', [
                            'code' => Html::tag('strong', $order->coupon_code)->toHtml(),
                        ])) !!}
                    </p>
                @elseif ($order->discount_description)
                    <p class="mb-0">{{ $order->discount_description }}</p>
                @endif
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                {{ format_price($order->discount_amount) }}
            </x-core::table.body.cell>
        </x-core::table.body.row>
        @if ($order->shipping_method_name)
            @php
                $isMixedOrder = false;
                $shipmozoWeight = 0;
                $directWeight = 0;
                $shipmozoItemCount = 0;
                $directItemCount = 0;
                $nonShipmozoItemsFormatted = [];

                if (is_plugin_active('shipmozo') && setting('shipping_shipmozo_status') == 1 && setting('shipping_shipmozo_scope', 'all') === 'individual') {
                    foreach ($order->products as $orderProduct) {
                        $prod = $orderProduct->product?->original_product ?: $orderProduct->product;
                        $qty = (int) $orderProduct->qty;
                        $itemWeight = (float) ($prod ? $prod->weight : 0) * $qty;

                        $isEligible = $prod && (is_callable([$prod, 'isShipmozoShippingEnabled'])
                            ? $prod->isShipmozoShippingEnabled()
                            : (bool) $prod->enable_shipmozo_shipping);

                        if ($isEligible) {
                            $shipmozoItemCount += $qty;
                            $shipmozoWeight += $itemWeight;
                        } else {
                            $directItemCount += $qty;
                            $directWeight += $itemWeight;
                            $nonShipmozoItemsFormatted[] = [
                                'id' => $orderProduct->product_id,
                                'product_id' => $orderProduct->product_id,
                                'qty' => $qty,
                                'weight' => (float) ($prod ? $prod->weight : 0),
                                'price' => (float) $orderProduct->price,
                            ];
                        }
                    }
                    $isMixedOrder = ($shipmozoItemCount > 0 && $directItemCount > 0);
                }

                $directFee = 0.0;
                $directRuleName = null;
                $shipmozoCustomerFee = 0.0;
                if ($isMixedOrder && ! empty($nonShipmozoItemsFormatted)) {
                    try {
                        $hookProvider = new \SparroWave\Shipmozo\Providers\HookServiceProvider(app());
                        $address = $order->shippingAddress ?: $order->address;
                        $directDetails = $hookProvider->resolveNonShipmozoShippingDetails([
                            'address_to' => [
                                'zip_code' => $address?->zip_code ?: $address?->zip,
                                'city' => $address?->city,
                                'state' => $address?->state,
                                'country' => $address?->country,
                            ],
                            'origin' => EcommerceHelper::getOriginAddress(),
                            'store_id' => $order->store_id,
                        ], $nonShipmozoItemsFormatted);
                        $directFee = (float) Arr::get($directDetails, 'price', 0.0);
                        $directRuleName = Arr::get($directDetails, 'rule_name');
                        $shipmozoCustomerFee = max(0, (float) $order->shipping_amount - $directFee);
                    } catch (\Throwable $e) {
                        // Safe fallback
                    }
                }
            @endphp
            <x-core::table.body.row>
                <x-core::table.body.cell>
                    <p class="mb-1">{{ trans('plugins/ecommerce::order.shipping_fee') }}</p>
                    <span class="small d-block fw-semibold">{{ $order->shipping_method_name }}</span>
                    @if ($isMixedOrder)
                        <div class="mt-1 small text-start">
                            <span class="badge bg-purple-lt text-purple mb-1" style="font-size: 11px;">
                                <i class="ti ti-packages me-1"></i>{{ trans('plugins/shipmozo::shipmozo.mixed_fulfillment') }}
                            </span>
                            <div class="text-secondary" style="font-size: 12px;">
                                &bull; {{ trans('plugins/shipmozo::shipmozo.shipmozo_parcel') }}: <strong>{{ rtrim(rtrim(number_format($shipmozoWeight, 3, '.', ''), '0'), '.') }} {{ ecommerce_weight_unit() }}</strong> ({{ trans('plugins/shipmozo::shipmozo.item_count_label', ['count' => $shipmozoItemCount]) }})
                                @if ($shipmozoCustomerFee > 0)
                                    &mdash; <span class="fw-semibold text-dark">{{ format_price($shipmozoCustomerFee) }}</span>
                                @endif
                            </div>
                            <div class="text-secondary" style="font-size: 12px;">
                                &bull; {{ trans('plugins/shipmozo::shipmozo.direct_shop_parcel') }}: <strong>{{ rtrim(rtrim(number_format($directWeight, 3, '.', ''), '0'), '.') }} {{ ecommerce_weight_unit() }}</strong> ({{ trans('plugins/shipmozo::shipmozo.item_count_label', ['count' => $directItemCount]) }})
                                @if ($directFee > 0)
                                    &mdash; <span class="fw-semibold text-dark">{{ format_price($directFee) }}</span>
                                @endif
                                @if ($directRuleName)
                                    <span class="badge bg-blue-lt ms-1" style="font-size: 10px;" title="{{ trans('plugins/shipmozo::shipmozo.applied_shipping_rule') }}">
                                        {{ trans('plugins/shipmozo::shipmozo.rule_label') }}: {{ $directRuleName }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size: 11px;">
                                {{ trans('plugins/shipmozo::shipmozo.total_weight') }}: {{ rtrim(rtrim(number_format((float) $weight, 3, '.', ''), '0'), '.') }} {{ ecommerce_weight_unit() }}
                                @if ((float) $order->shipping_amount > 0)
                                    | {{ trans('plugins/shipmozo::shipmozo.combined_total') }}: {{ format_price($order->shipping_amount) }}
                                @endif
                            </div>
                        </div>
                    @else
                        <span class="small d-block">{{ trans('plugins/shipmozo::shipmozo.package_weight') }}: {{ rtrim(rtrim(number_format((float) $weight, 3, '.', ''), '0'), '.') }} {{ ecommerce_weight_unit() }}</span>
                    @endif
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    {{ format_price($order->shipping_amount) }}
                    @if ($isMixedOrder)
                        <span class="small text-muted d-block" style="font-size: 11px;">({{ trans('plugins/shipmozo::shipmozo.combined_fee') }})</span>
                    @endif
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif
        @if (EcommerceHelper::isTaxEnabled())
            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.tax') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    {{ format_price($order->tax_amount) }}
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif
        @if ((float) ($order->shipping_tax_amount ?? 0))
            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.shipping_tax') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    {{ format_price($order->shipping_tax_amount) }}
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif

        @if ((float) $order->payment_fee)
            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/payment::payment.payment_fee') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    {{ format_price($order->payment_fee) }}
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif
        <x-core::table.body.row>
            <x-core::table.body.cell>
                {{ trans('plugins/ecommerce::order.total_amount') }}
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                @if (is_plugin_active('payment') && $order->payment->id)
                    <span @class(['text-warning' => $order->payment->status != Botble\Payment\Enums\PaymentStatusEnum::COMPLETED]) class="text-warning">
                {{ format_price($order->amount) }}
            </span>
                @else
                    {{ format_price($order->amount) }}
                @endif
            </x-core::table.body.cell>
        </x-core::table.body.row>
        @if (is_plugin_active('payment') && $order->payment->id)
            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.paid_amount') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    @if ($isInAdmin && $order->payment->id)
                        <a
                            href="{{ route('payment.show', $order->payment->id) }}"
                            target="_blank"
                        >
                            <span>{{ format_price($order->payment->status == Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->payment->amount : 0) }}</span>
                        </a>
                    @else
                        <span>{{ format_price($order->payment->status == Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->payment->amount : 0) }}</span>
                    @endif
                </x-core::table.body.cell>
            </x-core::table.body.row>

            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.payment_method') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    @if ($isInAdmin && $order->payment->id)
                        <a href="{{ route('payment.show', $order->payment->id) }}" target="_blank">
                            {{ $order->payment->payment_channel->displayName() }}

                            <x-core::icon name="ti ti-external-link" />
                        </a>
                    @else
                        {{ $order->payment->payment_channel->displayName() }}
                    @endif
                </x-core::table.body.cell>
            </x-core::table.body.row>

            <x-core::table.body.row>
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.payment_status_label') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    {!! BaseHelper::clean($order->payment->status->toHtml()) !!}
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif

        @if(!empty($proofDownloadUrl))
            @if ($order->proof_file && Storage::disk('local')->exists($order->proof_file))
                <x-core::table.body.row>
                    <x-core::table.body.cell colspan="2">
                        <div style="margin-top: -1rem !important">
                            @include('plugins/ecommerce::orders.partials.payment-proof-detail', ['downloadUrl' => $proofDownloadUrl])
                        </div>
                    </x-core::table.body.cell>
                </x-core::table.body.row>
            @endif
        @endif

        {!! apply_filters('ecommerce_admin_order_extra_info', null, $order) !!}

        <x-core::table.body.row>
            <td colspan="2">
                <hr class="my-0">
            </td>
        </x-core::table.body.row>

        @if (is_plugin_active('payment') && $order->payment->status == Botble\Payment\Enums\PaymentStatusEnum::REFUNDED)
            <x-core::table.body.row class="hidden">
                <x-core::table.body.cell>
                    {{ trans('plugins/ecommerce::order.refunded_amount') }}
                </x-core::table.body.cell>
                <x-core::table.body.cell>
                    <span>{{ format_price($order->payment->amount) }}</span>
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endif
        <x-core::table.body.row class="hidden">
            <x-core::table.body.cell>
                {{ trans('plugins/ecommerce::order.amount_received') }}
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                {{ format_price(is_plugin_active('payment') && $order->payment->status == Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->amount : 0) }}
            </x-core::table.body.cell>
        </x-core::table.body.row>
    </x-core::table.body>
</x-core::table>
