@php
    $title = $shortcode->title;
    $subtitle = $shortcode->subtitle;
    $btntext = $shortcode->btntext;
    $btnlink = $shortcode->btnlink;
    $rowimg = $shortcode->rowimg;
    $imgdirection = $shortcode->imgdirection ?? 0;
    $titlecolor = $shortcode->titlecolor ?? '#000000';
    $imageradius = is_numeric($shortcode->imageradius) ? ($shortcode->imageradius . 'px') : ($shortcode->imageradius ?: '5px');
    $imagebordercolor = $shortcode->imagebordercolor ?? '#e7e5e5';
    $imagebordersize = is_numeric($shortcode->imagebordersize) ? ($shortcode->imagebordersize . 'px') : ($shortcode->imagebordersize ?: '1px');
    $imagebordertype = $shortcode->imagebordertype ?? 'solid';
    $imagecolor = $shortcode->imagecolor ?? '#f7f7f7';
@endphp

<div class="widget-text-image-row py-4">
    <div class="container-xxxl">
        <div class="row align-items-center @if($imgdirection == 1) flex-row-reverse @endif">
            <div class="col-md-5 col-12 mb-3 mb-md-0 text-center">
                <div class="text-image-row__img-wrap p-2 text-center" style="background: {{ $imagecolor }}; border: {{ $imagebordersize }} {{ $imagebordertype }} {{ $imagebordercolor }}; border-radius: {{ $imageradius }};">
                    <img class="img-fluid" style="border-radius: {{ $imageradius }}; max-height: 380px; object-fit: contain;" src="{{ RvMedia::getImageUrl($rowimg, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $title }}">
                </div>
            </div>
            <div class="col-md-7 col-12">
                <div class="text-image-row__content px-md-4 py-2">
                    <h3 class="text-image-row__title mb-3 fw-bold" style="color: {{ $titlecolor }}; font-size: 1.5rem;">{{ $title }}</h3>
                    <div class="text-image-row__subtitle mb-4 text-secondary fs-6 lh-base" style="font-size: 1rem; line-height: 1.7;">
                        {!! BaseHelper::clean($subtitle) !!}
                    </div>
                    @if($btntext && $btnlink)
                        <a href="{{ url($btnlink) }}" class="btn btn-primary px-4 py-2 fw-bold text-uppercase" style="background-color: var(--primary-color); border-color: var(--primary-color); border-radius: 4px;">
                            {{ $btntext }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
