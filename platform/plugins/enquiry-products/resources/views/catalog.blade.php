<section class="ep-shell ep-catalog" aria-labelledby="ep-title">
    @if($store)<p class="ep-store-heading">{{ __('plugins/enquiry-products::messages.enquiry_catalogue') }} — <strong>{{ $store->name }}</strong></p>@endif
    <div class="ep-toolbar">
        <div>
            <h1 id="ep-title">{{ setting('ep_catalog_title', "Equipment's Enquiry") }}</h1>
            @if(setting('ep_catalog_subtitle'))<p class="ep-catalog-subtitle">{{ setting('ep_catalog_subtitle') }}</p>@endif
        </div>
        <div class="ep-toolbar-controls">
            <label for="ep-sort">{{ __('plugins/enquiry-products::messages.sort_by') }}</label>
            <select id="ep-sort" name="sort" form="ep-catalog-form">
                @foreach(['default' => __('plugins/enquiry-products::messages.default'), 'name_asc' => __('plugins/enquiry-products::messages.name_a_to_z'), 'name_desc' => __('plugins/enquiry-products::messages.name_z_to_a'), 'newest' => __('plugins/enquiry-products::messages.newest')] as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <span>{{ __('plugins/enquiry-products::messages.view') }}</span>
            <button type="button" data-ep-view="grid" aria-label="{{ __('plugins/enquiry-products::messages.grid_view') }}" aria-pressed="true">▦</button>
            <button type="button" data-ep-view="list" aria-label="{{ __('plugins/enquiry-products::messages.list_view') }}" aria-pressed="false">☰</button>
        </div>
    </div>
    <form id="ep-catalog-form" class="ep-catalog-filters" action="{{ $catalogUrl }}" method="get" role="search" data-ep-catalog data-verified="{{ __('plugins/enquiry-products::messages.verified_seller') }}" data-available="{{ __('plugins/enquiry-products::messages.suggestions_available') }}" data-empty="{{ __('plugins/enquiry-products::messages.no_matching_equipment') }}" data-unavailable="{{ __('plugins/enquiry-products::messages.suggestions_unavailable') }}" data-suggestions="{{ $suggestionsUrl }}">
        <div class="ep-search-row">
            <select name="category" aria-label="{{ __('plugins/enquiry-products::messages.search_category') }}">
                <option value="">{{ __('plugins/enquiry-products::messages.all_categories') }}</option>
                @foreach($categories as $item)<option value="{{ $item->id }}" @selected($category === (int) $item->id)>{{ $item->enquiry_label }}</option>@endforeach
            </select>
            <div class="ep-autocomplete">
                <input id="ep-search" aria-label="{{ __('plugins/enquiry-products::messages.search_equipment_by_name') }}" type="search" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('plugins/enquiry-products::messages.search_equipment_by_name_76f3f') }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ep-suggestions">
                <span class="ep-search-status" role="status" aria-live="polite"></span>
            </div>
            <button class="ep-search-submit" type="submit" aria-label="{{ __('plugins/enquiry-products::messages.search_equipment') }}"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 6 6"/></svg></button>
            <div id="ep-suggestions" class="ep-suggestions" role="listbox" aria-label="{{ __('plugins/enquiry-products::messages.matching_equipment') }}" hidden></div>
        </div>
    </form>
    <div class="ep-catalog-layout">
        <aside class="ep-sidebar" aria-label="{{ __('plugins/enquiry-products::messages.product_categories') }}">
            <h2>{{ __('plugins/enquiry-products::messages.product_categories_f8ea0') }}</h2>
            @include('plugins/enquiry-products::partials.category-tree', ['parent' => 0, 'visited' => []])
            @if($search !== '' || $category)<a class="ep-clear" href="{{ $catalogUrl }}">{{ __('plugins/enquiry-products::messages.clear_filters') }}</a>@endif
        </aside>
        <div class="ep-results">
            @include('plugins/enquiry-products::partials.products')
            <div class="ep-pagination">{{ $products->links() }}</div>
        </div>
    </div>
    <script src="{{ asset('vendor/core/plugins/enquiry-products/catalog.js') }}?v=2" defer></script>
</section>
