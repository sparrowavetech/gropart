@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

@php
    $dashboardStats = [
        [
            'label' => 'Sent today',
            'value' => $stats['today'],
            'class' => 'bg-blue-lt text-blue',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14l11-11"/><path d="M21 3l-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1z"/></svg>',
        ],
        [
            'label' => 'Successful',
            'value' => $stats['sent'],
            'class' => 'bg-green-lt text-green',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2l4-4"/><path d="M12 22a10 10 0 1 0 0-20a10 10 0 0 0 0 20z"/></svg>',
        ],
        [
            'label' => 'Delivered',
            'value' => $stats['delivered'],
            'class' => 'bg-azure-lt text-azure',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 12l3 3l7-7"/><path d="M12 3a9 9 0 1 0 9 9"/></svg>',
        ],
        [
            'label' => 'Failed',
            'value' => $stats['failed'],
            'class' => 'bg-red-lt text-red',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.5l-8 14a2 2 0 0 0 1.7 3h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0z"/></svg>',
        ],
    ];
@endphp

<style>
    .indian-sms-stat-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .indian-sms-stat-icon svg { width: 23px; height: 23px; }
</style>

<div class="row row-cards mb-3">
    @foreach ($dashboardStats as $stat)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="indian-sms-stat-icon {{ $stat['class'] }}">{!! $stat['icon'] !!}</span>
                        <div>
                            <div class="text-secondary">{{ $stat['label'] }}</div>
                            <div class="h2 mb-0">{{ number_format($stat['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row row-cards">
    <div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">System health</h3></div><div class="list-group list-group-flush">
        @foreach ($checks as $check)
            @if ($check['ok'])
                <div class="list-group-item"><div class="d-flex align-items-center"><span class="status-dot status-green me-2"></span><div class="flex-fill"><strong>{{ $check['label'] }}</strong><div class="text-secondary small">{{ $check['detail'] }}</div></div><span class="badge bg-success-lt">Healthy</span></div></div>
            @else
                <a class="list-group-item list-group-item-action text-reset" href="{{ route('india-sms.health.show', $check['key']) }}" title="Open solution"><div class="d-flex align-items-center"><span class="status-dot status-red me-2"></span><div class="flex-fill"><strong>{{ $check['label'] }}</strong><div class="text-secondary small">{{ $check['detail'] }}</div></div><span class="badge bg-danger-lt me-2">Needs attention</span><i class="ti ti-chevron-right text-secondary"></i></div></a>
            @endif
        @endforeach
    </div></div></div>
    <div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Recent delivery activity</h3><div class="card-actions"><a href="{{ route('india-sms.logs.index') }}" class="btn btn-sm btn-outline-primary">View all</a></div></div><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Recipient</th><th>Gateway</th><th>Status</th><th>Time</th></tr></thead><tbody>
        @forelse ($recentLogs as $log)<tr><td>{{ $log->recipient }}</td><td>{{ $gateways[$log->gateway]['name'] ?? $log->gateway }}</td><td><span class="badge {{ in_array($log->status,['sent','accepted','delivered']) ? 'bg-success-lt' : ($log->status==='failed' ? 'bg-danger-lt' : 'bg-secondary-lt') }}">{{ ucfirst($log->status) }}</span></td><td class="text-secondary">{{ $log->created_at?->diffForHumans() }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No SMS activity yet.</td></tr>@endforelse
    </tbody></table></div></div></div>
</div>
@endsection
