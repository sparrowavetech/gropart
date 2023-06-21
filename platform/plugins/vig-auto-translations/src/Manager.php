<?php

namespace VigStudio\VigAutoTranslations;

use BaseHelper;
use Illuminate\Support\Str;
use Theme;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use VigStudio\VigAutoTranslations\Contracts\Translator;

class Manager
{
    protected Translator $translator;

    public function setDriver(Translator $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function translate(string $source, string $target, string $value): string|null
    {
        $originalValue = $value;

        $value = str_replace('%', '#_#', $value);

        $variables = array_values(array_filter(explode(' ', $value), fn ($item) => Str::startsWith($item, ':')));

        foreach ($variables as $item) {
            $value = str_replace($item, '%s', $value);
        }

        $translated = $this->translator->translate($source, $target, $value);
        $translated = sprintf($translated, ...$variables);

        $translated = str_replace('#_#', '%', $translated);
        $translated = str_replace('#_ #', '%', $translated);

        $translatedVariables = array_values(array_filter(explode(' ', $translated), fn ($item) => Str::startsWith($item, ':')));

        if (count($translatedVariables) == count($variables)) {
            return $translated;
        }

        return $originalValue;
    }

    public function getThemeTranslations(string $locale): array
    {
        $translations = BaseHelper::getFileData($this->getThemeTranslationPath($locale));

        ksort($translations);

        if ($locale !== 'en' && $defaultEnglishFile = theme_path(Theme::getThemeName() . '/lang/en.json')) {
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
}
