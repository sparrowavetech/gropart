@foreach ($categories as $category)
    <option value="{{ $category->id }}">{!! $indent !!}{{ $category->name }}</option>
    @if ($category->activeChildren->first())
        {!! Theme::partial('product-categories-select', [
            'categories' => $category->activeChildren,
            'indent' => $indent . '&nbsp;&nbsp;',
        ]) !!}
    @endif
@endforeach
