@if ($breakdown)
    <x-core::table.body.row>
        <x-core::table.body.cell>
            <span class="small fw-semibold">{{ trans('plugins/shipmozo::shipmozo.mixed_fulfillment') }}</span>
            <span class="small d-block text-muted">{{ trans('plugins/shipmozo::shipmozo.shipping_breakdown_included') }}</span>
            <span class="small d-block text-secondary">{{ trans('plugins/shipmozo::shipmozo.shipmozo_parcel') }}: {{ format_price($breakdown['courier']) }}
                @if ($breakdown['shipmozo_count'])
                    ({{ $breakdown['shipmozo_weight'] }} {{ $breakdown['weight_unit'] }}, {{ trans('plugins/shipmozo::shipmozo.item_count_label', ['count' => $breakdown['shipmozo_count']]) }})
                @endif
            </span>
            <span class="small d-block text-secondary">{{ trans('plugins/shipmozo::shipmozo.direct_shop_parcel') }}: {{ format_price($breakdown['direct']) }}
                @if ($breakdown['direct_count'])
                    ({{ $breakdown['direct_weight'] }} {{ $breakdown['weight_unit'] }}, {{ trans('plugins/shipmozo::shipmozo.item_count_label', ['count' => $breakdown['direct_count']]) }})
                @endif
            </span>
            @if ($breakdown['direct_rule_name'])
                <span class="small d-block text-secondary">{{ trans('plugins/shipmozo::shipmozo.rule_label') }}: {{ $breakdown['direct_rule_name'] }}</span>
            @endif
        </x-core::table.body.cell>
        <x-core::table.body.cell></x-core::table.body.cell>
    </x-core::table.body.row>
@endif
