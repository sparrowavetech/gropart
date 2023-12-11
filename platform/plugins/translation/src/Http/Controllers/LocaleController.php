<?php

namespace Botble\Translation\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Language;
use Botble\Translation\Exceptions\FileNotWritableException;
use Botble\Translation\Http\Requests\LocaleRequest;
use Botble\Translation\Services\CreateLocaleService;
use Botble\Translation\Services\DeleteLocaleService;
use Botble\Translation\Services\DownloadLocaleService;
use Illuminate\Support\Facades\File;
use Throwable;

class LocaleController extends BaseController
{
    public function index()
    {
        $this->pageTitle(trans('plugins/translation::translation.locales'));

        Assets::addScriptsDirectly('vendor/core/plugins/translation/js/locales.js');

        $existingLocales = Language::getAvailableLocales();
        $languages = Language::getListLanguages();
        $flags = Language::getListLanguageFlags();

        $locales = collect($languages)->pluck('2', '0')->unique()->all();

        return view('plugins/translation::locales', compact('existingLocales', 'locales', 'flags'));
    }

    public function store(LocaleRequest $request, CreateLocaleService $createLocaleService)
    {
        $locale = $request->input('locale');

        if (! File::isDirectory(lang_path($locale))) {
            $createLocaleService->handle($locale);
        }

        return $this
            ->httpResponse()
            ->withCreatedSuccessMessage();
    }

    public function destroy(string $locale, DeleteLocaleService $deleteLocaleService)
    {
        try {
            $deleteLocaleService->handle($locale);

            return $this
                ->httpResponse()
                ->withDeletedSuccessMessage();
        } catch (FileNotWritableException $e) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function download(string $locale, DownloadLocaleService $downloadLocaleService)
    {
        try {
            $file = $downloadLocaleService->handle($locale);

            return response()->download($file)->deleteFileAfterSend();
        } catch (Throwable $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
