@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {{-- Diagnostic & Live Sync Card --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-md bg-primary-subtle text-primary rounded">
                        <i class="ti ti-credit-card fs-3"></i>
                    </span>
                    <div>
                        <h4 class="card-title mb-0 fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.diagnostics_title') }}</h4>
                        <small class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.diagnostics_desc') }}</small>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2" id="btn-sync-razorpay-offers" data-url="{{ route('razorpay-easy-emi.settings.sync-offers') }}">
                        <i class="ti ti-refresh" id="sync-icon"></i>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.actions.sync_offers') }}</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.razorpay_gateway') }}</span>
                            @if ($diagnostics['razorpay_plugin_active'] && $diagnostics['razorpay_payment_active'])
                                <span class="badge bg-success text-success-fg">
                                    <i class="ti ti-check me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.connected') }}
                                </span>
                            @else
                                <span class="badge bg-danger text-danger-fg">
                                    <i class="ti ti-alert-triangle me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.disconnected') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.api_key_status') }}</span>
                            @if (! empty($diagnostics['key_id']))
                                <span class="badge bg-success text-success-fg">
                                    <i class="ti ti-key me-1"></i> {{ $diagnostics['key_id'] }} ({{ ucfirst($diagnostics['key_source']) }})
                                </span>
                            @else
                                <span class="badge bg-warning text-warning-fg">
                                    <i class="ti ti-alert-circle me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.missing_key') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.cached_offers') }}</span>
                            <span class="badge bg-info text-info-fg fs-6" id="cached-offers-badge">
                                {{ $diagnostics['cached_offers_count'] }} {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.offers_active') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.last_sync') }}</span>
                            <span class="text-secondary fw-medium" id="last-sync-text">
                                {{ $diagnostics['last_synced_at'] ? $diagnostics['last_synced_at']->diffForHumans() : trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.never') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div id="sync-feedback-alert" class="alert alert-success mt-3 d-none align-items-center" role="alert">
                    <i class="ti ti-circle-check fs-4 me-2"></i>
                    <span id="sync-feedback-msg"></span>
                </div>
            </div>
        </div>

        {{-- Main Settings Form --}}
        {!! $form->renderForm() !!}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var syncBtn = document.getElementById('btn-sync-razorpay-offers');
            if (syncBtn) {
                syncBtn.addEventListener('click', function () {
                    var url = this.getAttribute('data-url');
                    var icon = document.getElementById('sync-icon');
                    var alertBox = document.getElementById('sync-feedback-alert');
                    var msgSpan = document.getElementById('sync-feedback-msg');
                    var badge = document.getElementById('cached-offers-badge');
                    var lastSync = document.getElementById('last-sync-text');

                    syncBtn.disabled = true;
                    if (icon) {
                        icon.classList.add('ti-spin');
                    }

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        syncBtn.disabled = false;
                        if (icon) {
                            icon.classList.remove('ti-spin');
                        }

                        alertBox.classList.remove('d-none', 'alert-danger', 'alert-success');
                        if (data.error) {
                            alertBox.classList.add('alert-danger');
                            msgSpan.textContent = data.message || 'Failed to sync offers.';
                        } else {
                            alertBox.classList.add('alert-success');
                            msgSpan.textContent = data.message;
                            if (badge && data.data && typeof data.data.count !== 'undefined') {
                                badge.textContent = data.data.count + ' {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.offers_active') }}';
                            }
                            if (lastSync) {
                                lastSync.textContent = '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.diagnostics.just_now') }}';
                            }
                        }
                    })
                    .catch(function (err) {
                        syncBtn.disabled = false;
                        if (icon) {
                            icon.classList.remove('ti-spin');
                        }
                        alertBox.classList.remove('d-none', 'alert-success');
                        alertBox.classList.add('alert-danger');
                        msgSpan.textContent = 'Network or server error while syncing offers.';
                    });
                });
            }
        });
    </script>
@endsection

