@php
    preg_match('/\Ashipmozo_(\d+)/', (string) $order->shipping_option, $courierMatches);
    $courierId = $courierMatches[1] ?? null;
    $shipmozoOrderId = Arr::get($shipment->metadata, 'workflow.push_order.data.order_id');

    $isMixedOrder = false;
    $shipmozoCount = 0;
    $nonShipmozoCount = 0;
    if (setting('shipping_shipmozo_scope', 'all') === 'individual') {
        foreach ($order->products as $orderProduct) {
            $prod = $orderProduct->product;
            $isEligible = $prod && (is_callable([$prod, 'isShipmozoShippingEnabled'])
                ? $prod->isShipmozoShippingEnabled()
                : (bool) $prod->enable_shipmozo_shipping);
            if ($isEligible) {
                $shipmozoCount++;
            } else {
                $nonShipmozoCount++;
            }
        }
        $isMixedOrder = ($shipmozoCount > 0 && $nonShipmozoCount > 0);
    }

    $rate = $rate ?? null;
    if (! $rate && isset($order)) {
        try {
            $controller = app(\SparroWave\Shipmozo\Http\Controllers\ShipmozoController::class);
            $rates = $controller->resolveRatesWithNonShipmozo($controller->getRateData($order));
            $rate = Arr::first($rates, fn (array $item): bool => Arr::get($item, 'id') == $order->shipping_option) ?: Arr::first($rates);
        } catch (\Throwable $e) {
            $rate = null;
        }
    }
@endphp

<div>
    @if ($error = Arr::get($shipment->metadata, 'data.error'))
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
            <x-core::icon name="ti ti-alert-triangle" class="flex-shrink-0 mt-1" />
            <div>
                <div class="fw-semibold">{{ trans('plugins/shipmozo::shipmozo.previous_attempt_attention') }}</div>
                <div>{{ $error }}</div>
            </div>
        </div>
    @endif

    @if ($isMixedOrder)
        <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
            <x-core::icon name="ti ti-info-circle" class="flex-shrink-0 mt-1" />
            <div>
                <div class="fw-semibold">{{ trans('plugins/shipmozo::shipmozo.mixed_order_title', ['default' => 'Mixed Fulfillment Order']) }}</div>
                <div class="small">{{ trans('plugins/shipmozo::shipmozo.mixed_order_desc', [
                    'shipmozo_count' => $shipmozoCount,
                    'standard_count' => $nonShipmozoCount,
                    'default' => "This order contains {$shipmozoCount} ShipMozo item(s) and {$nonShipmozoCount} standard delivery item(s). Courier assignment below books shipping only for the ShipMozo parcel. Standard items are fulfilled directly."
                ]) }}</div>
            </div>
        </div>
    @endif

    <div class="d-flex align-items-start gap-3 mb-4">
        <span class="avatar avatar-lg bg-success-lt text-success flex-shrink-0">
            <x-core::icon name="ti ti-package-export" />
        </span>
        <div>
            <h3 class="mb-1">{{ trans('plugins/shipmozo::shipmozo.ready_to_create') }}</h3>
            <p class="text-secondary mb-0">
                {{ trans('plugins/shipmozo::shipmozo.ready_to_create_desc') }}
            </p>
        </div>
    </div>

    <div class="border-top border-bottom py-3 mb-4">
        <div class="row g-4">
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">{{ trans('plugins/shipmozo::shipmozo.order_label') }}</div>
                <div class="fw-semibold">{{ $order->code }}</div>
                @if ($shipmozoOrderId)
                    <div class="small text-secondary">{{ trans('plugins/shipmozo::shipmozo.shipmozo_id') }}: {{ $shipmozoOrderId }}</div>
                @endif
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">{{ trans('plugins/shipmozo::shipmozo.selected_courier') }}</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status status-green"></span>
                    <span class="fw-semibold">{{ trans('plugins/shipmozo::shipmozo.courier_id') }} {{ $courierId ? '#' . $courierId : trans('plugins/shipmozo::shipmozo.auto_assign') }}</span>
                </div>
                <div class="small text-secondary">{{ trans('plugins/shipmozo::shipmozo.saved_at_checkout') }}</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">
                    {{ trans('plugins/shipmozo::shipmozo.package_weight') }}
                    @if ($isMixedOrder)
                        <span class="text-primary">({{ trans('plugins/shipmozo::shipmozo.shipmozo_parcel') }})</span>
                    @endif
                </div>
                <div class="fw-semibold">{{ number_format((float) $shipment->weight, 2) }} {{ ecommerce_weight_unit() }}</div>
                @if ($isMixedOrder)
                    <div class="small text-muted">{{ trans('plugins/shipmozo::shipmozo.shipmozo_item_count', ['count' => $shipmozoCount]) }}</div>
                @endif
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">
                    {{ trans('plugins/shipmozo::shipmozo.actual_shipmozo_cost') }}
                    <span class="badge bg-success-lt ms-1" style="font-size: 10px;">{{ trans('plugins/shipmozo::shipmozo.wallet_deduction_label') }}</span>
                </div>
                @php
                    $actualCharge = isset($rate) ? (float) Arr::get($rate, 'actual_charge', Arr::get($rate, 'raw_shipping_charges', 0)) : 0;
                    $rawCharge = isset($rate) ? (float) Arr::get($rate, 'raw_shipping_charges', 0) : 0;
                    $rawGst = isset($rate) ? (float) Arr::get($rate, 'gst', 0) : 0;
                @endphp
                @if ($actualCharge > 0)
                    <div class="fw-semibold text-success">{{ format_price($actualCharge) }}</div>
                    @if ($rawCharge > 0 && $rawGst > 0)
                        <div class="small text-secondary">
                            {{ trans('plugins/shipmozo::shipmozo.base_charge_with_gst', [
                                'base' => format_price($rawCharge),
                                'gst' => format_price($rawGst),
                            ]) }}
                        </div>
                    @else
                        <div class="small text-secondary">{{ trans('plugins/shipmozo::shipmozo.uninflated_note') }}</div>
                    @endif
                @else
                    <div class="fw-semibold">{{ format_price($shipment->price ?: $order->shipping_amount) }}</div>
                @endif

                <div class="mt-2 pt-1 border-top">
                    <div class="small text-secondary mb-0">
                        {{ trans('plugins/shipmozo::shipmozo.customer_paid_shipping') }}:
                        <span class="fw-semibold text-dark">{{ format_price($order->shipping_amount) }}</span>
                        @if ($isMixedOrder)
                            <span class="text-muted">({{ trans('plugins/shipmozo::shipmozo.combined_fee') }})</span>
                        @endif
                    </div>
                    @if ($isMixedOrder && isset($rate) && ! empty($rate['direct_shipping_fee']))
                        <div class="small text-muted" style="font-size: 11px;">
                            &bull; {{ trans('plugins/shipmozo::shipmozo.customer_shipmozo_fee') }}: {{ format_price(Arr::get($rate, 'customer_shipmozo_price', $order->shipping_amount - $rate['direct_shipping_fee'])) }}
                            | {{ trans('plugins/shipmozo::shipmozo.direct_delivery_fee') }}: {{ format_price($rate['direct_shipping_fee']) }}
                            @if (! empty($rate['direct_rule_name']))
                                [{{ $rate['direct_rule_name'] }}]
                            @endif
                        </div>
                    @endif
                </div>

                @if (is_plugin_active('payment') && $order->payment && $order->payment->payment_channel->getValue() == \Botble\Payment\Enums\PaymentMethodEnum::COD)
                    <div class="small text-danger mt-1">{{ trans('plugins/shipmozo::shipmozo.cod_order') }}: {{ format_price($order->amount) }}</div>
                @else
                    <div class="small text-secondary mt-1">{{ trans('plugins/shipmozo::shipmozo.prepaid_order') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-start gap-2 text-secondary mb-4">
        <x-core::icon name="ti ti-wallet" class="flex-shrink-0 mt-1" />
        <small>{{ trans('plugins/shipmozo::shipmozo.wallet_balance_notice') }}</small>
    </div>

    @php
        $url = route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.transactions.create', $shipment->id);
        $isShowButton = ! is_in_admin(true) || ! Auth::check() || Auth::user()->hasPermission('shipmozo.shipments.create') || Auth::user()->hasPermission('ecommerce.shipments.edit');
    @endphp

    @if ($isShowButton)
        <div class="shipmozo-transaction-actions d-grid d-sm-flex justify-content-sm-between align-items-sm-center gap-2">
            <button
                class="btn btn-secondary get-new-rates"
                data-url="{{ route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.rates', $shipment->id) }}"
                type="button"
            >
                <x-core::icon name="ti ti-refresh" class="me-1" />
                {{ trans('plugins/shipmozo::shipmozo.recheck_rate') }}
            </button>
            <div class="d-grid d-sm-flex gap-2">
                <button class="btn btn-ghost-secondary" data-bs-dismiss="modal" type="button">{{ trans('plugins/shipmozo::shipmozo.cancel') }}</button>
                <button
                    class="btn btn-primary btn-lg create-transaction fw-semibold px-4"
                    data-url="{{ $url }}"
                    type="button"
                >
                    <x-core::icon name="ti ti-barcode" class="me-1" />
                    {{ trans('plugins/shipmozo::shipmozo.assign_courier_btn') }}
                </button>
            </div>
        </div>
        <div class="shipmozo-rates-panel mt-3"></div>
    @endif
</div>
