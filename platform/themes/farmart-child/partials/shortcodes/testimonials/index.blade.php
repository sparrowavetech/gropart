@php
    $style = data_get($shortcode, 'style', 'style-1');
@endphp

@if ($testimonials->count())
    @switch($style)
        @case('style-2')
        @case('style-3')
        @case('style-4')
            {!! Theme::partial('shortcodes.testimonials.' . $style, compact('shortcode', 'testimonials')) !!}
            @break
        @default
            {!! Theme::partial('shortcodes.testimonials.style-1', compact('shortcode', 'testimonials')) !!}
    @endswitch
@endif
