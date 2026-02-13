@php
    Theme::set('pageDescription', $brand->description);
    $pageName = $brand->name;
@endphp

@include(Theme::getThemeNamespace('views.ecommerce.products'))
