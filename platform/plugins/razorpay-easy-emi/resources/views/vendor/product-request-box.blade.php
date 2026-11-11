@php
    $config = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig::query()->where('product_id', $model->id)->first();
    $pendingRequest = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest::query()
        ->where('product_id', $model->id)
        ->where('status', \SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum::PENDING)
        ->latest()
        ->first();
@endphp

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-credit-card text-primary fs-4"></i>
            <h5 class="card-title mb-0 fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.card_title') }}</h5>
        </div>
        <div>
            @if ($config && $config->status === 'enabled')
                <span class="badge bg-success text-success-fg">
                    <i class="ti ti-check me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.active') }} ({{ $config->emi_type->label() }})
                </span>
            @elseif ($pendingRequest)
                <span class="badge bg-warning text-warning-fg">
                    <i class="ti ti-clock me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_pending') }}
                </span>
            @else
                <span class="badge bg-secondary text-secondary-fg">
                    {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.not_active') }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if ($config && $config->status === 'enabled')
            <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                <i class="ti ti-circle-check fs-3 me-2"></i>
                <div>
                    <div><strong>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.emi_enabled_for_product') }}</strong></div>
                    <small class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.scheme') }}: {{ $config->emi_type->label() }} | {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention') }}: {{ $config->subvention_payer->label() }}</small>
                </div>
            </div>
        @elseif ($pendingRequest)
            <div class="alert alert-info mb-0">
                <div class="fw-bold mb-1"><i class="ti ti-info-circle me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.review_in_progress') }}</div>
                <small>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submitted_scheme') }}: <strong>{{ $pendingRequest->requested_scheme->label() }}</strong> ({{ $pendingRequest->created_at->diffForHumans() }})</small>
            </div>
        @else
            <p class="text-muted small mb-3">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.request_intro') }}</p>
            <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" data-bs-target="#vendorEmiRequestCollapse" aria-expanded="false">
                <i class="ti ti-send"></i>
                <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.btn_request_program') }}</span>
            </button>

            <div class="collapse mt-3" id="vendorEmiRequestCollapse">
                <div class="p-3 border rounded bg-light">
                    <form id="form-vendor-emi-request" action="{{ route('marketplace.vendor.razorpay-easy-emi.requests.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $model->id }}">

                        <div class="form-group mb-3">
                            <label for="vendor_requested_scheme" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.requested_scheme') }}</label>
                            <select class="form-select form-select-sm" name="requested_scheme" id="vendor_requested_scheme" required>
                                <option value="standard">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard') }}</option>
                                <option value="no_cost">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.no_cost') }}</option>
                                <option value="low_cost">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.low_cost') }}</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="vendor_subvention_willingness" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_willingness') }}</label>
                            <select class="form-select form-select-sm" name="subvention_willingness" id="vendor_subvention_willingness" required>
                                <option value="seller">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_seller') }}</option>
                                <option value="platform">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_platform') }}</option>
                            </select>
                            <small class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.subvention_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="vendor_emi_notes" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.notes') }}</label>
                            <textarea class="form-control form-control-sm" name="notes" id="vendor_emi_notes" rows="2" placeholder="{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.notes_placeholder') }}"></textarea>
                        </div>

                        <div id="vendor-emi-feedback" class="alert d-none mb-3 py-2 small"></div>

                        <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" id="btn-submit-vendor-emi">
                            <i class="ti ti-check"></i>
                            <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.submit_request') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('form-vendor-emi-request');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = document.getElementById('btn-submit-vendor-emi');
                var feedback = document.getElementById('vendor-emi-feedback');
                var formData = new FormData(form);

                btn.disabled = true;
                feedback.className = 'alert d-none mb-3 py-2 small';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    feedback.classList.remove('d-none');
                    if (data.error) {
                        feedback.classList.add('alert-danger');
                        feedback.textContent = data.message;
                    } else {
                        feedback.classList.add('alert-success');
                        feedback.textContent = data.message;
                        setTimeout(function () { window.location.reload(); }, 1200);
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    feedback.classList.remove('d-none');
                    feedback.classList.add('alert-danger');
                    feedback.textContent = 'Server or network error.';
                });
            });
        }
    })();
</script>

