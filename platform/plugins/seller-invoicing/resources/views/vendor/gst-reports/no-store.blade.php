@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <div class="avatar avatar-xl bg-warning-subtle text-warning mb-3">
                    <x-core::icon name="ti ti-alert-triangle" class="fs-1" />
                </div>
                <h3 class="fw-bold text-dark">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.no_store_found') }}</h3>
                <p class="text-muted">
                    Please ensure your vendor store profile is created and verified to access your GSTR-1 and GSTR-3B tax reports.
                </p>
                <a href="{{ route('marketplace.vendor.dashboard') }}" class="btn btn-primary mt-2">
                    <x-core::icon name="ti ti-dashboard" class="me-1" /> Return to Vendor Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

