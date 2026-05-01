@if ($application)
    <div class="card border-0 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="bg-indigo bg-opacity-20 rounded-circle p-3 d-inline-flex">
                        <x-core::icon name="ti ti-building-store" class="text-white" size="lg" />
                    </span>
                </div>
                <div class="col">
                    <h5 class="card-title h6 mb-1">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account') }}</h5>
                    @if ($application->status->getValue() === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::PENDING)
                        <span class="badge bg-yellow-lt">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.under_review') }}</span>
                        <p class="text-muted small mb-0 mt-1">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.submitted_date', ['date' => $application->created_at->translatedFormat('M d, Y')]) }}
                        </p>
                    @elseif ($application->status->getValue() === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::APPROVED)
                        <span class="badge bg-green-lt">{{ trans('plugins/ecommerce-wholesale::wholesale.application.approved') }}</span>
                        @if ($application->assignedGroup)
                            <span class="badge bg-indigo-lt ms-1">{{ $application->assignedGroup->name }}</span>
                            @if ($application->assignedGroup->discount_value > 0)
                                <p class="text-muted small mb-0 mt-1">
                                    @if ($application->assignedGroup->discount_type->getValue() === \Botble\EcommerceWholesale\Enums\DiscountTypeEnum::PERCENTAGE)
                                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.discount_percent_off', ['value' => rtrim(rtrim(number_format($application->assignedGroup->discount_value, 2), '0'), '.')]) }}
                                    @else
                                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.discount_fixed_off', ['value' => format_price($application->assignedGroup->discount_value)]) }}
                                    @endif
                                </p>
                            @endif
                        @endif
                    @elseif ($application->status->getValue() === \Botble\EcommerceWholesale\Enums\ApplicationStatusEnum::REJECTED)
                        <span class="badge bg-red-lt">{{ trans('plugins/ecommerce-wholesale::wholesale.application.rejected') }}</span>
                        <p class="text-muted small mb-0 mt-1">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.rejected_date', ['date' => $application->reviewed_at?->translatedFormat('M d, Y') ?? $application->updated_at->translatedFormat('M d, Y')]) }}
                        </p>
                    @endif
                </div>
                <div class="col-12 col-md-auto mt-2 mt-md-0">
                    <a href="{{ route('customer.wholesale.index') }}" class="btn btn-outline-primary btn-sm">
                        <x-core::icon name="ti ti-building-store" class="me-1" />
                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.view_details') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
