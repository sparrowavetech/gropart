<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" class="form-control" name="title" value="{{ Arr::get($attributes, 'title') }}" placeholder="{{ __('Title') }}" />
</div>

<div class="form-group">
    <label class="control-label">{{ __('Subtitle') }}</label>
    <textarea name="subtitle" class="form-control" rows="4">{{ Arr::get($attributes, 'subtitle') }}</textarea>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Background Image') }}</label>
    {!! Form::mediaImage('bgimg', Arr::get($attributes, 'bgimg')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Newsletter Image') }}</label>
    {!! Form::mediaImage('nlimg', Arr::get($attributes, 'nlimg')) !!}
</div>
