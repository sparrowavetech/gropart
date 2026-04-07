<x-plugins-ecommerce::fronts.ajax-search class="form--quick-search">
    <div
        class="form-group--icon"
        style="display: none"
    >
        <div class="product-category-label">
            <label for="product-category-select" class="text">{{ __('All Categories') }}</label>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-chevron-down"
                        xlink:href="#svg-icon-chevron-down"
                    ></use>
                </svg>
            </span>
        </div>
        <x-plugins-ecommerce::fronts.ajax-search.categories-dropdown
            class="form-control product-category-select"
            id="product-category-select"
        />
    </div>
    <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="form-control input-search-product" />
    <button
        class="btn"
        type="submit"
        aria-label="Submit"
    >
        <span class="svg-icon">
            <svg>
                <use
                    href="#svg-icon-search"
                    xlink:href="#svg-icon-search"
                ></use>
            </svg>
        </span>
    </button>
</x-plugins-ecommerce::fronts.ajax-search>
