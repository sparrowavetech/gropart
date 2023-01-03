@if ($testimonials->count())
    @switch($shortcode->style)
        @case('style-2')
        @case('style-3')
        @case('style-4')
            {!! Theme::partial('shortcodes.testimonials.' . $shortcode->style, compact('shortcode', 'testimonials')) !!}
            @break
        @default
            {!! Theme::partial('shortcodes.testimonials.style-1', compact('shortcode', 'testimonials')) !!}
    @endswitch
@endif
