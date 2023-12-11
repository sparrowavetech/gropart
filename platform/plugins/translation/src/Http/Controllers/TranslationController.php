<?php

namespace Botble\Translation\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Supports\Language;
use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Setting\Http\Controllers\SettingController;
use Botble\Translation\Http\Requests\TranslationRequest;
use Botble\Translation\Manager;
use Botble\Translation\Models\Translation;
use Botble\Translation\Tables\TranslationTable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class TranslationController extends SettingController
{
    public function __construct(protected Manager $manager)
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/translation::translation.translations'), route('translations.locales'));
    }

    public function index(Request $request, TranslationTable $translationTable)
    {
        Validator::validate($request->input(), [
            'group' => ['nullable', 'string'],
        ]);

        $this->pageTitle(trans('plugins/translation::translation.admin-translations'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable'])
            ->addScriptsDirectly('vendor/core/plugins/translation/js/translation.js')
            ->addStylesDirectly('vendor/core/plugins/translation/css/translation.css');

        $locales = Language::getAvailableLocales();
        $defaultLanguage = Language::getDefaultLanguage();

        if (! count($locales)) {
            $locales = [
                'en' => $defaultLanguage,
            ];
        }

        $currentLocale = is_plugin_active('language') ? LanguageFacade::getRefLang() : app()->getLocale();

        $locale = Arr::first($locales, fn ($item) => $item['locale'] == $currentLocale);

        if (! $locale) {
            $locale = $defaultLanguage;
        }

        $translationTable->setLocale($locale['locale']);

        if ($request->expectsJson()) {
            return $translationTable->renderTable();
        }

        $groups = Translation::query()
            ->groupBy('group')
            ->pluck('group', 'group')
            ->all();

        $groups = ['' => trans('plugins/translation::translation.all')] + $groups;

        $count = Translation::query()
            ->where('locale', $locale['locale'])
            ->count();

        return view(
            'plugins/translation::index',
            compact('locales', 'locale', 'defaultLanguage', 'translationTable', 'groups', 'count')
        );
    }

    public function update(TranslationRequest $request)
    {
        $group = $request->input('group');

        if (in_array($group, $this->manager->getConfig('exclude_groups'))) {
            return $this->httpResponse();
        }

        $name = $request->input('name');
        $value = $request->input('value');

        [$locale, $key] = explode('|', $name, 2);
        $translation = Translation::query()->firstOrNew([
            'locale' => $locale,
            'group' => $group,
            'key' => $key,
        ]);

        $translation->update([
            'value' => (string)$value ?: null,
            'status' => Translation::STATUS_CHANGED,
        ]);

        $this->manager->exportTranslations($group);

        return $this->httpResponse();
    }

    public function import(Request $request)
    {
        $counter = $this->manager->importTranslations($request->boolean('replace'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/translation::translation.import_done', compact('counter')));
    }
}
