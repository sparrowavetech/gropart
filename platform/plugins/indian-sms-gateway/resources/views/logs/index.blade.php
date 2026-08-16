@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')
@if(!empty($databaseError))
<div class="alert alert-danger mt-3">
    <strong>Database setup is incomplete.</strong>
    Indian SMS attempted an automatic repair. Run
    <code>php artisan migrate --force</code> and
    <code>php artisan optimize:clear</code>, then reload.
</div>
@endif

<div class="card mb-3"><div class="card-body"><form class="row g-2"><div class="col-md-3"><input name="phone" class="form-control" placeholder="Search phone" value="{{ request('phone') }}"></div><div class="col-md-2"><select name="gateway" class="form-select"><option value="">All gateways</option>@foreach(['msg91'=>'MSG91','fast2sms'=>'Fast2SMS','twofactor'=>'2Factor','smscountry'=>'SMSCountry','kaleyra'=>'Kaleyra','generic'=>'Custom India HTTP'] as $k=>$v)<option value="{{ $k }}" @selected(request('gateway')===$k)>{{ $v }}</option>@endforeach</select></div><div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['processing','accepted','sent','delivered','failed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></div><div class="col-auto"><button class="btn btn-primary">Filter</button><a href="{{ route('india-sms.logs.index') }}" class="btn btn-outline-secondary">Reset</a></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>ID</th><th>Recipient</th><th>Gateway</th><th>Message</th><th>Segments</th><th>Status</th><th>Sent</th><th></th></tr></thead><tbody>@forelse($logs as $log)<tr><td>#{{ $log->id }}</td><td>{{ $log->recipient }}</td><td>{{ $log->gateway }}</td><td class="text-truncate" style="max-width:260px">{{ $log->message }}</td><td>{{ $log->segments }}</td><td><span class="badge {{ in_array($log->status,['accepted','sent','delivered']) ? 'bg-success-lt' : ($log->status==='failed' ? 'bg-danger-lt' : 'bg-secondary-lt') }}">{{ ucfirst($log->status) }}</span></td><td class="text-secondary">{{ $log->created_at?->format('d M Y, h:i A') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('india-sms.logs.show',$log) }}">View</a></td></tr>@empty<tr><td colspan="8" class="text-center text-secondary py-5">No delivery logs found.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $logs->links() }}</div></div>
@endsection
