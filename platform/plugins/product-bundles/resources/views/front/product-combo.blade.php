@php
    /**
     * @var \Botble\ProductBundles\Models\Bundle $bundle
     */
    $bundle = $bundle ?? null;
    if (! $bundle) {
        return;
    }

    $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
        ? 'Botble\\Ecommerce\\Models\\Product'
        : 'App\\Models\\Product';
@endphp

@once
    {!! app('product-bundles')->assets() !!}
@endonce

<div class="pb-wrap pb-wrap-product" data-add-url="{{ route('product-bundles.add_to_cart') }}">
    <div class="pb-title">{{ trans('plugins/product-bundles::bundles.front.contains_title') }}</div>

    <div class="pb-card" data-bundle-id="{{ $bundle->id }}" data-bundle-type="{{ $bundle->type }}">
        @include('plugins/product-bundles::front.includes.bundle-price', ['bundle' => $bundle])

        @if ($bundle->type === 'fixed')
            <div class="pb-items pb-items--compact">
                @foreach ($bundle->items as $it)
                    @php
                        $p = $it->variation_id ? $productClass::query()->find($it->variation_id) : $it->product;
                        $pDetail = $p->original_product ?? $p;
                        $pName = $p->name ?? ($it->product->name ?? ('#' . $it->product_id));
                        $pImage = pb_image_url($p->image ?? ($it->product->image ?? null));
                        $pUrl = $pDetail?->url ?: null;
                    @endphp
                    <div class="pb-item pb-item--compact">
                        <a class="pb-item__thumb" href="{{ $pUrl ?: 'javascript:void(0);' }}">
                            <img src="{{ $pImage }}" alt="{{ $pName }}" width="44" height="44" loading="lazy">
                        </a>
                        <span class="pb-item__meta">
                            <a class="pb-item__link" href="{{ $pUrl ?: 'javascript:void(0);' }}">{{ $pName }}</a>
                        </span>
                        <span class="pb-item__qty">x{{ $it->quantity }}</span>
                    </div>
                @endforeach
            </div>
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
                                    $pDetail = $p->original_product ?? $p;
                                    $pName = $p->name ?? ($opt->product->name ?? ('#' . $opt->product_id));
                                    $pImage = pb_image_url($p->image ?? ($opt->product->image ?? null));
                                    $pUrl = $pDetail?->url ?: null;
                                @endphp
                                <a class="pb-option pb-option--compact" href="{{ $pUrl ?: 'javascript:void(0);' }}">
                                    <span class="pb-option__thumb">
                                        <img src="{{ $pImage }}" alt="{{ $pName }}" width="36" height="36" loading="lazy">
                                    </span>
                                    <span class="pb-option__name">{{ $pName }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="pb-msg" aria-live="polite"></div>
    </div>
</div>
