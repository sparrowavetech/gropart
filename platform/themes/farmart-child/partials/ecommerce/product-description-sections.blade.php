<div class="product-description-section py-4">
    @if (EcommerceHelper::isReviewEnabled() && $product->reviews_count > 0)
        <section class="mb-5">
            <h4 class="pb-3 border-bottom">{{ __('Reviews') }} ({{ $product->reviews_count }})</h4>
            @include('plugins/ecommerce::themes.includes.reviews')
        </section>
    @endif
    <section class="mb-5">
        <h4 class="pb-3 border-bottom">{{ __('Description') }}</h4>
        <div class="ck-content">{!! BaseHelper::clean($product->content) !!}</div>
        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $product) !!}
    </section>
    @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
        <section class="mb-5">
            <h4 class="pb-3 border-bottom">{{ __('Specification') }}</h4>
            @include(EcommerceHelper::viewPath('includes.product-specification'))
        </section>
    @endif
    @if (is_plugin_active('faq') && count($product->faq_items) > 0)
        <section class="mb-5">
            <h4 class="pb-3 border-bottom">{{ __('Questions & Answers') }}</h4>
            @include('plugins/ecommerce::themes.includes.product-faqs', ['faqs' => $product->faq_items])
        </section>
    @endif
    @if (is_plugin_active('marketplace') && $product->store_id)
        <section class="mb-5">
            <h4 class="pb-3 border-bottom">{{ __('Vendor Info') }}</h4>
            @include(Theme::getThemeNamespace() . '::views.marketplace.includes.info-box', ['store' => $product->store])
        </section>
    @endif
</div>
