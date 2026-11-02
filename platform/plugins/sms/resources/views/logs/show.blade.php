@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .sms-log-entry { min-width: 0; overflow: hidden; }
        .sms-log-context { max-height: 420px; overflow: auto; white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; font-size: 12px; line-height: 1.5; }
    </style>

    <x-core::card>
        <x-core::card.header>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 w-100">
                <div class="min-w-0">
                    <h3 class="card-title text-break mb-1">{{ $file }}</h3>
                    <div class="text-muted small">
                        {{ $fileSize }} · {{ $fileModified->format('d M Y, h:i:s A') }} ·
                        {{ trans('plugins/sms::sms.logs.entries', ['count' => count($entries)]) }}
                    </div>
                </div>
                <a href="{{ route('sms.logs.index') }}" class="btn btn-outline-primary">
                    <x-core::icon name="ti ti-arrow-left" class="me-1" />{{ trans('plugins/sms::sms.logs.back') }}
                </a>
            </div>
        </x-core::card.header>

        <div class="card-body border-bottom bg-light">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <input type="search" id="sms-log-search" class="form-control" placeholder="{{ trans('plugins/sms::sms.logs.search') }}" aria-label="{{ trans('plugins/sms::sms.logs.search') }}">
                </div>
                <div class="col-md-5">
                    <select id="sms-log-level" class="form-select" aria-label="{{ trans('plugins/sms::sms.logs.level') }}">
                        <option value="">{{ trans('plugins/sms::sms.logs.all_levels') }}</option>
                        @foreach (['ERROR', 'WARNING', 'INFO', 'DEBUG'] as $level)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if ($truncated)
                <div class="text-muted small mt-2">{{ trans('plugins/sms::sms.logs.recent_only') }}</div>
            @endif
        </div>

        <div class="card-body" id="sms-log-entries">
            @forelse ($entries as $entry)
                @php
                    $badge = match ($entry['level']) {
                        'ERROR' => 'bg-danger text-white',
                        'WARNING', 'WARN' => 'bg-warning text-dark',
                        'INFO' => 'bg-azure text-white',
                        default => 'bg-secondary text-white',
                    };
                @endphp
                <div class="card sms-log-entry mb-3" data-level="{{ $entry['level'] }}">
                    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge {{ $badge }}">{{ $entry['level'] }}</span>
                        <span class="text-muted font-monospace small">{{ $entry['date'] }}</span>
                        <strong class="text-break">{{ $entry['message'] }}</strong>
                    </div>
                    @if ($entry['context'] !== '')
                        <div class="card-body p-0">
                            <pre class="sms-log-context m-0 p-3 bg-dark text-light"><code>{{ $entry['context'] }}</code></pre>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-5">{{ trans('plugins/sms::sms.logs.no_entries') }}</div>
            @endforelse
        </div>
    </x-core::card>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('sms-log-search');
            const level = document.getElementById('sms-log-level');
            const cards = document.querySelectorAll('#sms-log-entries .sms-log-entry');
            const filter = () => {
                const text = search.value.trim().toLowerCase();
                cards.forEach(card => {
                    card.hidden = Boolean((level.value && card.dataset.level !== level.value) || (text && !card.textContent.toLowerCase().includes(text)));
                });
            };
            search.addEventListener('input', filter);
            level.addEventListener('change', filter);
        });
    </script>
@endsection
