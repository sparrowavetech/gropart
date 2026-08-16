@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center w-100">
                    <span class="avatar {{ $diagnostic['ok'] ? 'bg-success-lt' : 'bg-danger-lt' }} me-3">
                        <i class="ti {{ $diagnostic['ok'] ? 'ti-circle-check' : 'ti-alert-triangle' }}"></i>
                    </span>
                    <div class="flex-fill">
                        <h3 class="card-title mb-1">{{ $diagnostic['label'] }}</h3>
                        <div class="text-secondary">{{ $diagnostic['detail'] }}</div>
                    </div>
                    <span class="badge {{ $diagnostic['ok'] ? 'bg-success-lt' : 'bg-danger-lt' }}">
                        {{ $diagnostic['ok'] ? 'Healthy' : 'Needs attention' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert {{ $diagnostic['ok'] ? 'alert-success' : 'alert-warning' }}">
                    <div class="d-flex">
                        <div class="me-2"><i class="ti {{ $diagnostic['ok'] ? 'ti-circle-check' : 'ti-info-circle' }}"></i></div>
                        <div>{{ $diagnostic['summary'] }}</div>
                    </div>
                </div>

                @if (!empty($diagnostic['missing_tables']))
                    <div class="mb-4">
                        <h4>Missing tables</h4>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($diagnostic['missing_tables'] as $table)
                                <code class="px-2 py-1 border rounded">{{ $table }}</code>
                            @endforeach
                        </div>
                    </div>
                @endif

                <h4>How to fix it</h4>
                <ol class="ps-3 mb-4">
                    @foreach ($diagnostic['steps'] as $step)
                        <li class="mb-2">{{ $step }}</li>
                    @endforeach
                </ol>

                @if (!empty($diagnostic['commands']))
                    <h4>Server commands</h4>
                    <div class="mb-4">
                        @foreach ($diagnostic['commands'] as $command)
                            <div class="input-group mb-2">
                                <input class="form-control font-monospace" value="{{ $command }}" readonly>
                                <button class="btn btn-outline-secondary js-copy-command" type="button" data-command="{{ $command }}">
                                    <i class="ti ti-copy me-1"></i>Copy
                                </button>
                            </div>
                        @endforeach
                        <small class="text-secondary">Run commands from the Botble project root. Create a database backup before migrations.</small>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @php
                        $actionUrl = route($diagnostic['action_route']);
                        if (!empty($diagnostic['action_fragment'])) {
                            $actionUrl .= '#' . $diagnostic['action_fragment'];
                        }
                    @endphp
                    <a href="{{ $actionUrl }}" class="btn btn-primary">
                        <i class="ti ti-tool me-1"></i>{{ $diagnostic['action_label'] }}
                    </a>
                    <a href="{{ route('india-sms.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back to overview
                    </a>
                    <a href="{{ request()->fullUrl() }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>Check again
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-copy-command');
    if (!button) return;

    navigator.clipboard.writeText(button.dataset.command || '').then(function () {
        const original = button.innerHTML;
        button.innerHTML = '<i class="ti ti-check me-1"></i>Copied';
        setTimeout(function () { button.innerHTML = original; }, 1500);
    });
});
</script>
@endsection
