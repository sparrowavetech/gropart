@php
$order = $shipment->order;
$method = $order->shipping_method->getValue();
$isShipmozo = ($method === SHIPMOZO_SHIPPING_METHOD_NAME) ||
($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT && is_numeric($order->shipping_option));
@endphp

@if ($isShipmozo)
@if (app(SparroWave\Shipmozo\Shipmozo::class)->canCreateTransaction($shipment))
@php
$url = route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.show', $shipment->id);
@endphp
<x-core::button
    type="button"
    class="shipmozo-view-and-create"
    icon="ti ti-truck-delivery"
    data-bs-toggle="modal"
    data-bs-target="#shipmozo-view-n-create-transaction"
    data-url="{{ $url }}">
    <img
        src="{{ url('vendor/core/plugins/shipmozo/images/icon.svg') }}"
        alt="shipmozo"
        style="height: 14px; width: auto; filter: brightness(0) invert(1);"
        class="me-1">
    {{ trans('plugins/shipmozo::shipmozo.transaction.view_and_create') }}
</x-core::button>

<div
    class="modal fade"
    id="shipmozo-view-n-create-transaction"
    aria-labelledby="shipmozo-view-n-create-transaction-label"
    aria-hidden="true"
    tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5
                    class="modal-title"
                    id="shipmozo-view-n-create-transaction-label">{{ trans('plugins/shipmozo::shipmozo.transaction.view_and_create') }}</h5>
                <button
                    class="btn-close"
                    data-bs-dismiss="modal"
                    type="button"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
@endif

@if ($shipment->label_url)
<x-core::button
    tag="a"
    color="success"
    :href="$shipment->label_url"
    target="_blank"
    icon="ti ti-printer">
    {{ trans('plugins/shipmozo::shipmozo.print_label') }}
</x-core::button>
@endif
@endif