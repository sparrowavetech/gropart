<style>
    .colorbox .sp-replacer.sp-light,
    .colorbox-full .sp-replacer.sp-light {
        height: 40px;
        width: 100%;
    }
    .colorbox .sp-replacer.sp-light .sp-preview {
        width: 85%;
        height: 30px;
    }
    .colorbox-full .sp-replacer.sp-light .sp-preview {
        width: 92%;
        height: 30px;
    }
</style>
<div class="row g-2">
    <div class="col colorbox-full">
        <div class="form-group">
            <label class="control-label">{{ __('Section Background Color') }}</label>
            <input type="text" class="form-control" name="sectioncolor" value="{{ Arr::get($attributes, 'sectioncolor') }}" id="sectioncolor" data-bb-color-picker="" />
        </div>
    </div>
    <div class="col colorbox-full">
        <div class="form-group">
            <label class="control-label">{{ __('Section Title Color') }}</label>
            <input type="text" class="form-control" name="titlecolor" value="{{ Arr::get($attributes, 'titlecolor') }}" id="titlecolor" data-bb-color-picker="" />
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col">
        <div class="form-group">
            <label class="control-label">{{ __('Title Style') }}</label>
            <select class="form-select" name="titletype">
                <option value="">{{ __('-- Select Style --') }}</option>
                <option value="bold"
                    @if ('bold' == Arr::get($attributes, 'titletype')) selected @endif
                >{{ __('Bold') }}</option>
                <option value="normal"
                    @if ('normal' == Arr::get($attributes, 'titletype')) selected @endif
                >{{ __('Normal') }}</option>
            </select>
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <label class="control-label">{{ __('All Content Direction') }}</label>
            <select class="form-select" name="contentdirection">
                <option value="">{{ __('-- select --') }}</option>
                <option value="0"
                    @if (0 == Arr::get($attributes, 'contentdirection')) selected @endif
                >{{ __('Left') }}</option>
                <option value="1"
                    @if (1 == Arr::get($attributes, 'contentdirection')) selected @endif
                >{{ __('Right') }}</option>
                <option value="2"
                    @if (2 == Arr::get($attributes, 'contentdirection')) selected @endif
                >{{ __('Center') }}</option>
                <option value="3"
                    @if (3 == Arr::get($attributes, 'contentdirection')) selected @endif
                >{{ __('Justify') }}</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" class="form-control" name="title" value="{{ Arr::get($attributes, 'title') }}" placeholder="{{ __('Title') }}" />
</div>

<div class="form-group">
    <label class="control-label">{{ __('Content') }}</label>
    <textarea name="subtitle" class="form-control" rows="4" placeholder="{{ __('Enter Section content') }}">{{ Arr::get($attributes, 'subtitle') }}</textarea>
</div>

<div class="row g-2">
    <div class="col">
        <div class="form-group">
            <label class="control-label">{{ __('Button Text') }}</label>
            <input type="text" class="form-control" name="btntext" value="{{ Arr::get($attributes, 'btntext') }}" placeholder="{{ __('Button text') }}" />
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <label class="control-label">{{ __('Button Link') }}</label>
            <input type="text" class="form-control" name="btnlink" value="{{ Arr::get($attributes, 'btnlink') }}" placeholder="{{ __('Button link') }}" />
        </div>
    </div>
</div>

<div class="form-group">
    <input type="checkbox" name="btncheckbox" @if ('on' == Arr::get($attributes, 'btncheckbox')) checked @endif /> {{ __('Open link in new tab?') }}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Image') }}</label>
    {!! Form::mediaImage('rowimg', Arr::get($attributes, 'rowimg')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Image Border') }}</label>
    <div class="row g-2">
        <div class="col">
            <select class="form-select" name="imageradius">
                <option value="">{{ __('-- Radius --') }}</option>
                @for ($i = 0; $i <= 10; $i++)
                    <option value="{{ $i }}"
                            @if ($i == Arr::get($attributes, 'imageradius')) selected @endif
                    >{{ $i }} {{ __(' px') }}</option>
                @endfor
            </select>
            <label class="control-label"><small>{{ __('Border Radius') }}</small></label>
        </div>
        <div class="col">
            <select class="form-select" name="imagebordersize">
                <option value="">{{ __('-- Size --') }}</option>
                @for ($j = 0; $j <= 10; $j++)
                    <option value="{{ $j }}"
                            @if ($j == Arr::get($attributes, 'imagebordersize')) selected @endif
                    >{{ $j }} {{ __(' px') }}</option>
                @endfor
            </select>
            <label class="control-label"><small>{{ __('Border Size') }}</small></label>
        </div>
        <div class="col">
            <select class="form-select" name="imagebordertype">
                <option value="">{{ __('-- Type --') }}</option>
                <option value="none"
                        @if ('none' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('None') }}</option>
                <option value="solid"
                        @if ('solid' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Solid') }}</option>
                <option value="dashed"
                        @if ('dashed' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Dashed') }}</option>
                <option value="dotted"
                        @if ('dotted' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Dotted') }}</option>
                <option value="double"
                        @if ('double' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Double') }}</option>
                <option value="groove"
                        @if ('groove' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Groove') }}</option>
                <option value="inset"
                        @if ('inset' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Inset') }}</option>
                <option value="outset"
                        @if ('outset' == Arr::get($attributes, 'imagebordertype')) selected @endif
                >{{ __('Outset') }}</option>
            </select>
            <label class="control-label"><small>{{ __('Border Type') }}</small></label>
        </div>
        <div class="col colorbox">
            <input class="form-control" type="text" name="imagebordercolor" id="imagebordercolor" value="{{ Arr::get($attributes, 'imagebordercolor') }}" data-bb-color-picker="" />
            <label class="control-label"><small>{{ __('Border Color') }}</small></label>
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col colorbox-full">
        <div class="form-group">
            <label class="control-label">{{ __('Image Background Color') }}</label>
            <input type="text" class="form-control" name="imagecolor" value="{{ Arr::get($attributes, 'imagecolor') }}" id="imagecolor" data-bb-color-picker="" />
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <label class="control-label">{{ __('Image Direction') }}</label>
            <select class="form-select" name="imgdirection">
                <option value="">{{ __('-- select --') }}</option>
                <option value="0"
                        @if (0 == Arr::get($attributes, 'imgdirection')) selected @endif
                >{{ __('Left') }}</option>
                <option value="1"
                    @if (1 == Arr::get($attributes, 'imgdirection')) selected @endif
                >{{ __('Right') }}</option>
            </select>
        </div>
    </div>
</div>
