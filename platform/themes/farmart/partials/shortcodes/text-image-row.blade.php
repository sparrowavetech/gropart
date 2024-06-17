<div class="widget-custom-section-with-image mt-4 mb-2" @if($shortcode->sectioncolor) style="background-color: {!! BaseHelper::clean($shortcode->sectioncolor) !!};" @endif>
    <div class="container-xxxl">
        <div class="row mb-3 section-custom align-items-center">
            @if($shortcode->imgdirection == '0')
                <div class="col-sm-5 ps-0">
                    <img style="@if($shortcode->imageradius) border-radius: {!! BaseHelper::clean($shortcode->imageradius) !!}px; @endif @if($shortcode->imagebordersize) border-width: {!! BaseHelper::clean($shortcode->imagebordersize) !!}px; @endif @if($shortcode->imagebordertype) border-style: {!! BaseHelper::clean($shortcode->imagebordertype) !!}; @endif @if($shortcode->imagebordercolor) border-color: {!! BaseHelper::clean($shortcode->imagebordercolor) !!}; @endif @if($shortcode->imagecolor) background-color: {!! BaseHelper::clean($shortcode->imagecolor) !!}; @endif" class="lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->{'rowimg'}), null, false, RvMedia::getDefaultImage() }}" alt="{{ $shortcode->{'title'} }}" />
                </div>
            @endif
            <div class="col-sm-7">
                <div class="@if($shortcode->contentdirection == '1') text-right @elseif($shortcode->contentdirection == '2') text-center elseif($shortcode->contentdirection == '3') text-justify @else text-left @endif my-5">
                    <div class="widget-header m-0">
                        <h3 @if($shortcode->titlecolor) style="color: {!! BaseHelper::clean($shortcode->titlecolor) !!};" @endif class="@if($shortcode->titletype == 'bold') fw-bold @endif @if($shortcode->titletype == 'normal') fw-normal @endif entry-title">{!! BaseHelper::clean($shortcode->title) !!}</h3>
                    </div>
                    <p class="content mb-2">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
                    @if($shortcode->btnlink) <a @if($shortcode->btncheckbox == 'on') target="_BLANK" @endif href="{!! BaseHelper::clean($shortcode->btnlink) !!}" class="btn btn-primary" style="color: var(--primary-button-color);padding: 10px 50px; font-size: 16px;">{!! BaseHelper::clean($shortcode->btntext) !!}</a> @endif
                </div>
            </div>
            @if($shortcode->imgdirection == '1')
                <div class="col-sm-5 pe-0">
                    <img style="@if($shortcode->imageradius) border-radius: {!! BaseHelper::clean($shortcode->imageradius) !!}px; @endif @if($shortcode->imagebordersize) border-width: {!! BaseHelper::clean($shortcode->imagebordersize) !!}px; @endif @if($shortcode->imagebordertype) border-style: {!! BaseHelper::clean($shortcode->imagebordertype) !!}; @endif @if($shortcode->imagebordercolor) border-color: {!! BaseHelper::clean($shortcode->imagebordercolor) !!}; @endif @if($shortcode->imagecolor) background-color: {!! BaseHelper::clean($shortcode->imagecolor) !!}; @endif" class="lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->{'rowimg'}), null, false, RvMedia::getDefaultImage() }}" alt="{{ $shortcode->{'title'} }}" />
                </div>
            @endif
        </div>
    </div>
</div>
