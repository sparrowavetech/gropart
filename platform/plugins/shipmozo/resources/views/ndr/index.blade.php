@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>ShipMozo NDRs</x-core::card.title>
        </x-core::card.header>
        <x-core::table>
            <x-core::table.header>
                <x-core::table.header.cell>AWB Number</x-core::table.header.cell>
                <x-core::table.header.cell>Order ID</x-core::table.header.cell>
                <x-core::table.header.cell>Status</x-core::table.header.cell>
                <x-core::table.header.cell>Reason</x-core::table.header.cell>
                <x-core::table.header.cell>Action</x-core::table.header.cell>
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
                                <form action="{{ route('shipmozo.ndr.action', $awb) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reattempt">
                                    <x-core::button type="submit" size="sm">Re-attempt</x-core::button>
                                </form>
                                <form action="{{ route('shipmozo.ndr.action', $awb) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="rto">
                                    <x-core::button type="submit" color="danger" size="sm">RTO</x-core::button>
                                </form>
                            @endif
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                @empty
                    <x-core::table.body.row>
                        <x-core::table.body.cell colspan="5" class="text-center">No NDR records found.</x-core::table.body.cell>
                    </x-core::table.body.row>
                @endforelse
            </x-core::table.body>
        </x-core::table>
    </x-core::card>
@endsection
