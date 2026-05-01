@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce-wholesale::wholesale.frontend.reapply_for_wholesale'))

@section('content')
    <div class="bb-customer-content-wrapper">
        <div class="bb-customer-card order-info-card mb-4">
            <div class="bb-customer-card-header">
                <h3 class="bb-customer-card-title h5 mb-0">
                    <x-core::icon name="ti ti-refresh" class="me-1" />
                    {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.reapply_for_wholesale') }}
                </h3>
            </div>
            <div class="bb-customer-card-body">
                @if ($application)
                    <div class="alert alert-warning mb-4">
                        <x-core::icon name="ti ti-alert-triangle" class="me-1" />
                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.previous_rejected_on', ['date' => $application->reviewed_at?->translatedFormat('M d, Y') ?? $application->updated_at->translatedFormat('M d, Y')]) }}
                        @if ($application->rejection_reason)
                            <br>
                            <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.reason') }}</strong> {{ $application->rejection_reason }}
                        @endif
                    </div>
                @endif

                <form action="{{ route('customer.wholesale.reapply.submit') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ trans('plugins/ecommerce-wholesale::wholesale.application.company_name') }}</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                value="{{ old('company_name', $application?->company_name) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.tax_id') }}</label>
                            <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror"
                                value="{{ old('tax_id', $application?->tax_id) }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.phone') }}</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $application?->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.business_type') }}</label>
                            <input type="text" name="business_type" class="form-control @error('business_type') is-invalid @enderror"
                                value="{{ old('business_type', $application?->business_type) }}"
                                placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.business_type_placeholder') }}">
                            @error('business_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume') }}</label>
                        <input type="text" name="expected_volume" class="form-control @error('expected_volume') is-invalid @enderror"
                            value="{{ old('expected_volume', $application?->expected_volume) }}"
                            placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.expected_volume_placeholder') }}">
                        @error('expected_volume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.notes') }}</label>
                        <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.tell_us_about_business') }}">{{ old('notes', $application?->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('customer.wholesale.index') }}" class="btn btn-outline-secondary">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-core::icon name="ti ti-send" class="me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.submit_reapplication') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
