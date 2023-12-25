<?php

namespace Botble\Translation\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Translation\Exceptions\FileNotWritableException;
use Illuminate\Support\Facades\File;

class DeleteLocaleService
{
    public function handle(string $locale): void
    {
        if ($locale === 'en') {
            return;
        }

        $langPaths = [
            lang_path($locale),
            lang_path(sprintf('%s.json', $locale)),
        ];

        $langPaths = array_merge($langPaths, File::glob(lang_path(BaseHelper::joinPaths(['vendor', '*', '*', $locale]))));
        $langPaths = array_merge($langPaths, File::glob(lang_path(BaseHelper::joinPaths(['vendor', '*', '*', $locale . '.json']))));

        foreach ($langPaths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            if (! File::isWritable($path)) {
                throw new FileNotWritableException(sprintf('Could not delete %s', $path));
            }

            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            } else {
                File::delete($path);
            }
        }
    }
}
