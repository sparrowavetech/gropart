<section class="ep-shell ep-product-page">
    <div class="ep-product-overview">
        <div class="ep-product-gallery" style="display:block">
            @include(EcommerceHelper::viewPath('includes.product-gallery'), [
                'productImages' => collect([$product->image, ...($product->images ?: [])])->filter()->unique()->values()->all(),
            ])
        </div>
        <article class="ep-product-summary">
            <h1>{{ $product->name }}</h1>
            @if($product->brand)<p class="ep-product-brand">{{ __('plugins/enquiry-products::messages.brand') }}: <a href="{{ $product->brand->url }}">{{ $product->brand->name }}</a></p>@endif
            <hr>
            @include('plugins/enquiry-products::partials.seller')
            <div class="ep-short-description">{!! BaseHelper::clean($product->description) !!}</div>
            @if($product->tags->isNotEmpty())<p class="ep-product-tags">{{ __('plugins/enquiry-products::messages.tags') }}: @foreach($product->tags as $tag)<span>{{ $tag->name }}</span>{{ !$loop->last ? ', ' : '' }}@endforeach</p>@endif
            <a class="ep-enquire-button" href="{{ route('public.enquiry-products.form', $product->slugable->key) }}">{{ __('plugins/enquiry-products::messages.enquiry_now') }}</a>
            <div class="ep-share"><strong>{{ __('plugins/enquiry-products::messages.share_this_product') }}</strong>
                @php($shareUrl = urlencode(route('public.enquiry-products.show', $product->slugable->key)))
                <a class="ep-share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a class="ep-share-x" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer">X (Twitter)</a>
                <a class="ep-share-pinterest" href="https://pinterest.com/pin/create/button/?url={{ $shareUrl }}&media={{ urlencode(RvMedia::getImageUrl($product->image)) }}" target="_blank" rel="noopener noreferrer">Pinterest</a>
                <a class="ep-share-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            </div>
        </article>
    </div>
    <section class="ep-product-description"><h2>{{ __('plugins/enquiry-products::messages.description') }}</h2><div>{!! BaseHelper::clean($product->content) !!}</div></section>
    @if(is_plugin_active('marketplace') && $product->store)
        <section class="ep-partner"><h2>{{ __('plugins/enquiry-products::messages.store_partner_info') }}</h2>
            <div class="ep-partner-header">
                <img src="{{ RvMedia::getImageUrl($product->store->logo, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->store->name }}">
                <div>@include('plugins/enquiry-products::partials.seller')</div>
                @if($product->store->created_at)<small>{{ __('plugins/enquiry-products::messages.started_from') }}: {{ $product->store->created_at->format('M d, Y') }}</small>@endif
            </div>
            <div class="ep-partner-description">{!! BaseHelper::clean($product->store->description) !!}</div>
        </section>
    @endif
</section>
