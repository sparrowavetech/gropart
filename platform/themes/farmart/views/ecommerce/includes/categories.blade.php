@php
    $categoriesRequest ??= [];
    $activeCategoryId ??= 0;
    $categories = $categories->where('is_enquiry','=' , $condition['is_enquiry']);
@endphp

<ul @class(['loading-skeleton'])>
    @php
        if (!isset($groupedCategories)) {
            $groupedCategories = $categories->groupBy('parent_id');
        }

        $currentCategories = $groupedCategories->get($parentId ?? 0);
    @endphp

    @if($currentCategories)
        @foreach ($currentCategories as $category)
            @php
                $hasChildren = $groupedCategories->has($category->id);
            @endphp
            @if ($loop->first && !$category->parent_id)
                @if($condition['is_enquiry'] ==1)
                    <li class="category-filter show-all-product-categories mb-2">
                        <a class="nav-list__item-link" data-id="" href="{{ route('public.product.enquiry') }}?iseq=1">
                            <span class="cat-menu-close svg-icon">
                                <svg>
                                    <use href="#svg-icon-chevron-left" xlink:href="#svg-icon-close"></use>
                                </svg>
                            </span>
                            <span>{{ __('All categories') }}</span>
                        </a>
                    </li>
                @else
                    <li class="category-filter show-all-product-categories mb-2">
                        <a class="nav-list__item-link" data-id="" href="{{ route('public.products') }}">
                            <span class="cat-menu-close svg-icon">
                                <svg>
                                    <use href="#svg-icon-chevron-left" xlink:href="#svg-icon-close"></use>
                                </svg>
                            </span>
                            <span>{{ __('All categories') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            <li @class(['category-filter', 'opened' => ($activeCategoryId == $category->id || $urlCurrent != route('public.single', $category->url)),])>
                <div class="widget-layered-nav-list__item">
                    <div class="nav-list__item-title">
                        <a data-id="{{ $category->id }}" data-is_enquiry="{{ $category->is_enquiry }}" href="{{ route('public.single', $category->url) }}?iseq={{$condition['is_enquiry']}}" @class(['nav-list__item-link', 'active' => $activeCategoryId == $category->id || $urlCurrent == route('public.single', $category->url),])>
                            @if (!$category->parent_id)
                                @if ($category->icon_image)
                                    <img src="{{ RvMedia::getImageUrl($category->icon_image) }}" alt="{{ $category->name }}" width="18" height="18">
                                @elseif ($category->icon)
                                    <i class="{{ $category->icon }}"></i>
                                @endif
                                <span class="ms-1">{{ $category->name }}</span>
                            @else
                                <span>{{ $category->name }}</span>
                            @endif
                        </a>
                    </div>

                    @if ($hasChildren)
                        <span class="cat-menu-close svg-icon closed-icon">
                            <svg><use href="#svg-icon-increase" xlink:href="#svg-icon-increase"></use></svg>
                        </span>

                        <span class="cat-menu-close svg-icon opened-icon">
                            <svg><use href="#svg-icon-decrease" xlink:href="#svg-icon-decrease"></use></svg>
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
    @endif
</ul>
