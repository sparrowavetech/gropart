@push('meta-box-header-seo_wrap')
    <x-core::card.actions>
        <a
            href="#"
            class="btn-trigger-show-seo-detail"
            v-pre
        >{{ trans('packages/seo-helper::seo-helper.edit_seo_meta') }}</a>
    </x-core::card.actions>
@endpush

<div
    class="seo-preview"
    v-pre
>
    <p @class(['default-seo-description', 'hidden' => !empty($object->id)])>
        {{ trans('packages/seo-helper::seo-helper.default_description') }}
    </p>

    <div @class(['existed-seo-meta', 'hidden' => empty($object->id)])>
        <span class="page-title-seo">
            {!! BaseHelper::clean($meta['seo_title'] ?? (!empty($object->id) ? $object->name ?? $object->title : null)) !!}
        </span>

        <div class="page-url-seo ws-nm">
            <p>{{ !empty($object->id) && $object->url ? $object->url : '-' }}</p>
        </div>

        <div class="ws-nm">
            <span
                style="color: #70757a;">{{ !empty($object->id) && $object->created_at ? $object->created_at->format('M d, Y') : Carbon\Carbon::now()->format('M d, Y') }}
                - </span>
            <span class="page-description-seo">
                @if (!empty($meta['seo_description']))
                    {{ strip_tags($meta['seo_description']) }}
                @elseif ($metaDescription = (!empty($object->id) ? ($object->description ?: ($object->content ? Str::limit($object->content, 250) : old('seo_meta.seo_description'))) : old('seo_meta.seo_description')))
                    {{ strip_tags($metaDescription) }}
                @endif
            </span>
        </div>
    </div>
</div>

<div
    class="hidden seo-edit-section"
    v-pre
>
    <x-core::hr />

    <x-core::form.text-input
        :label="trans('packages/seo-helper::seo-helper.seo_title')"
        id="seo_title"
        name="seo_meta[seo_title]"
        :value="old('seo_meta.seo_title', $meta['seo_title'])"
        :placeholder="trans('packages/seo-helper::seo-helper.seo_title')"
        :data-counter="120"
    />

    <x-core::form.textarea
        :label="trans('packages/seo-helper::seo-helper.seo_description')"
        id="seo_description"
        name="seo_meta[seo_description]"
        type="textarea"
        :value="old('seo_meta.seo_description', strip_tags((string) $meta['seo_description']))"
        :placeholder="trans('packages/seo-helper::seo-helper.seo_description')"
        :data-counter="160"
        rows="3"
    />
</div>
