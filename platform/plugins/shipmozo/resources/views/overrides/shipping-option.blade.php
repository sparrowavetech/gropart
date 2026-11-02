<li class="list-group-item">
    {!! Form::radio(Arr::get($attributes, 'name'), $shippingKey, Arr::get($attributes, 'checked'), $attributes) !!}
    <label for="{{ Arr::get($attributes, 'id') }}" style="display: block !important; width: 100%; min-width: 0;">
        <div style="display: flex; align-items: flex-start; gap: 8px; min-width: 0;">
            @if ($image = Arr::get($shippingItem, 'image'))
                <img
                    src="{{ $image }}"
                    alt="{{ $shippingItem['name'] }}"
                    style="max-height: 40px; max-width: 55px; flex-shrink: 0;"
                    onerror="this.style.display='none'"
                >
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 2px 10px; line-height: 1.35;">
                    <span style="font-size: 13px !important;">{{ $shippingItem['name'] }}</span>
                    @if (Arr::get($shippingItem, 'disabled'))
                        <small class="text-danger">{{ __('Shipping unavailable') }}</small>
                    @elseif ($shippingItem['price'] > 0)
                        <strong style="font-size: 13px !important; font-weight: 700; white-space: nowrap;">{{ format_price($shippingItem['price']) }}</strong>
                    @else
                        <strong style="font-size: 13px !important;">{{ __('Free shipping') }}</strong>
                    @endif
                </div>
                @if ($description = Arr::get($shippingItem, 'description'))
                    <div><small class="text-secondary">{!! BaseHelper::clean($description) !!}</small></div>
                @endif
                @if ($errorMessage = Arr::get($shippingItem, 'error_message'))
                    <div><small class="text-danger">{!! BaseHelper::clean($errorMessage) !!}</small></div>
                @endif
            </div>
        </div>
    </label>
</li>
