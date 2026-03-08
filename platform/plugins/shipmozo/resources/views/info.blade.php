<div class="container">
    <div class="row">
        <div class="col-12 my-3 text-center">
            <div>
                <span class="fs-4 fw-bold">
                    Push Order to ShipMozo
                </span>
                <div>
                    <small class="text-secondary">Are you sure you want to push this order to ShipMozo and generate an AWB?</small>
                </div>
            </div>
        </div>

        <div class="col-12 my-2">
            <div class="row">
                <div class="col-6">
                    <span class="fw-bold fs-5">{{ trans('plugins/ecommerce::shipping.shipping_fee') }}</span>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>{{ trans('plugins/ecommerce::shipping.amount') }}</td>
                                <td>{{ format_price($shipment->price ?: $order->shipping_amount) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('core/base::tables.created_at') }}</td>
                                <td>{{ BaseHelper::formatDateTime($shipment->created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    @if ($order->payment && $order->payment->payment_channel->getValue() == \Botble\Payment\Enums\PaymentMethodEnum::COD)
                    <span class="fw-bold text-danger" style="font-size: 18px">{{ trans('plugins/ecommerce::shipping.cash_on_delivery') }}</span>
                    <table class="table">
                        <tr>
                            <td>{{ trans('plugins/shipmozo::shipmozo.order_amount') }}</td>
                            <td>{{ format_price($order->amount) }}</td>
                        </tr>
                    </table>
                    @endif
                </div>
            </div>
        </div>

        @php
        $url = route(app(\SparroWave\Shipmozo\Shipmozo::class)->getRoutePrefixByFactor() . 'shipmozo.transactions.create', $shipment->id);
        $isShowButton = true;
        if (is_in_admin(true) && Auth::check() && ! Auth::user()->hasPermission('ecommerce.shipments.edit')) {
        $isShowButton = false;
        }
        @endphp

        @if ($isShowButton)
        <div class="col-12 my-4 text-center">
            <button
                class="btn btn-primary create-transaction"
                data-url="{{ $url }}"
                type="button">
                Create AWB & Push Order
            </button>
        </div>
        @endif
    </div>
</div>