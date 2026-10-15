<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ BaseHelper::siteLanguageDirection() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('plugins/enquiry-products::messages.enquiry_submitted_successfully') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/enquiry-products/enquiry-products.css') }}?v={{ (is_file(public_path('vendor/core/plugins/enquiry-products/enquiry-products.css')) ? filemtime(public_path('vendor/core/plugins/enquiry-products/enquiry-products.css')) : '1.0.0') }}">
</head>
<body class="ep-confirmation-page">
    <main class="ep-confirmation-layout">
        <section class="ep-confirmation-main" aria-labelledby="ep-confirmation-title">
            <a class="ep-confirmation-logo" href="{{ url('/') }}" aria-label="{{ __('plugins/enquiry-products::messages.home') }}">@if(Theme::getLogo())<img src="{{ RvMedia::getImageUrl(Theme::getLogo()) }}" alt="{{ Theme::getSiteTitle() }}" width="150" height="80">@else{{ Theme::getSiteTitle() }}@endif</a>
            <h1 id="ep-confirmation-title"><span class="ep-confirmation-check" aria-hidden="true">✓</span> {{ __('plugins/enquiry-products::messages.your_enquiry_is_successfully_placed') }}</h1>
            <p>{{ __('plugins/enquiry-products::messages.thank_you_for_enquiring_our_products') }}</p>
            <section class="ep-confirmation-customer" aria-labelledby="ep-customer-heading">
                <h2 id="ep-customer-heading">{{ __('plugins/enquiry-products::messages.customer_information') }}</h2>
                <dl>
                    <dt>{{ __('plugins/enquiry-products::messages.full_name') }}:</dt><dd>{{ $enquiry->name }}</dd>
                    <dt>{{ __('plugins/enquiry-products::messages.contact_number') }}:</dt><dd>{{ $enquiry->phone }}</dd>
                    <dt>{{ __('plugins/enquiry-products::messages.email') }}:</dt><dd>{{ $enquiry->email }}</dd>
                    <dt>{{ __('plugins/enquiry-products::messages.address') }}:</dt><dd>{{ $enquiry->address }}{{ $enquiry->zip_code ? ', ' . $enquiry->zip_code : '' }}</dd>
                </dl>
            </section>
            <a class="ep-confirmation-home" href="{{ url('/') }}">← {{ __('plugins/enquiry-products::messages.continue_to_home_page') }}</a>
        </section>
        <aside class="ep-confirmation-summary" aria-label="{{ __('plugins/enquiry-products::messages.enquiry_summary') }}">
            <p>{{ __('plugins/enquiry-products::messages.enquiry_number') }}: <strong>{{ $enquiry->code }}</strong></p>
            @if($product)
                @php($productUrl = $product->slugable?->key ? route('public.enquiry-products.show', $product->slugable->key) : null)
                <div class="ep-confirmation-product">
                    <img src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}">
                    <div>
                        <h2>@if($productUrl)<a href="{{ $productUrl }}">{{ $product->name }}</a>@else{{ $product->name }}@endif</h2>
                        @if($seller)
                            <div class="ep-confirmation-seller">{{ __('plugins/enquiry-products::messages.sold_by') }}: <a href="{{ \SparroWave\EnquiryProducts\Support\StoreUrl::catalogue($seller) ?? route('public.enquiry-products.index') }}">{{ $seller->name }}</a>
                                @if($seller->is_verified)<span class="ep-verified" title="{{ __('plugins/enquiry-products::messages.verified_seller') }}" aria-label="{{ __('plugins/enquiry-products::messages.verified_seller') }}">✓</span>@endif
                                @if(is_plugin_active('vendor-verified-badge'))
                                    @php($sellerType = \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::getShopType($seller))
                                    @if($sellerType)<span class="ep-seller-badge">{{ $sellerType->label() }}</span>@endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @else<p>{{ __('plugins/enquiry-products::messages.product_no_longer_available') }}</p>@endif
        </aside>
    </main>
</body>
</html>
