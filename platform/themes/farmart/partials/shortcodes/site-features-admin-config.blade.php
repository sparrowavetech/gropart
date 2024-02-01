<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" class="form-control" name="title" value="{{ Arr::get($attributes, 'title') }}" placeholder="{{ __('Title') }}" />
</div>

@for ($i = 1; $i <= 5; $i++)
<div class="form-group">
    <label class="control-label">{{ __('Name :number', ['number' => $i]) }}</label>
    <input type="text" class="form-control" name="name_{{ $i }}" value="{{ Arr::get($attributes, 'name_' . $i) }}">
</div>
<div class="form-group">
    <label class="control-label">{{ __('Subtitle :number', ['number' => $i]) }}</label>
    <input type="text" class="form-control" name="subtitle_{{ $i }}" value="{{ Arr::get($attributes, 'subtitle' . $i) }}">
</div>
<div class="form-group">
    <label class="control-label">{{ __('Icon :number', ['number' => $i]) }}</label>
    {!! Form::mediaImage('icon_' . $i, Arr::get($attributes, 'icon_' . $i)) !!}
</div>
@endfor
