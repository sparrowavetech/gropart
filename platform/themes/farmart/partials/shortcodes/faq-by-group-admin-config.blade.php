@php
    $categories = get_faq_category(['status'=>'published']);
@endphp

<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="p-2 border">
    @for ($i = 1; $i <= 5; $i++)
        <div class="form-group">
            <label class="control-label">{{ __('Select category :number', ['number' => $i]) }}</label>
            <div class="ui-select-wrapper form-group">
                <select name="category_id_{{ $i }}" class="ui-select">
                    <option value="">--{{ __('Select Category') }}--</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @if ($category->id == Arr::get($attributes, 'category_id_'.$i)) selected @endif>{!! BaseHelper::clean($category->indent_text) !!} {{ $category->name }}</option>
                    @endforeach
                </select>
                <svg class="svg-next-icon svg-next-icon-size-16">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                </svg>
            </div>
        </div>
    @endfor
    <div class="help-block">
        <small>{{ __('You can add up to 5 FAQ Category') }}</small>
    </div>
</div>
