@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .log-show-wrapper {
            max-width: 100%;
            overflow-x: hidden;
        }
        .log-entry-card {
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
        }
        .log-entry-card .card-header {
            min-width: 0;
        }
        .log-code-pre {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            white-space: pre-wrap !important;
            word-break: break-all !important;
            overflow-wrap: anywhere !important;
        }
    </style>

    <div class="row g-3 log-show-wrapper">
        <div class="col-12">
            <x-core::card class="shadow-sm">
                <x-core::card.header>
                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar bg-primary-lt text-primary">
                                    <x-core::icon name="ti ti-file-text" />
                                </span>
                                <div>
                                    <h4 class="card-title text-dark fw-bold mb-0 text-break">{{ $file }}</h4>
                                    <div class="small text-muted">
                                        <span><x-core::icon name="ti ti-database" class="me-1" />{{ $fileSize }}</span>
                                        <span class="mx-2">•</span>
                                        <span><x-core::icon name="ti ti-clock" class="me-1" />{{ $fileModified->format('d M Y, h:i:s A') }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="badge bg-blue-lt">{{ count($entries) }} {{ trans('plugins/shipmozo::shipmozo.logs.entries_count') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ecommerce.shipments.shipmozo.logs.download', $file) }}" class="btn btn-outline-secondary">
                                <x-core::icon name="ti ti-download" class="me-1" />
                                {{ trans('plugins/shipmozo::shipmozo.logs.download_raw') }}
                            </a>
                            <form action="{{ route('ecommerce.shipments.shipmozo.logs.destroy', $file) }}" method="POST" onsubmit="return confirm('{{ trans('plugins/shipmozo::shipmozo.logs.confirm_delete', ['file' => $file]) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <x-core::icon name="ti ti-trash" class="me-1" />
                                    {{ trans('plugins/shipmozo::shipmozo.logs.delete') }}
                                </button>
                            </form>
                            <a href="{{ route('ecommerce.shipments.shipmozo.logs.index') }}" class="btn btn-primary">
                                <x-core::icon name="ti ti-arrow-left" class="me-1" />
                                {{ trans('plugins/shipmozo::shipmozo.logs.back_to_logs') }}
                            </a>
                        </div>
                    </div>
                </x-core::card.header>

                <div class="card-body border-bottom bg-light py-2 px-3">
                    <div class="row align-items-center g-2">
                        <div class="col-md-6">
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <x-core::icon name="ti ti-search" />
                                </span>
                                <input type="text" id="log-search-input" class="form-control" placeholder="{{ trans('plugins/shipmozo::shipmozo.logs.search_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active log-filter-btn" data-level="ALL">
                                    {{ trans('plugins/shipmozo::shipmozo.logs.filter_all') }} ({{ count($entries) }})
                                </button>
                                @php
                                    $errorCount = count(array_filter($entries, fn($e) => ($e['level'] ?? '') === 'ERROR'));
                                    $infoCount = count(array_filter($entries, fn($e) => ($e['level'] ?? '') === 'INFO'));
                                    $warningCount = count(array_filter($entries, fn($e) => in_array($e['level'] ?? '', ['WARNING', 'WARN'])));
                                @endphp
                                @if ($errorCount > 0)
                                    <button type="button" class="btn btn-outline-danger log-filter-btn" data-level="ERROR">
                                        {{ trans('plugins/shipmozo::shipmozo.logs.filter_errors') }} ({{ $errorCount }})
                                    </button>
                                @endif
                                @if ($infoCount > 0)
                                    <button type="button" class="btn btn-outline-info log-filter-btn" data-level="INFO">
                                        {{ trans('plugins/shipmozo::shipmozo.logs.filter_info') }} ({{ $infoCount }})
                                    </button>
                                @endif
                                <button type="button" id="toggle-all-payloads" class="btn btn-outline-secondary">
                                    <x-core::icon name="ti ti-arrows-vertical" class="me-1" />
                                    {{ trans('plugins/shipmozo::shipmozo.logs.toggle_payloads') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3" id="log-entries-container">
                    @forelse ($entries as $index => $entry)
                        @php
                            $level = $entry['level'];
                            $badgeClass = match($level) {
                                'ERROR' => 'bg-danger text-white',
                                'WARNING', 'WARN' => 'bg-warning text-dark',
                                'INFO' => 'bg-azure text-white',
                                'DEBUG' => 'bg-secondary text-white',
                                default => 'bg-primary text-white',
                            };
                            $cardBorder = match($level) {
                                'ERROR' => 'border-danger-subtle',
                                'WARNING', 'WARN' => 'border-warning-subtle',
                                default => 'border-slate-200',
                            };
                        @endphp
                        <div class="card mb-3 log-entry-card shadow-sm {{ $cardBorder }}" 
                             data-level="{{ $level }}" 
                             data-search="{{ strtolower($entry['message'] . ' ' . ($entry['context'] ?? '') . ' ' . $entry['date']->format('Y-m-d H:i:s')) }}">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: rgba(248, 250, 252, 0.8); min-width: 0;">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden" style="min-width: 0;">
                                    <span class="badge {{ $badgeClass }} px-2 py-1 fw-bold font-monospace flex-shrink-0">{{ $level }}</span>
                                    <span class="badge bg-light text-secondary border font-monospace flex-shrink-0">
                                        <x-core::icon name="ti ti-clock" class="me-1" />{{ $entry['date']->format('H:i:s') }}
                                    </span>
                                    <h5 class="mb-0 text-dark fw-bold text-truncate" style="min-width: 0;" title="{{ $entry['message'] }}">
                                        {{ $entry['message'] }}
                                    </h5>
                                </div>
                                @if (!empty($entry['context']))
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary copy-json-btn py-1 px-2" data-json="{{ e($entry['context']) }}">
                                            <x-core::icon name="ti ti-copy" class="me-1" /> {{ trans('plugins/shipmozo::shipmozo.logs.copy_json') }}
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-primary toggle-payload-btn py-1 px-2" data-bs-toggle="collapse" data-bs-target="#payload-{{ $index }}">
                                            <x-core::icon name="ti ti-chevron-down" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if (!empty($entry['context']))
                                <div class="collapse show log-payload-collapse" id="payload-{{ $index }}" style="min-width: 0; max-width: 100%;">
                                    <div class="card-body p-0" style="min-width: 0; max-width: 100%;">
                                        <pre class="m-0 p-3 bg-dark text-light font-monospace rounded-bottom log-code-pre" style="max-height: 450px; overflow: auto; white-space: pre-wrap; word-break: break-all; overflow-wrap: anywhere; font-size: 12px; line-height: 1.5; max-width: 100%;"><code>{{ $entry['context'] }}</code></pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <x-core::icon name="ti ti-inbox" style="font-size: 32px;" class="d-block mx-auto mb-2" />
                            {{ trans('plugins/shipmozo::shipmozo.logs.no_entries') }}
                        </div>
                    @endforelse
                </div>
            </x-core::card>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('log-search-input');
            const cards = document.querySelectorAll('.log-entry-card');
            const filterBtns = document.querySelectorAll('.log-filter-btn');
            let currentLevel = 'ALL';

            function filterCards() {
                const query = (searchInput.value || '').toLowerCase().trim();

                cards.forEach(card => {
                    const cardLevel = card.getAttribute('data-level');
                    const cardText = card.getAttribute('data-search') || '';

                    const matchesLevel = (currentLevel === 'ALL' || cardLevel === currentLevel);
                    const matchesQuery = (!query || cardText.includes(query));

                    if (matchesLevel && matchesQuery) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterCards);
            }

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentLevel = this.getAttribute('data-level');
                    filterCards();
                });
            });

            // Copy JSON functionality
            document.querySelectorAll('.copy-json-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const json = this.getAttribute('data-json');
                    navigator.clipboard.writeText(json).then(() => {
                        const originalHtml = this.innerHTML;
                        this.innerHTML = '<i class="ti ti-check me-1 text-success"></i> Copied!';
                        setTimeout(() => {
                            this.innerHTML = originalHtml;
                        }, 2000);
                    });
                });
            });

            // Toggle all payloads
            const toggleAllBtn = document.getElementById('toggle-all-payloads');
            let payloadsExpanded = true;
            if (toggleAllBtn) {
                toggleAllBtn.addEventListener('click', function () {
                    payloadsExpanded = !payloadsExpanded;
                    document.querySelectorAll('.log-payload-collapse').forEach(collapseEl => {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        if (payloadsExpanded) {
                            bsCollapse.show();
                        } else {
                            bsCollapse.hide();
                        }
                    });
                });
            }
        });
    </script>
@endsection
