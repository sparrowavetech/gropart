<div class="ep-grid">
@forelse($products as $product)
    <article class="ep-card">
        <a href="{{ route('public.enquiry-products.show', $product->slugable->key) }}"><img loading="lazy" src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}"></a>
        <div class="ep-card-body">
            @if(is_plugin_active('marketplace') && $product->store)
                <div class="ep-card-seller"><a href="{{ \SparroWave\EnquiryProducts\Support\StoreUrl::catalogue($product->store) ?? route('public.enquiry-products.index') }}">{{ $product->store->name }}</a>
                    @if(is_plugin_active('vendor-verified-badge'))
                        {!! \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::renderBadges($product->store, ['size' => 12]) !!}
                    @elseif($product->store->is_verified)<span class="ep-verified" title="{{ __('plugins/enquiry-products::messages.verified_seller') }}">✓</span>@endif
                </div>
            @endif
            <h2><a href="{{ route('public.enquiry-products.show', $product->slugable->key) }}">{{ $product->name }}</a></h2>
            <a class="ep-button" href="{{ route('public.enquiry-products.show', $product->slugable->key) }}">{{ __('plugins/enquiry-products::messages.enquiry_now') }}</a>
        </div>
    </article>
@empty
    <p class="ep-empty">{{ __('plugins/enquiry-products::messages.no_enquiry_equipment_matches_your_search_try_another_name_or_cate') }}</p>
@endforelse
</div>
