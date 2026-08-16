@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')
@if(!empty($databaseError))
<div class="alert alert-danger mt-3"><strong>Database setup is incomplete.</strong> Run <code>php artisan migrate --force</code> and <code>php artisan optimize:clear</code>, then reload.</div>
@endif

<div class="card mb-3">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Template mapping overview</h3>
            <div class="text-secondary">See which approved provider IDs are configured for every message template.</div>
        </div>
        <div class="card-actions"><a href="{{ route('india-sms.templates.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Create template</a></div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Template</th>@foreach($gateways as $gateway)<th class="text-center">{{ $gateway['name'] }}</th>@endforeach<th></th></tr></thead>
            <tbody>
            @forelse($templates as $template)
                @php $ids = is_array($template->gateway_template_ids) ? $template->gateway_template_ids : []; @endphp
                <tr>
                    <td><div class="fw-semibold">{{ $template->name }}</div><code>{{ $template->key }}</code></td>
                    @foreach($gateways as $key => $gateway)
                        <td class="text-center">
                            @if(trim((string)($ids[$key] ?? '')) !== '')
                                <span class="badge bg-success-lt" title="{{ $ids[$key] }}"><i class="ti ti-check me-1"></i>Mapped</span>
                            @else
                                <span class="badge bg-secondary-lt">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('india-sms.templates.edit',$template) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="{{ count($gateways) + 2 }}" class="text-center py-5 text-secondary">No templates found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $templates->links() }}</div>
</div>
@endsection
