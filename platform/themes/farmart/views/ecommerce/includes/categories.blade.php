@php
    $categories->loadMissing(['slugable', 'activeChildren:id,name,parent_id', 'activeChildren.slugable']);
    $categories = $categories->where('is_enquiry','=' ,$condition['is_enquiry']);
    if (!empty($categoriesRequest)) {
        $categories = $categories->whereIn('id', $categoriesRequest);
    }
@endphp

<ul>
    @if (!empty($categoriesRequest))
        @if($condition['is_enquiry'])
        <li class="category-filter">
            <a class="nav-list__item-link" href="{{ route('public.product.enquiry') }}?iseq=1" data-id="">
                <span class="cat-menu-close svg-icon">
                    <svg>
                        <use href="#svg-icon-chevron-left" xlink:href="#svg-icon-close"></use>
                    </svg>
                </span>
                <span>{{ __('All categories') }}</span>
            </a>
        </li>
        @else
        <li class="category-filter">
            <a class="nav-list__item-link" href="{{ route('public.products') }}" data-id="">
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

    @foreach($categories as $category)
        @php
            $isActive = $urlCurrent == $category->url ||
                (!empty($categoriesRequest && in_array($category->id, $categoriesRequest))) ||
                ($loop->first && $categoriesRequest && $categories->count() == 1 && $category->activeChildren->where('is_enquiry','=' ,$condition['is_enquiry'])->count());
        @endphp
        
        <li class="category-filter @if ($isActive) opened @endif">
            <div class="widget-layered-nav-list__item">
                <div class="nav-list__item-title">
                    <a class="nav-list__item-link @if ($isActive) active @endif"
                        href="{{ $category->url }}?iseq={{$condition['is_enquiry']}}" data-id="{{ $category->id }}" data-is_enquiry="{{ $category->is_enquiry }}">
                        @if (!$category->parent_id)
                            @if ($category->getMetaData('icon_image', true))
                                <img src="{{ RvMedia::getImageUrl($category->getMetaData('icon_image', true)) }}" alt="{{ $category->name }}" width="18" height="18">
                            @elseif ($category->getMetaData('icon', true))
                                <i class="{{ $category->getMetaData('icon', true) }}"></i>
                            @endif
                            <span class="ms-1">{!! BaseHelper::clean($category->name) !!}</span>
                        @else
                            <span>{!! BaseHelper::clean($category->name) !!}</span>
                        @endif
                    </a>
                </div>
                @if ($category->activeChildren->where('is_enquiry','=' ,$condition['is_enquiry'])->count())
                    <span class="cat-menu-close svg-icon">
                        <svg>
                            <use href="#svg-icon-close" xlink:href="#svg-icon-close"></use>
                        </svg>
                    </span>
                @endif
            </div>
            @if ($category->activeChildren->where('is_enquiry','=' ,$condition['is_enquiry'])->count())
                @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.categories', compact('urlCurrent') + [
                    'categories' => $category->activeChildren,
                    'categoriesRequest' => [],
                ])
            @endif
        </li>
       
    @endforeach
</ul>
