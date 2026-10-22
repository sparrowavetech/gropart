@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>{{ trans('plugins/shipmozo::shipmozo.ndr.management_title') }}</x-core::card.title>
        </x-core::card.header>
        <x-core::table>
            <x-core::table.header>
                <x-core::table.header.cell>{{ trans('plugins/shipmozo::shipmozo.ndr.awb_number') }}</x-core::table.header.cell>
                <x-core::table.header.cell>{{ trans('plugins/shipmozo::shipmozo.ndr.order_id') }}</x-core::table.header.cell>
                <x-core::table.header.cell>{{ trans('plugins/shipmozo::shipmozo.ndr.status') }}</x-core::table.header.cell>
                <x-core::table.header.cell>{{ trans('plugins/shipmozo::shipmozo.ndr.reason') }}</x-core::table.header.cell>
                <x-core::table.header.cell>{{ trans('plugins/shipmozo::shipmozo.ndr.action') }}</x-core::table.header.cell>
            </x-core::table.header>
            <x-core::table.body>
                @forelse($ndrs as $ndr)
                    @php($awb = \Illuminate\Support\Arr::get($ndr, 'awb_number'))
                    <x-core::table.body.row>
                        <x-core::table.body.cell>{{ $awb ?: 'N/A' }}</x-core::table.body.cell>
                        <x-core::table.body.cell>{{ \Illuminate\Support\Arr::get($ndr, 'order_id', 'N/A') }}</x-core::table.body.cell>
                        <x-core::table.body.cell>{{ \Illuminate\Support\Arr::get($ndr, 'status', 'Pending') }}</x-core::table.body.cell>
                        <x-core::table.body.cell>{{ \Illuminate\Support\Arr::get($ndr, 'reason', 'N/A') }}</x-core::table.body.cell>
                        <x-core::table.body.cell>
                            @if($awb)
                                @if (Auth::user()->hasPermission('shipmozo.ndr.action') || Auth::user()->hasPermission('orders.edit') || Auth::user()->isSuperUser())
                                    <form action="{{ route('shipmozo.ndr.action', $awb) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="reattempt">
                                        <x-core::button type="submit" size="sm">{{ trans('plugins/shipmozo::shipmozo.ndr.reattempt') }}</x-core::button>
                                    </form>
                                    <form action="{{ route('shipmozo.ndr.action', $awb) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="rto">
                                        <x-core::button type="submit" color="danger" size="sm">{{ trans('plugins/shipmozo::shipmozo.ndr.rto') }}</x-core::button>
                                    </form>
                                @endif
                            @endif
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                @empty
                    <x-core::table.body.row>
                        <x-core::table.body.cell colspan="5" class="text-center">{{ trans('plugins/shipmozo::shipmozo.ndr.no_records') }}</x-core::table.body.cell>
                    </x-core::table.body.row>
                @endforelse
            </x-core::table.body>
        </x-core::table>
    </x-core::card>
@endsection
