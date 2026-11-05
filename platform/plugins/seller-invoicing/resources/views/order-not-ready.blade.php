@extends($vendor ? MarketplaceHelper::viewPath('vendor-dashboard.layouts.master') : BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="mb-3">
                <x-core::icon name="ti ti-file-invoice" class="text-muted" style="font-size: 3rem;" />
            </div>
            <h3>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_pending_title') }}</h3>
            <p class="text-muted mx-auto" style="max-width: 500px;">
                {{ trans('plugins/seller-invoicing::seller-invoicing.invoice_pending_desc') }}
            </p>
            <div class="mt-4">
                <a href="{{ $vendor ? route('marketplace.vendor.seller-invoicing.index') : route('seller-invoicing.index') }}" class="btn btn-secondary">
                    <x-core::icon name="ti ti-arrow-left" /> {{ trans('plugins/seller-invoicing::seller-invoicing.back_to_library') }}
                </a>
            </div>
        </div>
    </div>
@endsection

