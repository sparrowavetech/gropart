@php
    $config = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig::query()->where('product_id', $model->id)->first();
    $latestRequest = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest::query()
        ->where('product_id', $model->id)
        ->latest()
        ->first();
    $isApproved = $config && $config->status === 'enabled';
    $isPending = ! $isApproved && $latestRequest && $latestRequest->status->isPending();
    $isRejected = ! $isApproved && $latestRequest && $latestRequest->status->isRejected();
@endphp

<div class="card mb-3 border-0 shadow-sm" id="vendor-emi-request-metabox">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <rect x="3" y="5" width="18" height="14" rx="3" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <line x1="7" y1="15" x2="7.01" y2="15" />
                <line x1="11" y1="15" x2="13" y2="15" />
            </svg>
            <h5 class="card-title mb-0 fw-bold fs-6">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.card_title') }}</h5>
        </div>
        <div>
            @if ($isApproved)
                <span class="badge bg-success text-success-fg">
                    <i class="ti ti-check me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.active') }} ({{ $config->emi_type->label() }})
                </span>
            @elseif ($isPending)
                <span class="badge bg-warning text-dark">
                    <i class="ti ti-clock me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_pending') }}
                </span>
            @elseif ($isRejected)
                <span class="badge bg-danger text-danger-fg">
                    <i class="ti ti-x me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.request_statuses.rejected') }}
                </span>
            @else
                <span class="badge bg-secondary text-secondary-fg">
                    {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.not_active') }}
                </span>
            @endif
        </div>
    </div>

    <div class="card-body p-3">
        @if ($isApproved)
            {{-- State 1: Active & Configured --}}
            <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3" role="alert">
                <i class="ti ti-circle-check fs-2 me-3 text-success"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.emi_enabled_for_product') }}</div>
                    <div class="small text-muted mt-1">
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.scheme') }}: <strong>{{ $config->emi_type->label() }}</strong></span>
                        <span class="mx-2">&bull;</span>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention') }}: <strong>{{ $config->subvention_payer->label() }}</strong></span>
                    </div>
                    @if ($latestRequest && $latestRequest->reviewed_at)
                        <div class="small text-muted mt-1">
                            <i class="ti ti-info-circle me-1"></i> Approved on {{ $latestRequest->reviewed_at->format('d M Y, h:i A') }}
                            @if ($latestRequest->admin_remarks)
                                &mdash; <em>{{ $latestRequest->admin_remarks }}</em>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        @elseif ($isPending)
            {{-- State 2: Submitted & Under Review --}}
            <div class="alert alert-warning mb-3 py-3 px-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="ti ti-clock-hour-4 fs-3 text-warning me-2"></i>
                    <span class="fw-bold fs-6">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.review_in_progress') }}</span>
                </div>
                <p class="small text-muted mb-2">
                    {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.review_in_progress_desc') }}
                </p>
                <div class="bg-white p-3 rounded border small">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.requested_scheme') }}:</span>
                            <strong class="text-dark">{{ $latestRequest->requested_scheme->label() }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_willingness') }}:</span>
                            <strong class="text-dark">{{ $latestRequest->subvention_willingness->label() }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Submitted At:</span>
                            <span class="text-dark">{{ $latestRequest->created_at->format('d M Y, h:i A') }} ({{ $latestRequest->created_at->diffForHumans() }})</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-warning text-dark">{{ $latestRequest->status->label() }}</span>
                        </div>
                        @if ($latestRequest->notes)
                            <div class="col-12 mt-2 pt-2 border-top">
                                <span class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.notes') }}:</span>
                                <em class="text-secondary">{{ $latestRequest->notes }}</em>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" id="btn-cancel-vendor-emi" data-url="{{ route('marketplace.vendor.razorpay-easy-emi.requests.cancel', $latestRequest->id) }}">
                        <i class="ti ti-x"></i>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.cancel_request') }}</span>
                    </button>
                </div>
            </div>

        @else
            {{-- State 3: Rejected or Unsubmitted --}}
            @if ($isRejected)
                <div class="alert alert-danger mb-3 py-3 px-3">
                    <div class="d-flex align-items-center mb-1">
                        <i class="ti ti-alert-triangle fs-3 text-danger me-2"></i>
                        <span class="fw-bold fs-6">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_rejected_title') }}</span>
                    </div>
                    <p class="small mb-2">
                        {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_rejected_desc') }}
                        @if ($latestRequest->admin_remarks)
                            <br><strong>Admin Reason / Remarks:</strong> <span class="text-dark">{{ $latestRequest->admin_remarks }}</span>
                        @endif
                        @if ($latestRequest->reviewed_at)
                            <br><small class="text-muted">Reviewed on: {{ $latestRequest->reviewed_at->format('d M Y, h:i A') }}</small>
                        @endif
                    </p>
                    <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" data-bs-target="#vendorEmiRequestCollapse" aria-expanded="false">
                        <i class="ti ti-reload"></i>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.reapply_button') }}</span>
                    </button>
                </div>
            @else
                <p class="text-muted small mb-3">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_intro') }}</p>
                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-2" data-bs-toggle="collapse" data-bs-target="#vendorEmiRequestCollapse" aria-expanded="false">
                    <i class="ti ti-send"></i>
                    <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.btn_request_program') }}</span>
                </button>
            @endif

            <div class="collapse {{ $isRejected ? '' : '' }} mt-2" id="vendorEmiRequestCollapse">
                <div class="p-3 border rounded bg-light" id="vendor-emi-request-container">
                    <input type="hidden" id="vendor_emi_product_id" value="{{ $model->id }}">

                    <div class="form-group mb-3">
                        <label for="vendor_requested_scheme" class="form-label fw-bold small text-dark mb-1">
                            {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.requested_scheme') }}
                        </label>
                        <select class="form-select form-select-sm" id="vendor_requested_scheme">
                            <option value="standard">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard') }}</option>
                            <option value="no_cost" selected>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.no_cost') }}</option>
                            <option value="low_cost">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.low_cost') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="vendor_subvention_willingness" class="form-label fw-bold small text-dark mb-1">
                            {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_willingness') }}
                        </label>
                        <select class="form-select form-select-sm" id="vendor_subvention_willingness">
                            <option value="seller">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_seller') }}</option>
                            <option value="platform">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_platform') }}</option>
                        </select>
                        <small class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_help') }}</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="vendor_emi_notes" class="form-label fw-bold small text-dark mb-1">
                            {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.notes') }}
                        </label>
                        <textarea class="form-control form-control-sm" id="vendor_emi_notes" rows="2" placeholder="{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.notes_placeholder') }}"></textarea>
                    </div>

                    <div id="vendor-emi-feedback" class="alert d-none mb-3 py-2 small"></div>

                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" id="btn-submit-vendor-emi">
                        <i class="ti ti-send"></i>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submit_request') }}</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Footer Link to Requests Table --}}
        <div class="d-flex justify-content-between align-items-center pt-2 mt-3 border-top">
            <small class="text-muted">
                <i class="ti ti-shield-check text-success me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.footer_note') }}
            </small>
            <a href="{{ route('marketplace.vendor.razorpay-easy-emi.requests.index') }}" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold">
                <i class="ti ti-list-details me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.view_all_requests_link') }} &rarr;
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        var btn = document.getElementById('btn-submit-vendor-emi');
        if (!btn) return;

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            var productIdEl = document.getElementById('vendor_emi_product_id');
            var schemeEl = document.getElementById('vendor_requested_scheme');
            var subventionEl = document.getElementById('vendor_subvention_willingness');
            var notesEl = document.getElementById('vendor_emi_notes');
            var feedback = document.getElementById('vendor-emi-feedback');

            if (!productIdEl || !schemeEl) return;

            var productId = productIdEl.value;
            var scheme = schemeEl.value;
            var subvention = subventionEl ? subventionEl.value : 'seller';
            var notes = notesEl ? notesEl.value : '';

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ' + '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submitting') }}';
            feedback.className = 'alert d-none mb-3 py-2 small';

            fetch('{{ route('marketplace.vendor.razorpay-easy-emi.requests.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    requested_scheme: scheme,
                    subvention_willingness: subvention,
                    notes: notes
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send me-1"></i> ' + '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submit_request') }}';
                feedback.classList.remove('d-none');
                if (data.error) {
                    feedback.classList.add('alert-danger');
                    feedback.textContent = data.message;
                } else {
                    feedback.classList.add('alert-success');
                    feedback.textContent = data.message;
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send me-1"></i> ' + '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submit_request') }}';
                feedback.classList.remove('d-none');
                feedback.classList.add('alert-danger');
                feedback.textContent = 'Server or network error. Please try again.';
            });
        });
        var cancelBtn = document.getElementById('btn-cancel-vendor-emi');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!confirm('{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.confirm_cancel_request') }}')) {
                    return;
                }

                var url = cancelBtn.getAttribute('data-url');
                cancelBtn.disabled = true;
                cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.cancelling') }}';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.error) {
                        alert(data.message);
                        cancelBtn.disabled = false;
                        cancelBtn.innerHTML = '<i class="ti ti-x me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.cancel_request') }}';
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function () {
                    alert('Server or network error. Please try again.');
                    cancelBtn.disabled = false;
                    cancelBtn.innerHTML = '<i class="ti ti-x me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.cancel_request') }}';
                });
            });
        }
    })();
</script>
