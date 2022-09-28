@php
    $categories = ProductCategoryHelper::getAllProductCategories()
        ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
        ->whereIn('id', $config['categories']);
@endphp
@if ($categories->count())
    <div>
        <p>
            <strong>{{ $config['name'] }}:</strong>
            @foreach ($categories as $category)
                <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
            @endforeach
        </p>
    </div>
@endif
