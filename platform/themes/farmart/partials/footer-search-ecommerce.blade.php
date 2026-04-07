<x-plugins-ecommerce::fronts.ajax-search class="form--quick-search bb-form-quick-search w-100">
    <div class="search-inner-content">
        <div class="text-search">
            <div class="search-wrapper">
                <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="search-field input-search-product" />
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
            </div>
            <a
                class="close-search-panel close-toggle--sidebar"
                href="#"
                aria-label="Search"
            >
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-times"
                            xlink:href="#svg-icon-times"
                        ></use>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</x-plugins-ecommerce::fronts.ajax-search>
