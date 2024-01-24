<?php

namespace Botble\Translation;

use ArrayAccess;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Services\DeleteUnusedTranslationFilesService;
use Botble\Base\Services\DownloadLocaleService;
use Botble\Base\Supports\ServiceProvider;
use Botble\Theme\Facades\Theme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;

class Manager
{
    protected array|ArrayAccess $config;

    protected DownloadLocaleService $downloadLocaleService;

    protected DeleteUnusedTranslationFilesService $deleteUnusedTranslationFilesService;

    public function __construct(protected Application $app, protected Filesystem $files)
    {
        $this->config = $app['config']['plugins.translation.general'];

        $this->downloadLocaleService = new DownloadLocaleService();
        $this->deleteUnusedTranslationFilesService = new DeleteUnusedTranslationFilesService();
    }

    public function publishLocales(): void
    {
        if (! $this->files->isDirectory(lang_path('vendor/themes'))) {
            $this->files->makeDirectory(lang_path('vendor/themes'));
        }

        $paths = ServiceProvider::pathsToPublish(null, 'cms-lang');

        foreach ($paths as $from => $to) {
            $this->files->ensureDirectoryExists(dirname($to));
            $this->files->copyDirectory($from, $to);
        }

        if (! $this->files->isDirectory(lang_path('en'))) {
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
        return $this->downloadLocaleService->getAvailableLocales();
    }

    public function downloadRemoteLocale(string $locale): array
    {
        $this->ensureAllDirectoriesAreCreated();

        try {
            $this->downloadLocaleService->handle($locale);
        } catch (Throwable $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->deleteUnusedTranslationFilesService->handle();

        $this->removeUnusedThemeTranslations();

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
