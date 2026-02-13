@php
    /** @var \Botble\ProductBundles\Models\Bundle $bundle */
    $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
        ? 'Botble\\Ecommerce\\Models\\Product'
        : 'App\\Models\\Product';

    $cartAddUrl = null;
    try {
        $cartAddUrl = route('public.cart.add-to-cart');
    } catch (Throwable $e) {
        $cartAddUrl = null;
    }
@endphp

{!! app('product-bundles')->assets() !!}

<div class="pb-bundle-page">
    <div class="pb-bundle-hero">
        @php $img = pb_image_url($bundle->image); @endphp
        @if ($img)
            <div class="pb-bundle-hero__thumb"><img src="{{ $img }}" alt="{{ e($bundle->name) }}"></div>
        @endif

        <div class="pb-bundle-hero__content">
            <h1 class="pb-bundle-hero__title">{{ $bundle->name }}</h1>
            @if ($bundle->description)
                <div class="pb-bundle-hero__desc">{{ $bundle->description }}</div>
            @endif
        </div>
    </div>

    <div class="pb-wrap" data-add-url="{{ route('product-bundles.add_to_cart') }}">
        <div class="pb-card" data-bundle-id="{{ $bundle->id }}" data-bundle-type="{{ $bundle->type }}">
            @if ($bundle->type === 'fixed')
                @php
                    if (! $bundle->ecommerce_product_id) {
                        try { app('product-bundles')->syncBundleProduct($bundle); $bundle->refresh(); } catch (Throwable $e) {}
                    }
                @endphp
                <div class="pb-items">
                    @foreach ($bundle->items as $it)
                        @php
                            $p = $it->variation_id ? $productClass::query()->find($it->variation_id) : $it->product;
                            $pName = $p->name ?? ($it->product->name ?? ('#' . ($it->variation_id ?: $it->product_id)));
                        @endphp
                        <div class="pb-item">
                            <span class="pb-item__name">{{ $pName }}</span>
                            <span class="pb-item__qty">x{{ $it->quantity }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($cartAddUrl && $bundle->ecommerce_product_id)
                    <button
                        type="button"
                        class="pb-btn"
                        data-bb-toggle="add-to-cart"
                        data-url="{{ $cartAddUrl }}"
                        data-id="{{ (int) $bundle->ecommerce_product_id }}"
                    >
                        {{ __('Add To Cart') }}
                    </button>
                @else
                    <button class="pb-btn pb-add-fixed" type="button" data-bundle-id="{{ $bundle->id }}">
                        {{ trans('plugins/product-bundles::bundles.front.add_combo') }}
                    </button>
                @endif
            @else
                <div class="pb-groups">
                    @foreach ($bundle->groups as $g)
                        <div class="pb-group" data-group-id="{{ $g->id }}" data-min="{{ $g->choose_min }}" data-max="{{ $g->choose_max }}">
                            <div class="pb-group__title">
                                {{ $g->name }}
                                <span class="pb-group__hint">
                                    {{ trans('plugins/product-bundles::bundles.front.choose_between', ['min' => $g->choose_min, 'max' => $g->choose_max]) }}
                                </span>
                            </div>

                            <div class="pb-options">
                                @foreach ($g->items as $opt)
                                    @php
                                        $optId = (int) ($opt->variation_id ?: $opt->product_id);
                                        $p = $opt->variation_id ? $productClass::query()->find($opt->variation_id) : $opt->product;
                                        $pName = $p->name ?? ($opt->product->name ?? ('#' . ($opt->variation_id ?: $opt->product_id)));
                                    @endphp
                                    <label class="pb-option">
                                        <input type="checkbox" value="{{ $optId }}">
                                        <span>{{ $pName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="pb-btn pb-add-mix" type="button" data-bundle-id="{{ $bundle->id }}">
                    {{ trans('plugins/product-bundles::bundles.front.add_combo') }}
                </button>
            @endif

            <div class="pb-msg" aria-live="polite"></div>
        </div>
    </div>
</div>
