@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="page-title mb-1">{{ trans('plugins/indian-gst::indian-gst.slabs.page_title') }}</h2>
                <div class="text-muted">{{ trans('plugins/indian-gst::indian-gst.slabs.description') }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('ecommerce.settings.indian-gst.index') }}" class="btn btn-outline-secondary">
                    <x-core::icon name="ti ti-settings" />
                    {{ trans('plugins/indian-gst::indian-gst.settings.menu') }}
                </a>
                <a href="{{ route('tax.index') }}" class="btn btn-primary">
                    <x-core::icon name="ti ti-receipt-tax" />
                    {{ trans('plugins/indian-gst::indian-gst.slabs.manage_taxes') }}
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
                            <div class="font-weight-medium">{{ $taxes->count() }} {{ trans('plugins/indian-gst::indian-gst.slabs.active') }}</div>
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
                            <div class="font-weight-medium">{{ number_format($totalMappedProducts) }} {{ trans('plugins/indian-gst::indian-gst.slabs.mapped_products') }}</div>
                            <div class="text-muted small">{{ trans('plugins/indian-gst::indian-gst.slabs.mapped_to_slabs') }}</div>
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
                            <div class="text-muted small">{{ trans('plugins/indian-gst::indian-gst.slabs.origin_state_code', ['code' => \SparroWave\IndianGst\Supports\IndianGstHelper::getCompanyStateCode() ?: '—']) }}</div>
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
                            <div class="font-weight-medium text-truncate" title="{{ \SparroWave\IndianGst\Supports\IndianGstHelper::getCompanyGstin() }}">{{ \SparroWave\IndianGst\Supports\IndianGstHelper::getCompanyGstin() ?: '—' }}</div>
                            <div class="text-muted small">{{ trans('plugins/indian-gst::indian-gst.settings.company_gstin') }}</div>
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
                                    {{ trans('plugins/indian-gst::indian-gst.slabs.default_rate') }}
                                </span>
                            </div>
                        @endif

                        <div class="my-3">
                            <div class="display-6 fw-bold text-dark mb-1">{{ number_format($count) }}</div>
                            <div class="text-muted small">{{ trans_choice('plugins/indian-gst::indian-gst.slabs.product_mapped', $count) }} ({{ $pctShare }}% {{ trans('plugins/indian-gst::indian-gst.slabs.of_catalogue') }})</div>
                        </div>

                        <div class="progress progress-sm mb-3">
                            <div class="progress-bar bg-{{ $isDefault ? 'primary' : 'azure' }}" style="width: {{ $pctShare }}%" role="progressbar"></div>
                        </div>

                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <a href="{{ route('indian-gst.slabs.products', $tax->id) }}" class="btn btn-primary w-100">
                                <x-core::icon name="ti ti-list" />
                                {{ trans('plugins/indian-gst::indian-gst.slabs.view_products') }}
                            </a>
                            <a href="{{ route('tax.edit', $tax->id) }}" class="btn btn-icon btn-ghost-secondary" title="{{ trans('plugins/indian-gst::indian-gst.slabs.edit_slab') }}">
                                <x-core::icon name="ti ti-edit" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
