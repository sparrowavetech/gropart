{!! Assets::renderHeader(['core']) !!}

<link
    href="{{ asset('vendor/core/core/base/css/themes/default.css') }}?v={{ get_cms_version() }}"
    rel="stylesheet"
>
<link
    href="{{ asset('vendor/core/plugins/marketplace/css/vendors/normalize.css') }}"
    rel="stylesheet"
>
<link
    href="{{ asset('vendor/core/plugins/marketplace/css/vendors/material-icon-round.css') }}"
    rel="stylesheet"
>
<link
    href="{{ asset('vendor/core/plugins/marketplace/css/vendors/perfect-scrollbar.css') }}"
    rel="stylesheet"
>
<link
    href="{{ asset('vendor/core/plugins/marketplace/css/style.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}"
    rel="stylesheet"
>

@if (BaseHelper::siteLanguageDirection() == 'rtl')
    <link
        href="{{ asset('vendor/core/core/base/css/rtl.css') }}?v={{ get_cms_version() }}"
        rel="stylesheet"
    >
    <link
        href="{{ asset('vendor/core/plugins/marketplace/css/rtl.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}"
        rel="stylesheet"
    >
@endif
