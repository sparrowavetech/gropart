@php
    /**
     * Farmart-styled vendor vacation notice.
     *
     * Used in two places:
     *   - The store banner (views/marketplace/includes/info-box.blade.php)
     *   - The product detail page, injected via the ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML
     *     filter — which resolves this file through MarketplaceHelper::viewPath(), so this
     *     theme override takes precedence over the plugin's generic Bootstrap version.
     *
     * Inputs:
     *   $store  \Botble\Marketplace\Models\Store  (required)
     *
     * Uses the theme's Linearicons icon font (icon-leaf) and Farmart fresh/grocery
     * green palette so it reads as a friendly "temporarily away" status.
     */
@endphp

@if (! empty($store) && method_exists($store, 'isOnVacation') && $store->isOnVacation())
    <div class="store-vacation-notice" role="status">
        <span class="store-vacation-notice__icon" aria-hidden="true">
            <i class="icon icon-leaf"></i>
        </span>
        <span class="store-vacation-notice__body">
            <strong class="store-vacation-notice__title">
                {{ trans('plugins/marketplace::store.forms.vacation_badge') }}
            </strong>
            <span class="store-vacation-notice__text">
                @if (! empty($store->vacation_message))
                    {{ $store->vacation_message }}
                @else
                    {{ trans('plugins/marketplace::store.forms.vacation_default_notice', ['store' => $store->name]) }}
                @endif
            </span>
        </span>
    </div>
@endif
