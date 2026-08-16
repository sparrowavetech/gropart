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
            <span>
                {{ Arr::get($item, 'name') }} -
                {{ format_price($item['price']) }}
            </span>
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
