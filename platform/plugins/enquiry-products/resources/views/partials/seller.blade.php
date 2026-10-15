@if(is_plugin_active('marketplace') && $product->store)
<p class="ep-sold-by">{{ __('plugins/enquiry-products::messages.sold_by') }}: <a href="{{ \SparroWave\EnquiryProducts\Support\StoreUrl::catalogue($product->store) ?? route('public.enquiry-products.index') }}">{{ $product->store->name }}</a>
@if(is_plugin_active('vendor-verified-badge')){!! \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::renderBadges($product->store, ['size' => 13]) !!}@elseif($product->store->is_verified)<span class="ep-verified" title="{{ __('plugins/enquiry-products::messages.verified_seller') }}">✓</span>@endif
</p>
@endif
