@php
    Theme::set('pageDescription', $tag->description);
    $pageName = $tag->name;
@endphp

@include(Theme::getThemeNamespace('views.ecommerce.products'))
