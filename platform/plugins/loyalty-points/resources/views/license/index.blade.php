@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <x-core::icon name="ti ti-alert-triangle" class="me-2" />
                <div>
                    <strong>{{ trans('plugins/loyalty-points::loyalty-points.license.access_denied') }}</strong>
                    <p class="mb-0 mt-1">{{ session('warning') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/loyalty-points::loyalty-points.license.title') }}</h4>
                </div>
                <div class="card-body">
                    @if($isLicenseVerified && $licenseData)
                        @include('plugins/loyalty-points::license.partials.activated', ['licenseData' => $licenseData])
                    @else
                        @include('plugins/loyalty-points::license.partials.form')
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ trans('plugins/loyalty-points::loyalty-points.license.help_title') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>{{ trans('plugins/loyalty-points::loyalty-points.license.what_is_purchase_code') }}</h6>
                        <p class="text-muted small">{{ trans('plugins/loyalty-points::loyalty-points.license.purchase_code_description') }}</p>
                    </div>

                    <div class="mb-3">
                        <h6>{{ trans('plugins/loyalty-points::loyalty-points.license.where_to_find') }}</h6>
                        <p class="text-muted small">{{ trans('plugins/loyalty-points::loyalty-points.license.find_purchase_code_description') }}</p>
                        <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code"
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            {{ trans('plugins/loyalty-points::loyalty-points.license.learn_more') }}
                        </a>
                    </div>

                    <div class="mb-3">
                        <h6>{{ trans('plugins/loyalty-points::loyalty-points.license.license_terms') }}</h6>
                        <p class="text-muted small">{{ trans('plugins/loyalty-points::loyalty-points.license.license_terms_description') }}</p>
                        <a href="https://codecanyon.net/licenses/standard"
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                            {{ trans('plugins/loyalty-points::loyalty-points.license.view_license_terms') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/loyalty-points/css/license-activation.css') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}">

    <script>
    window.loyaltyTranslations = window.loyaltyTranslations || {};
    window.loyaltyTranslations = {
        somethingWentWrong: '{{ trans("plugins/loyalty-points::loyalty-points.js.something_went_wrong") }}',
        deactivateLicenseConfirm: '{{ trans("plugins/loyalty-points::loyalty-points.js.deactivate_license_confirm") }}',
        showPurchaseCode: '{{ trans("plugins/loyalty-points::loyalty-points.js.show_purchase_code") }}',
        hidePurchaseCode: '{{ trans("plugins/loyalty-points::loyalty-points.js.hide_purchase_code") }}'
    };
    </script>

    <script src="{{ asset('vendor/core/plugins/loyalty-points/js/license-activation.js') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}"></script>
@endpush
