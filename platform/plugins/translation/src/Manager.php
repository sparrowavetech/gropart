<?php

namespace Botble\Translation;

use ArrayAccess;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Supports\Zipper;
use Botble\Theme\Facades\Theme;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;

class Manager
{
    protected array|ArrayAccess $config;

    public function __construct(protected Application $app, protected Filesystem $files)
    {
        $this->config = $app['config']['plugins.translation.general'];
    }

    public function publishLocales(): void
    {
        $paths = ServiceProvider::pathsToPublish(null, 'cms-lang');

        foreach ($paths as $from => $to) {
            $this->files->ensureDirectoryExists(dirname($to));
            $this->files->copyDirectory($from, $to);
        }

        if (! File::isDirectory(lang_path('en'))) {
            $this->downloadRemoteLocale('en');
        }
    }

    public function updateTranslation(string $locale, string $group, string $key, string|null $value): void
    {
        $loader = Lang::getLoader();

        if (str_contains($group, '/')) {
            $englishTranslations = $loader->load('en', Str::afterLast($group, '/'), Str::beforeLast($group, '/'));
            $translations = $loader->load($locale, Str::afterLast($group, '/'), Str::beforeLast($group, '/'));
        } else {
            $englishTranslations = $loader->load('en', $group);
            $translations = $loader->load($locale, $group);
        }

        Arr::set($translations, $key, $value);

        $translations = array_merge($englishTranslations, $translations);

        if (
            $locale != 'en' &&
            isset($tree['en'][$group]) &&
            is_array($tree['en'][$group]) &&
            count($tree['en'][$group]) !== count($translations)
        ) {
            $translations = array_merge($tree['en'][$group], $translations);
        }

        $file = $locale . '/' . $group;

        if (! File::isDirectory(lang_path($locale))) {
            File::makeDirectory(lang_path($locale), 755, true);
        }

        $groups = explode('/', $group);
        if (count($groups) > 1) {
            $folderName = Arr::last($groups);
            Arr::forget($groups, count($groups) - 1);

            $dir = 'vendor/' . implode('/', $groups) . '/' . $locale;
            if (! File::isDirectory(lang_path($dir))) {
                File::makeDirectory(lang_path($dir), 755, true);
            }

            $file = $dir . '/' . $folderName;
        }

        $path = lang_path($file . '.php');
        $output = "<?php\n\nreturn " . VarExporter::export($translations) . ";\n";

        File::put($path, $output);
    }

    public function getConfig(string|null $key = null): string|array|null
    {
        if ($key == null) {
            return $this->config;
        }

        return $this->config[$key];
    }

    public function removeUnusedThemeTranslations(): bool
    {
        if (! defined('THEME_MODULE_SCREEN_NAME')) {
            File::deleteDirectory(lang_path('vendor/themes'));

            return false;
        }

        $existingThemes = BaseHelper::scanFolder(theme_path());

        foreach (BaseHelper::scanFolder(lang_path('vendor/themes')) as $theme) {
            if (! in_array($theme, $existingThemes)) {
                File::deleteDirectory(lang_path("vendor/themes/$theme"));
            }
        }

        $theme = Theme::getThemeName();

        foreach ($this->files->allFiles(lang_path("vendor/themes/$theme")) as $file) {
            if ($this->files->isFile($file) && $file->getExtension() === 'json') {
                $locale = $file->getFilenameWithoutExtension();

                if ($locale == 'en') {
                    continue;
                }

                $translations = BaseHelper::getFileData($file->getRealPath());

                $defaultEnglishFile = theme_path("$theme/lang/en.json");

                if ($defaultEnglishFile) {
                    $enTranslations = BaseHelper::getFileData($defaultEnglishFile);
                    $translations = array_merge($enTranslations, $translations);

                    $enTranslationKeys = array_keys($enTranslations);

                    foreach ($translations as $key => $translation) {
                        if (! in_array($key, $enTranslationKeys)) {
                            Arr::forget($translations, $key);
                        }
                    }
                }

                ksort($translations);

                $this->files->put(
                    $file->getRealPath(),
                    json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            }
        }

        return true;
    }

    public function getRemoteAvailableLocales(): array
    {
        try {
            $info = Http::withoutVerifying()
                ->asJson()
                ->acceptJson()
                ->get('https://api.github.com/repos/botble/translations/git/trees/master');

            if (! $info->ok()) {
                return ['ar', 'es', 'vi'];
            }

            $info = $info->json();

            $availableLocales = [];

            foreach ($info['tree'] as $tree) {
                if (in_array($tree['path'], ['.gitignore', 'README.md'])) {
                    continue;
                }

                $availableLocales[] = $tree['path'];
            }
        } catch (Throwable) {
            $availableLocales = ['ar', 'es', 'vi'];
        }

        return $availableLocales;
    }

    public function downloadRemoteLocale(string $locale): array
    {
        $this->ensureAllDirectoriesAreCreated();

        $repository = 'https://github.com/botble/translations';

        $destination = storage_path('app/translation-files.zip');

        $availableLocales = $this->getRemoteAvailableLocales();

        if (! in_array($locale, $availableLocales)) {
            return [
                'error' => true,
                'message' => sprintf('This locale is not available on %s', $repository),
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->sink(Utils::tryFopen($destination, 'w'))
                ->get($repository . '/archive/refs/heads/master.zip');

            if (! $response->ok()) {
                return [
                    'error' => true,
                    'message' => $response->reason(),
                ];
            }
        } catch (Throwable $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }

        $zip = new Zipper();

        $zip->extract($destination, storage_path('app'));

        if (File::exists($destination)) {
            unlink($destination);
        }

        $localePath = storage_path('app/translations-master/' . $locale);

        File::copyDirectory($localePath . '/' . $locale, lang_path($locale));
        File::copyDirectory($localePath . '/vendor/core', lang_path('vendor/core'));
        File::copyDirectory($localePath . '/vendor/packages', lang_path('vendor/packages'));
        File::copyDirectory($localePath . '/vendor/plugins', lang_path('vendor/plugins'));

        $theme = Theme::getThemeName();

        $themeVendorLangPath = lang_path("vendor/themes/$theme");

        File::ensureDirectoryExists($themeVendorLangPath);

        if (File::exists($themeJsonPath = "$localePath/vendor/themes/$theme/$locale.json")) {
            File::copy($themeJsonPath, $themeVendorLangPath . "/$locale.json");
        } else {
            $jsonFile = $localePath . '/' . $locale . '.json';

            if (File::exists($jsonFile)) {
                File::copy($jsonFile, $themeVendorLangPath . "/$locale.json");
            }

            $this->removeUnusedThemeTranslations();
        }

        File::deleteDirectory(storage_path('app/translations-master'));

        foreach (File::directories(lang_path('vendor/packages')) as $package) {
            if (! File::isDirectory(package_path(File::basename($package)))) {
                File::deleteDirectory($package);
            }
        }

        foreach (File::directories(lang_path('vendor/plugins')) as $plugin) {
            if (! File::isDirectory(plugin_path(File::basename($plugin)))) {
                File::deleteDirectory($plugin);
            }
        }

        return [
            'error' => false,
            'message' => 'Downloaded translation files!',
        ];
    }

    public function getThemeTranslations(string $locale): array
    {
        $translations = BaseHelper::getFileData($themeTranslationsFilePath = $this->getThemeTranslationPath($locale));

        ksort($translations);

        $defaultEnglishFile = theme_path(Theme::getThemeName() . '/lang/en.json');

        if ($defaultEnglishFile && ($locale !== 'en' || $defaultEnglishFile !== $themeTranslationsFilePath)) {
            $enTranslations = BaseHelper::getFileData($defaultEnglishFile);
            $translations = array_merge($enTranslations, $translations);

            $enTranslationKeys = array_keys($enTranslations);

            foreach ($translations as $key => $translation) {
                if (! in_array($key, $enTranslationKeys)) {
                    Arr::forget($translations, $key);
                }
            }
        }

        return array_combine(array_map('trim', array_keys($translations)), $translations);
    }

    public function getThemeTranslationPath(string $locale): string
    {
        $theme = Theme::getThemeName();

        $localeFilePath = $defaultLocaleFilePath = lang_path("vendor/themes/$theme/$locale.json");

        if (! File::exists($localeFilePath)) {
            $localeFilePath = lang_path("$locale.json");
        }

        if (! File::exists($localeFilePath)) {
            $localeFilePath = $defaultLocaleFilePath;

            File::ensureDirectoryExists(dirname($localeFilePath));

            $themeLangPath = theme_path("$theme/lang/$locale.json");

            if (! File::exists($themeLangPath)) {
                $themeLangPath = theme_path("$theme/lang/en.json");
            }

            File::copy($themeLangPath, $localeFilePath);
        }

        return $localeFilePath;
    }

    public function saveThemeTranslations(string $locale, array $translations): bool
    {
        ksort($translations);

        return BaseHelper::saveFileData($this->getThemeTranslationPath($locale), $translations);
    }

    public function ensureAllDirectoriesAreCreated(): void
    {
        $this->files->ensureDirectoryExists(lang_path('vendor'));
        $this->files->ensureDirectoryExists(lang_path('vendor/core'));
        $this->files->ensureDirectoryExists(lang_path('vendor/packages'));
        $this->files->ensureDirectoryExists(lang_path('vendor/plugins'));
    }
}
