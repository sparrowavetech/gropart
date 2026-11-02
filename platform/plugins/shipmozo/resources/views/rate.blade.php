<div class="list-group-item">
    {!! Form::input(
        'radio',
        'shipping_option',
        Arr::get($item, 'id'),
        array_merge($attributes, [
            'class' => 'magic-radio',
            'id' => 'shipping-method-shipmozo-' . $index,
        ]),
    ) !!}
    <label for="shipping-method-shipmozo-{{ $index }}">
        <div>
            @if ($image = Arr::get($item, 'image'))
                <img
                    src="{{ $image }}"
                    alt="{{ Arr::get($item, 'name') }}"
                    style="max-height: 40px; max-width: 55px"
                >
            @endif
            @php
                $actualCharge = (float) Arr::get($item, 'actual_charge', Arr::get($item, 'raw_shipping_charges', 0));
                $rawBase = (float) Arr::get($item, 'raw_shipping_charges', 0);
                $rawGst = (float) Arr::get($item, 'gst', 0);
                $isMixed = ! empty($item['is_mixed']);
            @endphp
            <span>
                <strong class="text-dark">{{ Arr::get($item, 'name') }}</strong>
                @if ($actualCharge > 0)
                    &mdash; <span class="fw-bold text-success">{{ format_price($actualCharge) }}</span>
                    <span class="badge bg-success-lt ms-1" style="font-size: 10px;">{{ trans('plugins/shipmozo::shipmozo.wallet_deduction_label') }}</span>
                    @if ($rawBase > 0 && $rawGst > 0)
                        <small class="text-secondary ms-1">({{ trans('plugins/shipmozo::shipmozo.base_charge_with_gst', ['base' => format_price($rawBase), 'gst' => format_price($rawGst)]) }})</small>
                    @endif
                @else
                    &mdash; <span class="fw-bold">{{ format_price($item['price']) }}</span>
                @endif
            </span>
            @if ($isMixed)
                <div class="small text-muted mt-1" style="font-size: 11px;">
                    {{ trans('plugins/shipmozo::shipmozo.customer_price_combined') }}: <strong>{{ format_price($item['price']) }}</strong>
                    (&bull; {{ trans('plugins/shipmozo::shipmozo.shipmozo_parcel') }}: {{ format_price(Arr::get($item, 'customer_shipmozo_price', $item['price'])) }}
                    + {{ trans('plugins/shipmozo::shipmozo.direct_shop_parcel') }}: {{ format_price(Arr::get($item, 'direct_shipping_fee', 0)) }}
                    @if (! empty($item['direct_rule_name']))
                        [{{ Arr::get($item, 'direct_rule_name') }}]
                    @endif)
                </div>
            @else
                <div class="small text-muted mt-1" style="font-size: 11px;">
                    {{ trans('plugins/shipmozo::shipmozo.customer_price_label') }}: {{ format_price($item['price']) }}
                </div>
            @endif
            @if ($item['price'] != $order->shipping_amount && ($deviant = $order->shipping_amount - $item['price']))
                <small class="{{ $deviant > 0 ? 'text-success' : 'text-warning' }}">
                    (<span>{{ $deviant > 0 ? '-' : '+' }}</span><span>{{ format_price($deviant) }}</span>)
                </small>
            @endif
        </div>
        @if ($days = Arr::get($item, 'estimated_delivery'))
            <div>
                <small
                    class="text-secondary">{{ $days }}</small>
            </div>
        @endif
    </label>
</div>
