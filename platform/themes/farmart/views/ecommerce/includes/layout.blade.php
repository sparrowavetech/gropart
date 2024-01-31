<div class="col-auto">
    <div class="catalog-toolbar__view d-flex align-items-center">
        <div class="text d-none d-lg-block">{{ __('View') }}</div>
        <div class="toolbar-view__icon">
            <a
                class="grid @if (request()->input('layout') != 'list') active @endif"
                data-layout="grid"
                data-target=".shop-products-listing"
                data-class-remove="row-cols-4 shop-products-listing__list"
                data-class-add="row-cols-xl-4 row-cols-lg-4 row-cols-md-2 row-cols-1"
                href="#"
            >
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-grid"
                            xlink:href="#svg-icon-grid"
                        ></use>
                    </svg>
                </span>
            </a>
            <a
                class="list @if (request()->input('layout') == 'list') active @endif"
                data-layout="list"
                data-target=".shop-products-listing"
                data-class-add="row-cols-2 shop-products-listing__list"
                data-class-remove="row-cols-xl-4 row-cols-lg-4 row-cols-md-2 row-cols-1"
                href="#"
            >
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-list"
                            xlink:href="#svg-icon-list"
                        ></use>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</div>
