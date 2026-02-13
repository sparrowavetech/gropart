<div>
    <strong>{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.main_feed_url') }}:</strong>
    <div class="d-flex align-items-center gap-2 mb-3">
        <input type="text" class="form-control" value="{{ $feedUrl }}" readonly>
        <x-core::copy :value="$feedUrl" />
    </div>
    
    <strong>{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.feed_types_info') }}:</strong>
    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <code class="text-break">{{ $allProductsUrl }}</code>
                <div class="small text-muted">{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.all_products') }}</div>
            </div>
            <x-core::copy :value="$allProductsUrl" />
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <code class="text-break">{{ $newProductsUrl }}</code>
                <div class="small text-muted">{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.new_products') }}</div>
            </div>
            <x-core::copy :value="$newProductsUrl" />
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <code class="text-break">{{ $featuredProductsUrl }}</code>
                <div class="small text-muted">{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.featured_products') }}</div>
            </div>
            <x-core::copy :value="$featuredProductsUrl" />
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <code class="text-break">{{ $onSaleProductsUrl }}</code>
                <div class="small text-muted">{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.on_sale_products') }}</div>
            </div>
            <x-core::copy :value="$onSaleProductsUrl" />
        </div>
    </div>
</div>