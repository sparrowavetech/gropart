<?php

namespace Botble\LanguageAdvanced\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Language\Repositories\Interfaces\LanguageInterface;
use Botble\Language\Repositories\Interfaces\LanguageMetaInterface;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Botble\Language\Facades\Language;
use Botble\Base\Facades\MetaBox;
use Illuminate\Support\Facades\Route;
use Botble\Slug\Facades\SlugHelper;
use Botble\Table\CollectionDataTable;
use Botble\Table\EloquentDataTable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action(BASE_ACTION_META_BOXES, [$this, 'addLanguageBox'], 1134, 2);
        add_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, [$this, 'addCurrentLanguageEditingAlert'], 1134, 3);
        add_action(BASE_ACTION_BEFORE_EDIT_CONTENT, [$this, 'getCurrentAdminLanguage'], 1134, 2);
        add_action(BASE_ACTION_META_BOXES, [$this, 'customizeMetaBoxes'], 10, 2);

        add_filter(BASE_FILTER_BEFORE_GET_ADMIN_SINGLE_ITEM, [$this, 'getDataLanguageBeforeShow'], 1135, 4);
        add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addLanguageTableHeading'], 1134, 2);
        add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addLanguageColumn'], 1134, 2);
        add_filter(BASE_FILTER_BEFORE_GET_FRONT_PAGE_ITEM, [$this, 'checkItemLanguageBeforeGetListItem'], 1134, 2);
        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'changeFormDataBeforeRendering'], 1134, 2);
        add_filter(BASE_FILTER_SLUG_AREA, [$this, 'changeSlugField'], 25, 2);
        add_filter('stored_meta_box_key', [$this, 'storeMetaBoxKey'], 1134, 2);
    }

    public function addLanguageBox(string $priority, Model|string|null $object): void
    {
        if ($priority == 'top' && ! empty($object) && $object->getKey() && LanguageAdvancedManager::isSupported($object) && Language::getActiveLanguage([
                'lang_code',
                'lang_flag',
                'lang_name',
            ])->count() > 1) {
            MetaBox::addMetaBox(
                'language_advanced_wrap',
                trans('plugins/language::language.name'),
                [$this, 'languageMetaField'],
                get_class($object),
                'top'
            );
        }
    }

    public function languageMetaField(): string|null
    {
        $languages = Language::getActiveLanguage([
            'lang_code',
            'lang_flag',
            'lang_name',
        ]);

        if ($languages->isEmpty()) {
            return null;
        }

        $args = func_get_args();

        $currentLanguage = self::checkCurrentLanguage($languages);

        if (! $currentLanguage) {
            $currentLanguage = Language::getDefaultLanguage([
                'lang_flag',
                'lang_name',
                'lang_code',
            ]);
        }

        $route = $this->getRoutes();

        return view('plugins/language-advanced::language-box', compact(
            'args',
            'languages',
            'currentLanguage',
            'route'
        ))
            ->render();
    }

    public function checkCurrentLanguage(array|Collection $languages)
    {
        $request = $this->app['request'];

        foreach ($languages as $language) {
            if (($request->input('ref_lang') && $language->lang_code == $request->input('ref_lang')) ||
                $language->lang_is_default
            ) {
                return $language;
            }
        }

        return null;
    }

    protected function getRoutes(): array
    {
        $currentRoute = implode('.', explode('.', Route::currentRouteName(), -1));

        return apply_filters(LANGUAGE_FILTER_ROUTE_ACTION, [
            'create' => $currentRoute . '.create',
            'edit' => $currentRoute . '.edit',
        ]);
    }

    public function addCurrentLanguageEditingAlert(Request $request, Model|string|null $data = null): void
    {
        $model = $data;
        if (is_object($data)) {
            $model = get_class($data);
        }

        if ($data && LanguageAdvancedManager::isSupported($model)) {
            $code = Language::getCurrentAdminLocaleCode();
            if (empty($code)) {
                $code = $this->getCurrentAdminLanguage($request, $data);
            }

            $language = null;
            if (! empty($code) && is_string($code)) {
                Language::setCurrentAdminLocale($code);
                $language = $this->app->make(LanguageInterface::class)->getFirstBy(
                    ['lang_code' => $code],
                    ['lang_name']
                );

                if (! empty($language)) {
                    $language = $language->lang_name;
                }
            }

            echo view('plugins/language::partials.notification', compact('language'))->render();
        }
    }

    public function getCurrentAdminLanguage(Request $request, Model|string|null $data = null): string|null
    {
        $code = null;
        if ($request->has('ref_lang')) {
            $code = $request->input('ref_lang');
        } elseif (! empty($data) && $data->id) {
            $meta = $this->app->make(LanguageMetaInterface::class)->getFirstBy([
                'reference_id' => $data->id,
                'reference_type' => get_class($data),
            ], ['lang_meta_code']);

            if (! empty($meta)) {
                $code = $meta->lang_meta_code;
            }
        }

        if (empty($code) || ! is_string($code)) {
            $code = Language::getDefaultLocaleCode();
        }

        Language::setCurrentAdminLocale($code);

        return $code;
    }

    public function languageSwitcher(array $options = []): string
    {
        return view('plugins/language::partials.switcher', compact('options'))->render();
    }

    public function getDataLanguageBeforeShow(EloquentBuilder|null|Collection $data, Model|string|null $model)
    {
        if (! LanguageAdvancedManager::isSupported($model)) {
            return $data;
        }

        $currentAdminLocale = Language::getCurrentAdminLocaleCode();

        if ((is_in_admin() && $currentAdminLocale == Language::getDefaultLocaleCode())) {
            return $data;
        }

        $table = $model->getTable();

        $translationTable = $table . '_translations';

        $language = DB::table($translationTable)
            ->where($table . '_id', $data->value('id'))
            ->where($translationTable . '.lang_code', $currentAdminLocale)
            ->first();

        if ($language) {
            if (! $data->getQuery()->columns) {
                $data = $data->select([$table . '.*']);
            }

            $data = $data
                ->addSelect([$translationTable . '.*'])
                ->join($translationTable, $translationTable . '.' . $table . '_id', $table . '.id');

            $data = $data->where($translationTable . '.lang_code', $currentAdminLocale);
        }

        return $data;
    }

    public function addLanguageColumn(EloquentDataTable|CollectionDataTable $data, Model|string|null $model): EloquentDataTable|CollectionDataTable
    {
        if ($model && LanguageAdvancedManager::isSupported($model)) {
            $route = $this->getRoutes();

            if (is_in_admin() && Auth::check() && ! Auth::user()->hasAnyPermission($route)) {
                return $data;
            }

            return $data->addColumn('language', function ($item) use ($route) {
                $languages = Language::getActiveLanguage();

                return view('plugins/language-advanced::language-column', compact('item', 'route', 'languages'))
                    ->render();
            });
        }

        return $data;
    }

    public function addLanguageTableHeading(array $headings, Model|string|null $model): array
    {
        if (! LanguageAdvancedManager::isSupported($model)) {
            return $headings;
        }

        if (is_in_admin() && Auth::check() && ! Auth::user()->hasAnyPermission($this->getRoutes())) {
            return $headings;
        }

        $languages = Language::getActiveLanguage(['lang_code', 'lang_name', 'lang_flag']);
        $heading = '';
        foreach ($languages as $language) {
            $heading .= language_flag($language->lang_flag, $language->lang_name);
        }

        return array_merge($headings, [
            'language' => [
                'name' => 'language_meta.lang_meta_id',
                'title' => $heading,
                'class' => 'text-center language-header no-sort',
                'width' => (count($languages) * 40) . 'px',
                'orderable' => false,
                'searchable' => false,
            ],
        ]);
    }

    public function checkItemLanguageBeforeGetListItem($query, Model|string|null $model): Builder|EloquentBuilder|Model
    {
        if ($query instanceof Builder || $query instanceof EloquentBuilder) {
            $model = $query->getModel();
        }

        $currentLocale = Language::getCurrentLocaleCode();

        if ($currentLocale == Language::getDefaultLocaleCode()) {
            return $query;
        }

        if (! LanguageAdvancedManager::isSupported($model)) {
            return $query;
        }

        return $query->with([
            'translations' => function ($query) use ($model, $currentLocale) {
                $query->where($model->getTable() . '_translations' . '.lang_code', $currentLocale);
            },
        ]);
    }

    public function changeFormDataBeforeRendering(FormAbstract $form, Model|string|null $data): FormAbstract
    {
        if (is_in_admin() && request()->input('ref_lang') && Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode() && $data && $data->id && LanguageAdvancedManager::isSupported($data)) {
            foreach ($form->getMetaBoxes() as $key => $metaBox) {
                if (LanguageAdvancedManager::isTranslatableMetaBox($key)) {
                    continue;
                }

                $form->removeMetaBox($key);
            }

            $columns = LanguageAdvancedManager::getTranslatableColumns($data);
            foreach ($form->getFields() as $key => $field) {
                if (! in_array($key, $columns)) {
                    $form->remove($key);
                }
            }

            $refLang = null;

            if (Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode()) {
                $refLang = '?ref_lang=' . Language::getCurrentAdminLocaleCode();
            }

            $form
                ->setFormOption('url', route('language-advanced.save', $data->id) . $refLang)
                ->add('model', 'hidden', ['value' => get_class($data)]);
        }

        return $form;
    }

    public function customizeMetaBoxes(string $context, Model|string|null $object): void
    {
        if (is_in_admin() && request()->input('ref_lang') && Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode() && LanguageAdvancedManager::isSupported($object)) {
            foreach (MetaBox::getMetaBoxes() as $reference => $metaBox) {
                foreach ($metaBox as $context => $position) {
                    foreach ($position as $item) {
                        foreach (array_keys($item) as $key) {
                            if (LanguageAdvancedManager::isTranslatableMetaBox($key)) {
                                continue;
                            }

                            MetaBox::removeMetaBox($key, $reference, $context);
                        }
                    }
                }
            }
        }
    }

    public function changeSlugField(string|null $html = null, Model|string|null $object = null): string|null
    {
        if (is_in_admin() && request()->input('ref_lang') && Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode() && LanguageAdvancedManager::isSupported($object) && SlugHelper::isSupportedModel(get_class($object))) {
            Assets::addStylesDirectly('vendor/core/packages/slug/css/slug.css');

            $prefix = SlugHelper::getPrefix(get_class($object));

            return view('plugins/language-advanced::slug', compact('object', 'prefix'))->render();
        }

        return $html;
    }

    public function storeMetaBoxKey(string $key, Model|string|null $object): string
    {
        $locale = is_in_admin() ? Language::getCurrentAdminLocaleCode() : Language::getCurrentLocaleCode();

        $translatableColumns = LanguageAdvancedManager::getTranslatableColumns($object);

        $translatableColumns[] = 'seo_meta';

        if (is_plugin_active('language') && $locale != Language::getDefaultLocaleCode() && in_array($key, $translatableColumns)) {
            $key = $locale . '_' . $key;
        }

        return $key;
    }
}
