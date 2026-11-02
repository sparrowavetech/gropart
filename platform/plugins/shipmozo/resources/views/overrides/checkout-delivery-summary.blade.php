@php
    $shipmozoRates = collect(Arr::get($shipping, SHIPMOZO_SHIPPING_METHOD_NAME, []))
        ->filter(fn ($rate) => Arr::get($rate, 'shipmozo_only') === true && ! Arr::get($rate, 'disabled'));
    $weightRate = $shipmozoRates->first(fn ($rate) => (int) Arr::get($rate, 'package_weight_grams', 0) > 0);
    $times = $shipmozoRates->map(function ($rate) {
        $value = Arr::get($rate, 'estimated_delivery');

        return is_scalar($value) ? trim((string) $value) : '';
    });
    $uniqueTimes = $times->filter()->unique()->values();
    $estimatedDelivery = '';
    if ($uniqueTimes->count() === 1 && $times->every(fn ($time) => $time !== '')) {
        $estimatedDelivery = $uniqueTimes->first();
        if (ctype_digit($estimatedDelivery)) {
            $estimatedDelivery = trans_choice('plugins/shipmozo::shipmozo.delivery_days', (int) $estimatedDelivery, [
                'count' => (int) $estimatedDelivery,
            ]);
        }
    } elseif ($uniqueTimes->isNotEmpty()) {
        $estimatedDelivery = trans('plugins/shipmozo::shipmozo.delivery_time_varies');
    }
@endphp
@if ($weightRate)
    <div class="shipmozo-checkout-summary mb-2" style="display: block !important; margin: 8px 0 12px; padding: 8px 10px; background: #f7f9fb; border: 1px solid #e3e8ee; border-radius: 6px; color: #59636e; font-size: 12px !important; line-height: 1.45;">
        <div style="display: block !important; font-weight: 600;">{{ trans('plugins/shipmozo::shipmozo.package_weight_inline', ['weight' => number_format((int) $weightRate['package_weight_grams'] / 1000, 2)]) }}</div>
        @if ($estimatedDelivery !== '')
            <div style="display: block !important; margin-top: 3px;">{{ trans('plugins/shipmozo::shipmozo.estimated_delivery_time', ['time' => $estimatedDelivery]) }}</div>
        @endif
    </div>
@endif
