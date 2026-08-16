@php
$order = $shipment->order;
$isShipmozo = app(SparroWave\Shipmozo\Shipmozo::class)->isShipmozoOrder($order);
@endphp

@if ($isShipmozo)
@if (app(SparroWave\Shipmozo\Shipmozo::class)->canCreateTransaction($shipment))
@php
$url = route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.show', $shipment->id);
@endphp
<x-core::button
    type="button"
    color="primary"
    class="shipmozo-view-and-create fw-semibold px-3 shadow-sm"
    icon="ti ti-truck-delivery"
    data-bs-toggle="modal"
    data-bs-target="#shipmozo-view-n-create-transaction"
    data-url="{{ $url }}"
    title="Assign the checkout-selected courier and generate the ShipMozo AWB">
    Assign Courier & Generate AWB
    <span class="badge bg-white text-primary ms-2">Action required</span>
</x-core::button>

<div
    class="modal fade"
    id="shipmozo-view-n-create-transaction"
    aria-labelledby="shipmozo-view-n-create-transaction-label"
    aria-hidden="true"
    tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar bg-primary-lt text-primary">
                        <x-core::icon name="ti ti-truck-delivery" />
                    </span>
                    <div>
                        <h5
                            class="modal-title mb-0"
                            id="shipmozo-view-n-create-transaction-label"
                        >Confirm ShipMozo Shipment</h5>
                        <div class="small text-secondary">Courier assignment and AWB generation</div>
                    </div>
                </div>
                <button
                    class="btn-close"
                    data-bs-dismiss="modal"
                    type="button"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4"></div>
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
