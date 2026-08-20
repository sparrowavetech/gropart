@php
    $categories = get_product_categories([
        'condition' => ['status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED],
    ])->filter(function ($category) {
        return empty($category->is_enquiry);
    });
@endphp
<div class="widget-featured-brands all-brands py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h3 class="col-auto mb-0 py-2">{!! BaseHelper::clean($shortcode->title ?: __('All Categories')) !!}</h3>
                </div>
                <div class="featured-brands__body arrows-top-right row row-cols-xl-6 row-cols-lg-3 row-cols-md-3 row-cols-3 g-0">
                @foreach ($categories as $category)
                    <div class="featured-brand-item col text-center">
                        <div class="brand-item-body mx-2 py-2">
                            <a class="py-3" href="{{ $category->url }}">
                                <div class="brand__thumb mb-2 img-fluid-eq">
                                    <div class="img-fluid-eq__dummy"></div>
                                    <div class="img-fluid-eq__wrap">
                                        <img
                                            class="lazyload mx-auto"
                                            data-src="{{ RvMedia::getImageUrl($category->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                            src="{{ image_placeholder($category->image, 'small') }}"
                                            alt="{{ $category->name }}"
                                        />
                                    </div>
                                </div>
                                <div class="brand__text py-1">
                                    <h6 class="fw-bold text-secondary brand__name">
                                        {{ $category->name }}
                                    </h6>
                                    @if($category->description)
                                    <div class="fw-bold brand__desc">
                                        <div>
                                            {{ BaseHelper::clean(Str::limit($category->description, 150)) }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
