@php
    /**
     * Render bundles as product-like cards (Shofy-compatible markup).
     * Variables: $bundles (Collection), $title, $subtitle, $layout
     */
    $bundles = $bundles ?? collect();
    $groups = $groups ?? [];
    $title = $title ?? '';
    $subtitle = $subtitle ?? '';
    $layout = $layout ?? 'grid';

    $bundleUrl = function ($bundle) {
        try {
            return route('product-bundles.show', $bundle->slug);
        } catch (Throwable $e) {
            return url('bundles/' . ltrim((string) $bundle->slug, '/'));
        }
    };

    $bundleImage = function ($bundle) {
        $image = trim((string) ($bundle->image ?? ''));
        if ($image !== '') {
            return $image;
        }

        // Fallback to first product image inside the bundle so cards look consistent with product groups.
        $fallback = data_get($bundle, 'items.0.product.image')
            ?: data_get($bundle, 'items.0.product.original_product.image')
            ?: data_get($bundle, 'groups.0.items.0.product.image')
            ?: data_get($bundle, 'groups.0.items.0.product.original_product.image')
            ?: '';

        return (string) $fallback;
    };

    $bundlePriceText = function ($bundle) {
        $priceText = null;

        if ($bundle) {
            try {
                $priceText = pb_format_price(pb_bundle_total_price($bundle));
            } catch (Throwable $e) {
                $priceText = null;
            }
        }

        if ($priceText && (string) ($bundle->type ?? '') === 'mix') {
            return trans('plugins/product-bundles::bundles.front.from_price', ['price' => $priceText]);
        }

        return $priceText;
    };

    $cartAddUrl = null;
    try {
        $cartAddUrl = route('public.cart.add-to-cart');
    } catch (Throwable $e) {
        $cartAddUrl = null;
    }
@endphp

{{--
  NOTE:
  Shortcodes are rendered inside page content. Depending on the theme/editor, external <link> tags may not be applied
  (or assets may not be published yet). We keep the normal asset loader, but also add a lightweight inline CSS fallback
  so the grid + hover remain correct.
--}}
@once
    {!! app('product-bundles')->assets() !!}
@endonce

<style>
  .pb-container{width:100%;max-width:1320px;margin-left:auto;margin-right:auto;padding-left:12px;padding-right:12px}
  .pb-groups{margin-top:14px}
  .pb-groups-header{margin-bottom:12px}
  .pb-groups-title{margin:0 0 4px 0;font-size:20px;font-weight:700}
  .pb-groups-subtitle{margin:0;color:rgba(0,0,0,.65);font-size:14px}
  .pb-groups-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:24px}
  .pb-groups-col{min-width:0}
  .pb-groups-columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
  .pb-group-column{border:1px solid rgba(0,0,0,.08);border-radius:14px;background:#fff;padding:14px}
  .pb-group-column__title{margin:0 0 10px 0;font-size:16px;font-weight:700}
  .pb-group-column__list{display:flex;flex-direction:column;gap:10px}
  .pb-bundle-mini{display:grid;grid-template-columns:56px 1fr;gap:10px;align-items:center;padding:8px;border:1px solid rgba(0,0,0,.08);border-radius:12px;background:#f8fafc;text-decoration:none;color:inherit}
  .pb-bundle-mini:hover{background:#f1f5f9;border-color:rgba(0,0,0,.16)}
  .pb-bundle-mini__thumb{width:56px;height:56px;border-radius:10px;overflow:hidden;background:#fff;border:1px solid rgba(0,0,0,.08);display:inline-flex;align-items:center;justify-content:center}
  .pb-bundle-mini__thumb img{width:100%;height:100%;object-fit:cover;display:block}
  .pb-bundle-mini__body{min-width:0}
  .pb-bundle-mini__name{font-weight:600;font-size:13px;line-height:1.3}
  .pb-bundle-mini .product-price{background:transparent;border:0;padding:0;margin:4px 0 0;gap:6px;justify-content:flex-start;font-size:12px;color:rgba(0,0,0,.65)}
  .pb-bundle-mini .product-price .fw-bold{font-size:12px;color:#111827}
  @media (max-width:1199.98px){.pb-groups-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media (max-width:767.98px){.pb-groups-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}}
  @media (max-width:479.98px){.pb-groups-grid{grid-template-columns:1fr}}

  .pb-bundle-item{border:1px solid rgba(0,0,0,.08);border-radius:14px;background:#fff;overflow:hidden;height:100%}
  .pb-bundle-thumb{position:relative;overflow:hidden}
  .pb-bundle-thumb-link{display:block}
  .pb-bundle-img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}

  .pb-bundle-actions{position:absolute;right:12px;top:12px;display:flex;flex-direction:column;gap:8px;opacity:0;transform:translateX(10px);transition:opacity .18s ease,transform .18s ease;z-index:3}
  .pb-action-btn{width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:12px;text-decoration:none;color:inherit;box-shadow:0 10px 30px rgba(0,0,0,.08)}
  .pb-bundle-add{position:absolute;left:0;right:0;bottom:0;transform:translateY(105%);transition:transform .18s ease;z-index:2}
  .pb-bundle-add-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 10px;text-decoration:none;background:#0b1a27;color:#fff;font-weight:600}
  .pb-bundle-item:hover .pb-bundle-actions{opacity:1;transform:translateX(0)}
  .pb-bundle-item:hover .pb-bundle-add{transform:translateY(0)}
  .pb-bundle-body{padding:12px 14px 14px}
  .pb-bundle-name{font-weight:700;font-size:15px;line-height:1.3}
  .pb-bundle-name-link{text-decoration:none;color:inherit}
</style>

<section class="pb-groups pb-layout-{{ e($layout) }}{{ $layout === 'columns' ? ' tp-product-sm-area' : '' }}" data-add-url="{{ route('product-bundles.add_to_cart') }}">
    <div class="{{ $layout === 'columns' ? 'container' : 'pb-container' }}">
        @if ($layout !== 'columns' && ((string) $title !== '' || (string) $subtitle !== ''))
            <div class="pb-groups-header">
                <div class="pb-groups-heading">
                        @if ((string) $title !== '')
                            <h3 class="pb-groups-title">{{ $title }}</h3>
                        @endif
                        @if ((string) $subtitle !== '')
                            <p class="pb-groups-subtitle">{{ $subtitle }}</p>
                        @endif
                </div>
            </div>
        @endif

        @if ($layout === 'columns' && ! empty($groups))
            <div class="row g-4">
                @foreach ($groups as $group)
                    @php
                        $groupTitle = (string) ($group['title'] ?? '');
                        $groupBundles = $group['bundles'] ?? collect();
                        $columnSize = count($groups) > 0 ? (int) floor(12 / count($groups)) : 12;
                        if ($columnSize <= 0) {
                            $columnSize = 12;
                        }
                    @endphp

                    <div class="col-xl-{{ $columnSize }} col-md-6">
                        <div class="tp-product-sm-list mb-50">
                            <div class="tp-section-title-wrapper">
                                <h3 class="section-title tp-section-title tp-section-title-sm">
                                    {{ $groupTitle }}
                                    @if (class_exists('Theme'))
                                        {!! Theme::partial('section-title-shape') !!}
                                    @endif
                                </h3>
                            </div>

                            <div class="tp-product-sm-wrapper">
                                @foreach ($groupBundles as $bundle)
                                    @php
                                        $url = $bundleUrl($bundle);
                                        $img = $bundleImage($bundle);
                                        $priceText = $bundlePriceText($bundle);
                                    @endphp

                                    <div class="tp-product-sm-item d-flex align-items-center">
                                        <div class="tp-product-thumb mr-25 fix">
                                            <a href="{{ $url }}" title="{{ $bundle->name }}">
                                                @if (class_exists('RvMedia'))
                                                    {!! RvMedia::image($img, $bundle->name, 'thumb') !!}
                                                @else
                                                    <img src="{{ e($img) }}" alt="{{ e($bundle->name) }}">
                                                @endif
                                            </a>
                                        </div>
                                        <div class="tp-product-sm-content">
                                            <h3 class="tp-product-title">
                                                <a href="{{ $url }}">{{ $bundle->name }}</a>
                                            </h3>
                                            <div class="tp-product-price-review">
                                                <div class="tp-product-price-wrapper">
                                                    <span class="tp-product-price new-price">
                                                        {{ $priceText ?: trans('plugins/product-bundles::bundles.front.view_details_for_price') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="pb-groups-grid">
                @foreach ($bundles as $bundle)
                    @php
                        $url = $bundleUrl($bundle);
                        $img = $bundleImage($bundle);
                    @endphp

                    <div class="pb-groups-col">
                        <div class="pb-bundle-item" data-bundle-id="{{ $bundle->id }}">
                            <div class="pb-bundle-thumb">
                                <a class="pb-bundle-thumb-link" title="{{ $bundle->name }}" href="{{ $url }}">
                                    @if (class_exists('RvMedia'))
                                        {!! RvMedia::image($img, $bundle->name, 'product-thumb', attributes: ['class' => 'pb-bundle-img']) !!}
                                    @else
                                        <img class="pb-bundle-img" src="{{ e($img) }}" alt="{{ e($bundle->name) }}">
                                    @endif
                                </a>

                                <div class="pb-bundle-actions" aria-label="{{ __('Actions') }}">
                                    <a class="pb-action-btn" href="{{ $url }}" title="{{ __('View') }}" aria-label="{{ __('View') }}">
                                        <x-core::icon name="ti ti-eye"/>
                                    </a>
                                    @if ($bundle->type === 'fixed' && $cartAddUrl && $bundle->ecommerce_product_id)
                                        <button
                                            type="button"
                                            class="pb-action-btn"
                                            data-bb-toggle="add-to-cart"
                                            data-url="{{ $cartAddUrl }}"
                                            data-id="{{ (int) $bundle->ecommerce_product_id }}"
                                            title="{{ __('Add To Cart') }}"
                                            aria-label="{{ __('Add To Cart') }}"
                                        >
                                            <x-core::icon name="ti ti-shopping-cart"/>
                                        </button>
                                    @else
                                        <a class="pb-action-btn" href="{{ $url }}" title="{{ __('Select Options') }}" aria-label="{{ __('Select Options') }}">
                                            <x-core::icon name="ti ti-arrows-right-left"/>
                                        </a>
                                    @endif
                                </div>

                                <div class="pb-bundle-add">
                                    @if ($bundle->type === 'fixed' && $cartAddUrl && $bundle->ecommerce_product_id)
                                        <button
                                            type="button"
                                            class="pb-bundle-add-btn"
                                            data-bb-toggle="add-to-cart"
                                            data-url="{{ $cartAddUrl }}"
                                            data-id="{{ (int) $bundle->ecommerce_product_id }}"
                                        >
                                            <x-core::icon name="ti ti-shopping-cart"/>
                                            <span>{{ __('Add To Cart') }}</span>
                                        </button>
                                    @else
                                        <a class="pb-bundle-add-btn" href="{{ $url }}">
                                            <x-core::icon name="ti ti-shopping-cart"/>
                                            <span>{{ __('Select Options') }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="pb-bundle-body">
                                <div class="pb-bundle-name">
                                    <a class="pb-bundle-name-link" title="{{ $bundle->name }}" href="{{ $url }}">{{ $bundle->name }}</a>
                                </div>

                                @include('plugins/product-bundles::front.includes.bundle-price', ['bundle' => $bundle])
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
