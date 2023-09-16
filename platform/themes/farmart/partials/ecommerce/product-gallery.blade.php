<div class="product-gallery product-gallery--with-images row">
    <div class="product-gallery__wrapper">
        @forelse ($productImages as $img)
            <div class="product-gallery__image item">
                <a
                    class="img-fluid-eq"
                    href="{{ RvMedia::getImageUrl($img) }}"
                >
                    <div class="img-fluid-eq__dummy"></div>
                    <div class="img-fluid-eq__wrap">
                        <img
                            class="mx-auto"
                            data-lazy="{{ RvMedia::getImageUrl($img) }}"
                            src="{{ image_placeholder($img) }}"
                            title="{{ $product->name }}"
                        >
                    </div>
                </a>
            </div>
        @empty
            <div class="product-gallery__image item">
                <a
                    class="img-fluid-eq"
                    href="{{ image_placeholder() }}"
                >
                    <div class="img-fluid-eq__dummy"></div>
                    <div class="img-fluid-eq__wrap">
                        <img
                            class="mx-auto"
                            src="{{ image_placeholder() }}"
                            title="{{ $product->name }}"
                        >
                    </div>
                </a>
            </div>
        @endforelse
    </div>
    <div class="product-gallery__variants px-1">
        @forelse ($productImages as $img)
            <div class="item">
                <div class="border p-1 m-1">
                    <img
                        class="lazyload"
                        data-src="{{ RvMedia::getImageUrl($img, 'thumb') }}"
                        src="{{ image_placeholder($img, 'thumb') }}"
                        title="{{ $product->name }}"
                    >
                </div>
            </div>
        @empty
            <div class="item">
                <div class="border p-1 m-1">
                    <img
                        src="{{ image_placeholder() }}"
                        title="{{ $product->name }}"
                    >
                </div>
            </div>
        @endforelse
    </div>
</div>
