<div class="bb-customer-card order-info-card mb-4">
    <div class="bb-customer-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="bb-customer-card-title h5 mb-0">
                <x-core::icon name="ti ti-building" class="me-1" />
                {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.company_profile') }}
            </h3>
            <a href="{{ route('customer.wholesale.profile.edit') }}" class="btn btn-outline-primary btn-sm">
                <x-core::icon name="ti ti-edit" class="me-1" />
                {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.edit') }}
            </a>
        </div>
    </div>
    <div class="bb-customer-card-body">
        <div class="bb-customer-card-info">
            <div class="row g-3">
                @if ($application->company_name)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.company_name') }}</span>
                            <span class="value fw-semibold">{{ $application->company_name }}</span>
                        </div>
                    </div>
                @endif

                @if ($application->tax_id)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.tax_id') }}</span>
                            <span class="value">{{ $application->tax_id }}</span>
                        </div>
                    </div>
                @endif

                @if ($application->phone)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.phone') }}</span>
                            <span class="value">{{ $application->phone }}</span>
                        </div>
                    </div>
                @endif

                @if ($application->business_type)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.business_type') }}</span>
                            <span class="value">{{ $application->business_type }}</span>
                        </div>
                    </div>
                @endif

                @if ($application->expected_volume)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume') }}</span>
                            <span class="value">{{ $application->expected_volume }}</span>
                        </div>
                    </div>
                @endif

                @if ($application->notes)
                    <div class="col-12">
                        <div class="info-item">
                            <span class="label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.notes') }}</span>
                            <span class="value">{{ $application->notes }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
