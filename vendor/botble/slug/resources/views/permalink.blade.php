@php
    Assets::addScriptsDirectly('vendor/core/packages/slug/js/slug.js')->addStylesDirectly('vendor/core/packages/slug/css/slug.css');
    $prefix = apply_filters(FILTER_SLUG_PREFIX, SlugHelper::getPrefix($model::class), $model);
    $value = $value ?: old('slug');
    $endingURL = SlugHelper::getPublicSingleEndingURL();
    $previewURL = str_replace('--slug--', (string) $value, url($prefix) . '/' . config('packages.slug.general.pattern')) . $endingURL . (Auth::user() && $preview ? '?preview=true' : '');
@endphp

<div
    class="slug-field-wrapper"
    data-field-name="{{ SlugHelper::getColumnNameToGenerateSlug($model) }}"
>
    @if (in_array(
        Route::currentRouteName(), ['pages.create', 'pages.edit'])
        && BaseHelper::isHomepage(Route::current()->parameter('page.id'))
    )
        <x-core::form.text-input
            :label="trans('core/base::forms.permalink')"
            name="slug"
            :group-flat="true"
            :value="route('public.index')"
            :readonly="true"
        />
    @else
        <x-core::form.text-input
            :label="trans('core/base::forms.permalink')"
            :required="true"
            name="slug"
            :group-flat="true"
            class="ps-0"
            :value="$value"
            :readonly="!!$value"
        >
            <x-slot:prepend>
                <span class="input-group-text">
                    {{ url($prefix) }}/
                </span>
            </x-slot:prepend>

            <x-slot:append>
                <span class="input-group-text">
                    @if($id)
                        <a href="javascript:void(0)" class="input-group-addon" data-bb-toggle="slug-edit">
                            {{ trans('core/base::forms.edit') }}
                        </a>
                            <a href="javascript:void(0)" class="input-group-addon" data-bb-toggle="slug-ok" style="display: none">
                            {{ trans('core/base::forms.ok') }}
                        </a>
                    @endif
                </span>
            </x-slot:append>
        </x-core::form.text-input>
        @if (Auth::user() && $id && is_in_admin(true))
            <x-core::form.helper-text class="mt-n2 text-truncate">
                {{ trans('packages/slug::slug.preview') }}: <a href="{{ $previewURL }}" target="_blank">{{ $previewURL }}</a>
            </x-core::form.helper-text>
        @endif
        @if ($editable)
            <input
                class="slug-current"
                name="{{ $name }}"
                type="hidden"
                value="{{ $value }}"
            >
            <div
                class="slug-data"
                data-url="{{ route('slug.create') }}"
                data-view="{{ rtrim(str_replace('--slug--', '', url($prefix) . '/' . config('packages.slug.general.pattern')), '/') . '/' }}"
                data-id="{{ $id ?: 0 }}"
            ></div>
            <input
                name="slug_id"
                type="hidden"
                value="{{ $id ?: 0 }}"
            >
            <input
                name="is_slug_editable"
                type="hidden"
                value="1"
            >
        @endif
    @endif
</div>
