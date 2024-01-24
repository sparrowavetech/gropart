@php
    Theme::asset()->add('lightgallery-css', 'vendor/core/plugins/ecommerce/libraries/lightgallery/css/lightgallery.min.css');
    Theme::asset()->add('slick-css', 'vendor/core/plugins/ecommerce/libraries/slick/slick.css');
    Theme::asset()->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css');
    Theme::asset()->container('footer')->add('lightgallery-js', 'vendor/core/plugins/ecommerce/libraries/lightgallery/js/lightgallery.min.js', ['jquery']);
    Theme::asset()->container('footer')->add('slick-js', 'vendor/core/plugins/ecommerce/libraries/slick/slick.min.js', ['jquery']);
    Theme::asset()->container('footer')->add('front-ecommerce-js', 'vendor/core/plugins/ecommerce/js/front-ecommerce.js', ['jquery', 'lightgallery-js', 'slick-js']);
@endphp

<div class="bb-product-gallery-images">
    @foreach ($productImages as $image)
        <a href="{{ RvMedia::getImageUrl($image) }}">
            {{ RvMedia::image($image, $product->name) }}
        </a>
    @endforeach
</div>
<div class="bb-product-thumbnails">
    @foreach ($productImages as $image)
        <div>
            {{ RvMedia::image($image, $product->name, 'thumb') }}
        </div>
    @endforeach
</div>
