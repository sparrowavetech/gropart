@php
    preg_match('/\Ashipmozo_(\d+)/', (string) $order->shipping_option, $courierMatches);
    $courierId = $courierMatches[1] ?? null;
    $shipmozoOrderId = Arr::get($shipment->metadata, 'workflow.push_order.data.order_id');
@endphp

<div>
    @if ($error = Arr::get($shipment->metadata, 'data.error'))
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
            <x-core::icon name="ti ti-alert-triangle" class="flex-shrink-0 mt-1" />
            <div>
                <div class="fw-semibold">Previous attempt needs attention</div>
                <div>{{ $error }}</div>
            </div>
        </div>
    @endif

    <div class="d-flex align-items-start gap-3 mb-4">
        <span class="avatar avatar-lg bg-success-lt text-success flex-shrink-0">
            <x-core::icon name="ti ti-package-export" />
        </span>
        <div>
            <h3 class="mb-1">Ready to create the shipment</h3>
            <p class="text-secondary mb-0">
                ShipMozo will assign the courier selected during checkout and return the AWB and tracking details.
            </p>
        </div>
    </div>

    <div class="border-top border-bottom py-3 mb-4">
        <div class="row g-4">
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">Order</div>
                <div class="fw-semibold">{{ $order->code }}</div>
                @if ($shipmozoOrderId)
                    <div class="small text-secondary">ShipMozo ID: {{ $shipmozoOrderId }}</div>
                @endif
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">Selected courier</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status status-green"></span>
                    <span class="fw-semibold">Courier ID {{ $courierId ? '#' . $courierId : 'Auto assign' }}</span>
                </div>
                <div class="small text-secondary">Saved at checkout</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">Package weight</div>
                <div class="fw-semibold">{{ number_format((float) $shipment->weight, 2) }} {{ ecommerce_weight_unit() }}</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-secondary mb-1">Shipping charge</div>
                <div class="fw-semibold">{{ format_price($shipment->price ?: $order->shipping_amount) }}</div>
                @if (is_plugin_active('payment') && $order->payment && $order->payment->payment_channel->getValue() == \Botble\Payment\Enums\PaymentMethodEnum::COD)
                    <div class="small text-danger">COD order: {{ format_price($order->amount) }}</div>
                @else
                    <div class="small text-secondary">Prepaid order</div>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-start gap-2 text-secondary mb-4">
        <x-core::icon name="ti ti-wallet" class="flex-shrink-0 mt-1" />
        <small>Courier assignment requires sufficient balance in the ShipMozo wallet.</small>
    </div>

    @php
        $url = route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.transactions.create', $shipment->id);
        $isShowButton = ! is_in_admin(true) || ! Auth::check() || Auth::user()->hasPermission('ecommerce.shipments.edit');
    @endphp

    @if ($isShowButton)
        <div class="shipmozo-transaction-actions d-grid d-sm-flex justify-content-sm-between align-items-sm-center gap-2">
            <button
                class="btn btn-secondary get-new-rates"
                data-url="{{ route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.rates', $shipment->id) }}"
                type="button"
            >
                <x-core::icon name="ti ti-refresh" class="me-1" />
                Recheck Rate
            </button>
            <div class="d-grid d-sm-flex gap-2">
                <button class="btn btn-ghost-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                <button
                    class="btn btn-primary btn-lg create-transaction fw-semibold px-4"
                    data-url="{{ $url }}"
                    type="button"
                >
                    <x-core::icon name="ti ti-barcode" class="me-1" />
                    Assign Courier & Generate AWB
                </button>
            </div>
        </div>
        <div class="shipmozo-rates-panel mt-3"></div>
    @endif
</div>
