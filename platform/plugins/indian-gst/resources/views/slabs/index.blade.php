@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="page-title mb-1">{{ __('Indian GST Tax Slabs & Catalogue Mapping') }}</h2>
                <div class="text-muted">{{ __('Overview of all Indian GST slabs and real-time product mappings.') }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('ecommerce.settings.indian-gst.index') }}" class="btn btn-outline-secondary">
                    <x-core::icon name="ti ti-settings" />
                    {{ __('GST Settings') }}
                </a>
                <a href="{{ route('tax.index') }}" class="btn btn-primary">
                    <x-core::icon name="ti ti-receipt-tax" />
                    {{ __('Manage Taxes Master') }}
                </a>
            </div>
        </div>

        <!-- Metric summary boxes -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar">
                                <x-core::icon name="ti ti-receipt-tax" />
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ $taxes->count() }} {{ __('Active Slabs') }}</div>
                            <div class="text-muted small">{{ __('GST 0%, 5%, 18%, 40%') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green text-white avatar">
                                <x-core::icon name="ti ti-package" />
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ number_format($totalMappedProducts) }} {{ __('Mapped Products') }}</div>
                            <div class="text-muted small">{{ __('Mapped to GST Slabs') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-indigo text-white avatar">
                                <x-core::icon name="ti ti-building" />
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ \SparroWave\IndianGst\Supports\IndianGstHelper::getCompanyStateName() }}</div>
                            <div class="text-muted small">{{ __('Origin State Code: :code', ['code' => \SparroWave\IndianGst\Supports\IndianGstHelper::getCompanyStateCode()]) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-azure text-white avatar">
                                <x-core::icon name="ti ti-file-certificate" />
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-truncate" title="{{ setting('indian_gst_default_company_gstin', '08AUBPA5903F1Z5') }}">{{ setting('indian_gst_default_company_gstin', '08AUBPA5903F1Z5') }}</div>
                            <div class="text-muted small">{{ __('Company GSTIN') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Slabs Grid -->
    <div class="row g-3">
        @foreach($taxes as $tax)
            @php
                $count = $taxStats[$tax->id] ?? 0;
                $pctShare = $totalMappedProducts > 0 ? round(($count / $totalMappedProducts) * 100, 1) : 0;
                $isDefault = (string)setting('default_tax_rate') === (string)$tax->id;
            @endphp
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 {{ $isDefault ? 'border-primary' : '' }}" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div class="card-status-top bg-{{ $isDefault ? 'primary' : ($tax->percentage >= 40 ? 'danger' : ($tax->percentage >= 18 ? 'azure' : ($tax->percentage > 0 ? 'success' : 'secondary'))) }}"></div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="card-title fw-bold fs-3 mb-0">{{ $tax->title }}</h3>
                            <span class="badge bg-{{ $isDefault ? 'primary' : 'dark' }}-lt fs-4 px-2 py-1">
                                {{ $tax->percentage }}%
                            </span>
                        </div>

                        @if($isDefault)
                            <div class="mb-2">
                                <span class="badge bg-primary-lt">
                                    <x-core::icon name="ti ti-star-filled" style="width: 12px; height: 12px;" />
                                    {{ __('Default Tax Rate') }}
                                </span>
                            </div>
                        @endif

                        <div class="my-3">
                            <div class="display-6 fw-bold text-dark mb-1">{{ number_format($count) }}</div>
                            <div class="text-muted small">{{ Str::plural(__('Product Mapped'), $count) }} ({{ $pctShare }}% {{ __('of catalogue') }})</div>
                        </div>

                        <div class="progress progress-sm mb-3">
                            <div class="progress-bar bg-{{ $isDefault ? 'primary' : 'azure' }}" style="width: {{ $pctShare }}%" role="progressbar"></div>
                        </div>

                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <a href="{{ route('indian-gst.slabs.products', $tax->id) }}" class="btn btn-primary w-100">
                                <x-core::icon name="ti ti-list" />
                                {{ __('View Products') }}
                            </a>
                            <a href="{{ route('tax.edit', $tax->id) }}" class="btn btn-icon btn-ghost-secondary" title="{{ __('Edit Slab') }}">
                                <x-core::icon name="ti ti-edit" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
