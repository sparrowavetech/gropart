@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'))

@section('content')
    @php
        $statusValue = $application->status->getValue();
        $isPending = $statusValue === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::PENDING;
        $isApproved = $statusValue === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::APPROVED;
        $isRejected = $statusValue === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::REJECTED;
    @endphp

    <div class="bb-customer-content-wrapper">
        @include('plugins/ecommerce-wholesale::themes.customer.partials.application-timeline', compact('application', 'isPending', 'isApproved', 'isRejected'))

        @if ($isRejected)
            <div class="bb-customer-card order-info-card mb-4">
                <div class="bb-customer-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.want_to_try_again') }}</h6>
                            <p class="text-muted small mb-0">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.submit_new_application') }}</p>
                        </div>
                        <a href="{{ route('customer.wholesale.reapply.form') }}" class="btn btn-primary btn-sm">
                            <x-core::icon name="ti ti-refresh" class="me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.reapply') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if ($isApproved && $application->assignedGroup)
            @include('plugins/ecommerce-wholesale::themes.customer.partials.customer-group-details', [
                'group' => $application->assignedGroup,
                'groupAssignment' => $groupAssignment,
            ])
        @endif

        @include('plugins/ecommerce-wholesale::themes.customer.partials.company-profile', compact('application'))
    </div>
@endsection
