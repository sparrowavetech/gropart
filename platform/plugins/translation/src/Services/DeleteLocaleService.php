<?php

namespace Botble\Translation\Services;

use Botble\Theme\Facades\Theme;
use Botble\Translation\Exceptions\FileNotWritableException;
use Botble\Translation\Models\Translation;
use Illuminate\Support\Facades\File;

class DeleteLocaleService
{
    public function handle(string $locale): void
    {
        if ($locale === 'en') {
            return;
        }

        if (! File::isWritable(lang_path()) || ! File::isWritable(lang_path('vendor'))) {
            throw new FileNotWritableException();
        }

        $defaultLocale = lang_path($locale);
        if (File::exists($defaultLocale)) {
            File::deleteDirectory($defaultLocale);
        }

        if (File::exists(lang_path($locale . '.json'))) {
            File::delete(lang_path($locale . '.json'));
        }

        if (File::isDirectory($themeLangPath = lang_path('vendor/themes/' . Theme::getThemeName()))) {
            File::deleteDirectory($themeLangPath);
            if (File::isEmptyDirectory(dirname($themeLangPath))) {
                File::deleteDirectory(dirname($themeLangPath));
            }
        }

        $this->deleteLocalFile(lang_path('vendor/core'), $locale);
        $this->deleteLocalFile(lang_path('vendor/packages'), $locale);
        $this->deleteLocalFile(lang_path('vendor/plugins'), $locale);

        Translation::query()->where('locale', $locale)->delete();
    }

    protected function deleteLocalFile(string $path, string $locale): void
    {
        $folders = File::directories($path);

        foreach ($folders as $module) {
            foreach (File::directories($module) as $item) {
                if (File::name($item) == $locale) {
                    File::deleteDirectory($item);
                }
            }
        }
    }
}
