@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@include('plugins/india-sms-gateway::partials.nav')
@if(!empty($databaseError))
<div class="alert alert-danger mt-3">
    <strong>Database setup is incomplete.</strong>
    Indian SMS attempted an automatic repair. Run
    <code>php artisan migrate --force</code> and
    <code>php artisan optimize:clear</code>, then reload.
</div>
@endif

<div class="alert alert-info"><i class="ti ti-info-circle me-1"></i>OTP codes are hashed and never shown in the admin panel. Phone numbers are encrypted at rest and masked here.</div>
<div class="card mb-3"><div class="card-body"><form class="row g-2"><div class="col-md-3"><select name="purpose" class="form-select"><option value="">All purposes</option>@foreach(['otp','registration','login','password_reset','checkout'] as $p)<option value="{{ $p }}" @selected(request('purpose')===$p)>{{ ucfirst(str_replace('_',' ',$p)) }}</option>@endforeach</select></div><div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['pending','verified','expired','blocked','replaced','send_failed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div><div class="col-auto"><button class="btn btn-primary">Filter</button></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Phone</th><th>Purpose</th><th>Status</th><th>Attempts</th><th>Expires</th><th>Created</th></tr></thead><tbody>@forelse($otps as $otp)<tr><td>{{ $otp->masked_phone }}</td><td>{{ ucfirst(str_replace('_',' ',$otp->purpose)) }}</td><td><span class="badge {{ $otp->status==='verified' ? 'bg-success-lt' : ($otp->status==='pending' ? 'bg-blue-lt' : 'bg-secondary-lt') }}">{{ ucfirst(str_replace('_',' ',$otp->status)) }}</span></td><td>{{ $otp->attempts }} / {{ $otp->max_attempts }}</td><td>{{ $otp->expires_at?->diffForHumans() }}</td><td>{{ $otp->created_at?->format('d M Y, h:i A') }}</td></tr>@empty<tr><td colspan="6" class="text-center text-secondary py-5">No OTP activity yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $otps->links() }}</div></div>
@endsection
