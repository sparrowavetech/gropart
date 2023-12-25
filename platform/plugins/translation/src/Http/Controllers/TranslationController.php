<?php

namespace Botble\Translation\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Breadcrumb;
use Botble\Base\Supports\Language;
use Botble\Setting\Http\Controllers\SettingController;
use Botble\Translation\Http\Requests\TranslationRequest;
use Botble\Translation\Manager;
use Botble\Translation\Tables\TranslationTable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class TranslationController extends SettingController
{
    public function __construct(protected Manager $manager)
    {
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/translation::translation.translations'), route('translations.locales'));
    }

    public function index(Request $request, TranslationTable $translationTable)
    {
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

        $currentLocale = $request->has('ref_lang') ? $request->input('ref_lang') : app()->getLocale();

        $locale = Arr::first($locales, fn ($item) => $item['locale'] == $currentLocale);

        if (! $locale) {
            $locale = $defaultLanguage;
        }

        $translationTable->setLocale($locale['locale']);

        if ($request->expectsJson()) {
            return $translationTable->renderTable();
        }

        $exists = File::isDirectory(lang_path($locale['locale'])) && File::exists(lang_path('vendor'));

        return view(
            'plugins/translation::index',
            compact('locales', 'locale', 'defaultLanguage', 'translationTable', 'exists')
        );
    }

    public function update(TranslationRequest $request)
    {
        $group = $request->input('group');

        $name = $request->input('name');
        $value = $request->input('value');

        [$locale, $key] = explode('|', $name, 2);

        $this->manager->updateTranslation($locale, $group, $key, $value);

        return $this->httpResponse();
    }

    public function import()
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $this->manager->publishLocales();

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/translation::translation.import_success_message'));
    }
}
