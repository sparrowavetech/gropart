@php
   Theme::set('pageDescription', $category->description);
   $pageName = $category->name;
@endphp

@include(Theme::getThemeNamespace('views.ecommerce.products'))
