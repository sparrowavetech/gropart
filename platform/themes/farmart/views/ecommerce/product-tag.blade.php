@php
    Theme::set('pageDescription', $tag->content ? \Botble\Shortcode\Facades\Shortcode::compile($tag->content, true)->toHtml() : $tag->description);
    $pageName = $tag->name;
@endphp

@include(Theme::getThemeNamespace('views.ecommerce.products'))
