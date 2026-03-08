@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<div class="card table-responsive">
    <div class="card-header pb-0 border-bottom-0">
        <h4 class="card-title">ShipMozo NDRs</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover mt-3 table-vcenter">
            <thead>
                <tr>
                    <th>AWB Number</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ndrs as $ndr)
                <tr>
                    <td>{{ \Illuminate\Support\Arr::get($ndr, 'awb_number', 'N/A') }}</td>
                    <td>{{ \Illuminate\Support\Arr::get($ndr, 'order_id', 'N/A') }}</td>
                    <td>
                        <span class="badge bg-warning text-warning-fg">
                            {{ \Illuminate\Support\Arr::get($ndr, 'status', 'Pending') }}
                        </span>
                    </td>
                    <td>{{ \Illuminate\Support\Arr::get($ndr, 'reason', 'N/A') }}</td>
                    <td>{{ \Illuminate\Support\Arr::get($ndr, 'date', 'N/A') }}</td>
                    <td class="text-center">
                        <form action="{{ route('shipmozo.ndr.action', \Illuminate\Support\Arr::get($ndr, 'awb_number')) }}" method="POST" class="d-inline" onsubmit="return confirm('Trigger re-attempt for this AWB?')">
                            @csrf
                            <input type="hidden" name="action" value="reattempt">
                            <button class="btn btn-sm btn-primary" type="submit" title="Mark for Re-attempt">Re-attempt</button>
                        </form>

                        <form action="{{ route('shipmozo.ndr.action', \Illuminate\Support\Arr::get($ndr, 'awb_number')) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this as RTO?')">
                            @csrf
                            <input type="hidden" name="action" value="rto">
                            <button class="btn btn-sm btn-danger" type="submit" title="Return to Origin">RTO</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No NDR records found based on ShipMozo synchronisation.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection