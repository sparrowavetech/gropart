@php
$categoriesRequest ??= [];
$activeCategoryId ??= 0;
@endphp
<ul @class(['loading-skeleton'])>

    @php
    if (!isset($groupedCategories) ) {
    $groupedCategories = $categories->groupBy('parent_id');
    }
    $currentCategories = $groupedCategories->get($parentId ?? 0);

    @endphp

    @if($currentCategories && $is_enquiry == 1)
    @foreach ($currentCategories as $category)
    @if (!empty($categoriesRequest) && $loop->first && !$category->parent_id)
    <li class="category-filter show-all-product-categories mb-2">

        <a
            class="nav-list__item-link"
            data-id=""
            href="{{ route('public.products') }}?enquiry={{ $is_enquiry}}">
            <span class="cat-menu-close svg-icon">
                <svg>
                    <use
                        href="#svg-icon-chevron-left"
                        xlink:href="#svg-icon-close"></use>
                </svg>
            </span>
            <span>{{ __('All categories') }}</span>
        </a>
    </li>
    @endif

    <li @class([ 'category-filter' , 'opened'=>
        in_array($category->id, $categoriesRequest) &&
        ($activeCategoryId == $category->id || $urlCurrent != route('public.single', $category->url)),
        ])>
        <div class="widget-layered-nav-list__item">
            <div class="nav-list__item-title">
                <a
                    data-id="{{ $category->id }}"
                    href="{{ route('public.single', $category->url) }}?enquiry={{ $is_enquiry}}"
                    @class([ 'nav-list__item-link' , 'active'=>
                    $activeCategoryId == $category->id || $urlCurrent == route('public.single', $category->url),
                    ])
                    >
                    @if (!$category->parent_id)
                    @if ($category->icon_image)
                    <img
                        src="{{ RvMedia::getImageUrl($category->icon_image) }}"
                        alt="{{ $category->name }}"
                        width="18"
                        height="18">
                    @elseif ($category->icon)
                    <i class="{{ $category->icon }}"></i>
                    @endif
                    <span class="ms-1">{{ $category->name }}</span>
                    @else
                    <span>{{ $category->name }}</span>
                    @endif
                </a>
            </div>

            @php
            $hasChildren = $groupedCategories->has($category->id);
            @endphp

            @if ($hasChildren)
            <span class="cat-menu-close svg-icon closed-icon">
                <svg>
                    <use
                        href="#svg-icon-increase"
                        xlink:href="#svg-icon-increase"></use>
                </svg>
            </span>

            <span class="cat-menu-close svg-icon opened-icon">
                <svg>
                    <use
                        href="#svg-icon-decrease"
                        xlink:href="#svg-icon-decrease"></use>
                </svg>
            </span>
            @endif
        </div>
        @if ($hasChildren)
        @include(Theme::getThemeNamespace('views.ecommerce.includes.categories'), [
        'categories' => $groupedCategories,
        'parentId' => $category->id,
        ])
        @endif
    </li>

    @endforeach
    @else

    @if (!empty(request()->all()) && $is_enquiry == 1)
    <li class="category-filter mb-2">
        <a
            class="nav-list__item-link"
            data-id=""
            href="{{ route('public.product.enquiry') }}?enquiry={{ $is_enquiry}}">
            <span class="cat-menu-close svg-icon">
                <svg>
                    <use
                        href="#svg-icon-chevron-left"
                        xlink:href="#svg-icon-close"></use>
                </svg>
            </span>
            <span>{{ __('All categories') }}</span>
        </a>
    </li>
    @endif
    @php
    if(!isset($categories)){
        $categories = $groupedCategories->get($parentId ?? 0);
    }
   
    @endphp
    @foreach ($categories as $category)

    <li @class([ 'category-filter' , 'opened'=>
        in_array($category->id, $categoriesRequest) &&
        ($activeCategoryId == $category->id || $urlCurrent != route('public.single', $category->url)),
        ])>
        <div class="widget-layered-nav-list__item">
            <div class="nav-list__item-title">
                <a
                    data-id="{{ $category->id }}"
                    href="{{ route('public.single', $category->url) }}?enquiry={{ $is_enquiry}}"
                    @class([ 'nav-list__item-link' , 'active'=>
                    $activeCategoryId == $category->id || $urlCurrent == route('public.single', $category->url),
                    ])
                    >
                    @if (!$category->parent_id)
                    @if ($category->icon_image)
                    <img
                        src="{{ RvMedia::getImageUrl($category->icon_image) }}"
                        alt="{{ $category->name }}"
                        width="18"
                        height="18">
                    @elseif ($category->icon)
                    <i class="{{ $category->icon }}"></i>
                    @endif
                    <span class="ms-1">{{ $category->name }}</span>
                    @else
                    <span>{{ $category->name }}</span>
                    @endif
                </a>
            </div>

            @php
            $hasChildren = $groupedCategories->has($category->id);
            @endphp

            @if ($hasChildren)
            <span class="cat-menu-close svg-icon closed-icon">
                <svg>
                    <use
                        href="#svg-icon-increase"
                        xlink:href="#svg-icon-increase"></use>
                </svg>
            </span>

            <span class="cat-menu-close svg-icon opened-icon">
                <svg>
                    <use
                        href="#svg-icon-decrease"
                        xlink:href="#svg-icon-decrease"></use>
                </svg>
            </span>
            @endif
        </div>
        @if ($hasChildren)
        @include(Theme::getThemeNamespace('views.ecommerce.includes.categories'), [
        'categories' => $groupedCategories,
        'parentId' => $category->id,
        'is_enquiry'=>'1'
        ])
        @endif
    </li>
    @endforeach
    @endif

</ul>