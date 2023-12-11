@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::alert type="warning">
        {{ trans('plugins/translation::translation.theme_translations_instruction') }}
    </x-core::alert>

    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>{{ trans('plugins/translation::translation.translations') }}</x-core::card.title>
        </x-core::card.header>
        <x-core::card.body @class(['box-translation', 'empty-translation' => $count < 1])>
            <div class="row">
                <div class="col-md-6">
                    <p>{{ trans('plugins/translation::translation.translate_from') }}
                        <strong class="text-info">{{ $defaultLanguage ? $defaultLanguage['name'] : 'en' }}</strong>
                        {{ trans('plugins/translation::translation.to') }}
                        <strong class="text-info">{{ $locale['name'] }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="text-end">
                        @include(
                            'plugins/translation::partials.list-theme-languages-to-translate',
                            ['groups' => $locales, 'group' => $locale, 'route' => 'translations.index']
                        )
                    </div>
                </div>
            </div>

            <x-core::form :url="route('translations.import')">
                <div class="row justify-content-between">
                    <div class="col-md-4">
                        <x-core::form.select
                            name="replace"
                            :options="[
                                0 => trans('plugins/translation::translation.append_translation'),
                                1 => trans('plugins/translation::translation.replace_translation'),
                            ]"
                            :input-group="true"
                        >
                            <x-slot:append>
                                <x-core::button
                                    type="submit"
                                    class="button-import-groups"
                                >
                                    {{ trans('plugins/translation::translation.import_group') }}
                                </x-core::button>
                            </x-slot:append>
                        </x-core::form.select>
                    </div>
                    @if ($count)
                        <div class="col">
                            <x-core::form.select
                                name="group"
                                :options="$groups"
                                :value="request()->input('group')"
                                class="group-select"
                                :searchable="true"
                                :input-group="true"
                            />
                        </div>
                    @endif
                </div>
            </x-core::form>

            @if ($count < 1)
                <div class="text-muted">
                    {!! BaseHelper::clean(trans('plugins/translation::translation.no_translations', ['locale' => "<strong>{$locale['name']}</strong>"])) !!}
                </div>
            @endif
        </x-core::card.body>

        @if ($count > 0)
            {{ $translationTable->renderTable() }}
        @endif
    </x-core::card>

    @if (!empty($group))
        <x-core::modal.action
            id="confirm-publish-modal"
            :title="trans('plugins/translation::translation.publish_translations')"
            :description="trans('plugins/translation::translation.confirm_publish_group', ['group' => $group])"
            type="warning"
            :submit-button-attrs="['id' => 'button-confirm-publish-groups']"
            :submit-button-label="trans('core/base::base.yes')"
        />
    @endif
@endsection
