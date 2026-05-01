<div class="bb-customer-card order-info-card mb-4">
    <div class="bb-customer-card-header">
        <h3 class="bb-customer-card-title h5 mb-0">
            <x-core::icon name="ti ti-timeline" class="me-1" />
            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.application_timeline') }}
        </h3>
    </div>
    <div class="bb-customer-card-body">
        <div class="list-group list-group-flush">
            {{-- Stage 1: Submitted --}}
            <div class="list-group-item px-0 d-flex align-items-start gap-3">
                <span class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                    <x-core::icon name="ti ti-send" class="text-white" size="sm" />
                </span>
                <div>
                    <div class="fw-semibold">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.application_submitted') }}</div>
                    <div class="text-muted small">{{ $application->created_at->translatedFormat('M d, Y \a\t h:i A') }}</div>
                </div>
            </div>

            {{-- Stage 2: Under Review --}}
            @if ($isPending)
                <div class="list-group-item px-0 d-flex align-items-start gap-3">
                    <span class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <x-core::icon name="ti ti-clock" class="text-white" size="sm" />
                    </span>
                    <div>
                        <div class="fw-semibold">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.under_review') }}</div>
                        <div class="text-muted small">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.application_under_review') }}</div>
                    </div>
                </div>
            @endif

            {{-- Stage 3: Approved --}}
            @if ($isApproved)
                <div class="list-group-item px-0 d-flex align-items-start gap-3">
                    <span class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <x-core::icon name="ti ti-check" class="text-white" size="sm" />
                    </span>
                    <div>
                        <div class="fw-semibold">{{ trans('plugins/ecommerce-wholesale::wholesale.application.approved') }}</div>
                        <div class="text-muted small">
                            {{ $application->reviewed_at?->translatedFormat('M d, Y \a\t h:i A') }}
                            @if ($application->reviewer)
                                &mdash; {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.by_name', ['name' => $application->reviewer->name]) }}
                            @endif
                        </div>
                        @if ($application->assignedGroup)
                            <span class="badge bg-green-lt mt-1">{{ $application->assignedGroup->name }}</span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Stage 3: Rejected --}}
            @if ($isRejected)
                <div class="list-group-item px-0 d-flex align-items-start gap-3">
                    <span class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <x-core::icon name="ti ti-x" class="text-white" size="sm" />
                    </span>
                    <div>
                        <div class="fw-semibold">{{ trans('plugins/ecommerce-wholesale::wholesale.application.rejected') }}</div>
                        <div class="text-muted small">
                            {{ $application->reviewed_at?->translatedFormat('M d, Y \a\t h:i A') }}
                            @if ($application->reviewer)
                                &mdash; {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.by_name', ['name' => $application->reviewer->name]) }}
                            @endif
                        </div>
                        @if ($application->rejection_reason)
                            <div class="alert alert-danger-lt mt-2 mb-0 small py-2 px-3">
                                <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.reason') }}</strong> {{ $application->rejection_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
