@php
$categories = get_all_categories(['status'=>'published']);
@endphp

<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Select category') }}</label>
    <div class="ui-select-wrapper form-group">
        <select name="category_id" class="ui-select">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @if ($category->id == Arr::get($attributes, 'category_id')) selected @endif>{!! BaseHelper::clean($category->indent_text) !!} {{ $category->name }}</option>
            @endforeach
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
        </svg>
    </div>
</div>

<div class="form-group mb-3">
    <label class="control-label">{{ trans('plugins/blog::base.number_posts_per_page') }}</label>
    {!! Form::number('paginate', theme_option('number_of_posts_in_a_category', 12), ['class' => 'form-control', 'name' => 'loop', 'placeholder' => trans('plugins/blog::base.number_posts_per_page')]) !!}
</div>