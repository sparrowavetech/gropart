<div class="widget-products-with-category py-5 all-product-collections">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-2">
                    <h1 class="mb-0 py-2">{!! BaseHelper::clean($shortcode->title) !!}</h1>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div class="row product-deals-day-body">
                        @foreach ($products as $product)
                            <div class="product-inner col-6 col-md-4 col-lg-2">
                                {!! Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
