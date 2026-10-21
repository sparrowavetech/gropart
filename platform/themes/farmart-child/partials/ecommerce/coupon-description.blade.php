@php
    use Botble\Ecommerce\Enums\DiscountTargetEnum;
    use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;

    $target = $discount->target;
    $type = $discount->type_option;
    $value = $discount->value;
    $hasStructuredItems = false;
    $targetItems = collect();
    $targetLabel = __('applicable items');

    // 1. Determine discount amount/percentage prefix
    $valuePrefix = '';
    if ($type === DiscountTypeOptionEnum::PERCENTAGE) {
        $valuePrefix = __('Discount :percentage%', ['percentage' => $value]);
    } elseif ($type === DiscountTypeOptionEnum::SHIPPING) {
        $valuePrefix = __('Free shipping');
        if ($value) {
            $valuePrefix .= ' ' . __('when shipping fee less than or equal :amount', ['amount' => format_price($value)]);
        }
    } elseif ($type === DiscountTypeOptionEnum::SAME_PRICE) {
        $valuePrefix = __('Same fee :amount', ['amount' => format_price($value)]);
    } else {
        $valuePrefix = __('Discount :amount', ['amount' => format_price($value)]);
    }

    // 2. Extract targeted items if target is specific products, variants, collections, or categories
    if ($target === DiscountTargetEnum::SPECIFIC_PRODUCT) {
        $prods = $discount->products()->where('is_variation', false)->get();
        if ($prods->isEmpty()) {
            $prods = $discount->products;
        }
        $targetItems = $prods->unique('id')->map(function ($p) {
            $orig = $p->is_variation && $p->original_product ? $p->original_product : $p;
            return [
                'name' => $orig->name ?: $p->name,
                'url' => $orig->url ?: $p->url,
            ];
        })->filter(fn($it) => !empty($it['name']) && !empty($it['url']))->unique('url');
        $hasStructuredItems = $targetItems->isNotEmpty();
        $targetLabel = $targetItems->count() > 1 ? __('products') : __('product');
    } elseif ($target === DiscountTargetEnum::PRODUCT_VARIANT) {
        $variants = $discount->products()->where('is_variation', true)->with('variationInfo.configurableProduct')->get();
        $targetItems = $variants->map(function ($v) {
            $orig = $v->original_product;
            $attrs = $v->variation_attributes ? ' (' . $v->variation_attributes . ')' : '';
            return [
                'name' => ($orig ? $orig->name : $v->name) . $attrs,
                'url' => $orig ? $orig->url : $v->url,
            ];
        })->filter(fn($it) => !empty($it['name']) && !empty($it['url']))->unique('url');
        $hasStructuredItems = $targetItems->isNotEmpty();
        $targetLabel = __('product variant(s)');
    } elseif ($target === DiscountTargetEnum::PRODUCT_COLLECTIONS) {
        $collections = $discount->productCollections;
        $targetItems = $collections->map(function ($c) {
            return [
                'name' => $c->name,
                'url' => $c->url,
            ];
        })->filter(fn($it) => !empty($it['name']) && !empty($it['url']))->unique('url');
        $hasStructuredItems = $targetItems->isNotEmpty();
        $targetLabel = __('collection(s)');
    } elseif ($target === DiscountTargetEnum::PRODUCT_CATEGORIES) {
        $categories = $discount->productCategories;
        $targetItems = $categories->map(function ($c) {
            return [
                'name' => $c->name,
                'url' => $c->url,
            ];
        })->filter(fn($it) => !empty($it['name']) && !empty($it['url']))->unique('url');
        $hasStructuredItems = $targetItems->isNotEmpty();
        $targetLabel = __('category(ies)');
    }

    $customDescription = $discount->description;
    $defaultDescription = get_discount_description($discount);
    $textDescription = $customDescription ?: $defaultDescription;
@endphp

<div class="checkout__coupon-item-description-wrapper">
    @if ($hasStructuredItems)
        <div class="coupon-desc-lead mb-1">
            <span class="fw-semibold">{{ $valuePrefix }}</span>
            <span class="coupon-target-info">{{ __('for :count :label:', ['count' => $targetItems->count(), 'label' => $targetLabel]) }}</span>
        </div>

        @php
            $visibleCount = 2;
            $visibleItems = $targetItems->slice(0, $visibleCount);
            $hiddenItems = $targetItems->slice($visibleCount);
        @endphp

        <div class="coupon-items-list">
            @foreach ($visibleItems as $item)
                <div class="coupon-item-row d-flex align-items-start mb-1">
                    <span class="coupon-bullet me-1">&bull;</span>
                    <div class="flex-grow-1">
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="coupon-product-link text-decoration-none" title="{{ __('Explore :name', ['name' => $item['name']]) }}">
                            <span>{{ $item['name'] }}</span>
                            <span class="coupon-explore-tag ms-1">{{ __('Explore') }} &nearr;</span>
                        </a>
                    </div>
                </div>
            @endforeach

            @if ($hiddenItems->isNotEmpty())
                <div class="coupon-items-more" style="display: none;">
                    @foreach ($hiddenItems as $item)
                        <div class="coupon-item-row d-flex align-items-start mb-1">
                            <span class="coupon-bullet me-1">&bull;</span>
                            <div class="flex-grow-1">
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="coupon-product-link text-decoration-none" title="{{ __('Explore :name', ['name' => $item['name']]) }}">
                                    <span>{{ $item['name'] }}</span>
                                    <span class="coupon-explore-tag ms-1">{{ __('Explore') }} &nearr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="coupon-toggle-wrapper mt-1">
                    <button type="button" class="btn-toggle-coupon-desc" data-count="{{ $hiddenItems->count() }}">
                        <span class="toggle-text">{{ __('Show :count more :label...', ['count' => $hiddenItems->count(), 'label' => $targetLabel]) }}</span>
                        <svg class="toggle-chevron ms-1" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                </div>
            @endif
        </div>
    @elseif (mb_strlen($textDescription) > 120)
        @php
            $preview = mb_substr($textDescription, 0, 110);
            $rest = mb_substr($textDescription, 110);
        @endphp
        <div class="coupon-text-container">
            <span class="coupon-text-preview">{!! BaseHelper::clean($preview) !!}</span>
            <span class="coupon-text-more" style="display: none;">{!! BaseHelper::clean($rest) !!}</span>
            <div class="coupon-toggle-wrapper mt-1">
                <button type="button" class="btn-toggle-coupon-text">
                    <span class="toggle-text">{{ __('Show more...') }}</span>
                    <svg class="toggle-chevron ms-1" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
            </div>
        </div>
    @else
        <div class="coupon-text-simple">
            {!! BaseHelper::clean($textDescription) !!}
        </div>
    @endif
</div>
