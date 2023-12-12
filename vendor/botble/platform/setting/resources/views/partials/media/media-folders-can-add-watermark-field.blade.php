<x-core-setting::form-group>
    <x-core::form.label
        for="media_folders_can_add_watermark"
        :label="trans('core/setting::setting.media.media_folders_can_add_watermark')"
    />
    <x-core::form.checkbox
        :label="trans('core/setting::setting.media.all')"
        class="check-all"
        data-set=".media-folder"
    />
    <ul class="list-unstyled">
        @foreach ($folders as $key => $item)
            <li>
                <x-core::form.checkbox
                    :label="$item"
                    class="media-folder"
                    name="media_folders_can_add_watermark[]"
                    value="{{ $key }}"
                    id="media-folder-item-{{ $key }}"
                    :checked="empty($folderIds) || in_array($key, $folderIds)"
                />
            </li>
        @endforeach
    </ul>
</x-core-setting::form-group>
