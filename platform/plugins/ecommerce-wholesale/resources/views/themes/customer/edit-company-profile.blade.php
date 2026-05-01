@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce-wholesale::wholesale.frontend.edit_company_profile'))

@section('content')
    <div class="bb-customer-content-wrapper">
        <div class="bb-customer-card order-info-card mb-4">
            <div class="bb-customer-card-header">
                <h3 class="bb-customer-card-title h5 mb-0">
                    <x-core::icon name="ti ti-edit" class="me-1" />
                    {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.edit_company_profile') }}
                </h3>
            </div>
            <div class="bb-customer-card-body">
                @if ($application->company_name || $application->tax_id)
                    <div class="alert alert-info mb-4">
                        <x-core::icon name="ti ti-info-circle" class="me-1" />
                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.company_name_tax_id_readonly') }}
                    </div>

                    <div class="row mb-4">
                        @if ($application->company_name)
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ trans('plugins/ecommerce-wholesale::wholesale.application.company_name') }}</label>
                                <input type="text" class="form-control" value="{{ $application->company_name }}" disabled>
                            </div>
                        @endif
                        @if ($application->tax_id)
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ trans('plugins/ecommerce-wholesale::wholesale.application.tax_id') }}</label>
                                <input type="text" class="form-control" value="{{ $application->tax_id }}" disabled>
                            </div>
                        @endif
                    </div>
                @endif

                <form action="{{ route('customer.wholesale.profile.update') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.phone') }}</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $application->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.business_type') }}</label>
                            <input type="text" name="business_type" class="form-control @error('business_type') is-invalid @enderror"
                                value="{{ old('business_type', $application->business_type) }}"
                                placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.business_type_placeholder') }}">
                            @error('business_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume') }}</label>
                        <input type="text" name="expected_volume" class="form-control @error('expected_volume') is-invalid @enderror"
                            value="{{ old('expected_volume', $application->expected_volume) }}"
                            placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.expected_volume_placeholder') }}">
                        @error('expected_volume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ trans('plugins/ecommerce-wholesale::wholesale.application.notes') }}</label>
                        <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.notes_placeholder') }}">{{ old('notes', $application->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('customer.wholesale.index') }}" class="btn btn-outline-secondary">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-core::icon name="ti ti-device-floppy" class="me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
