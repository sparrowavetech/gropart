<?php

namespace VigStudio\VigAutoTranslations\Commands;

use Botble\Base\Facades\BaseHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use VigStudio\VigAutoTranslations\Manager;

#[AsCommand('vig-translate:auto', 'Auto translate from English to a new language')]
class AutoTranslateCommand extends Command
{
    public function handle(): int
    {
        if (! preg_match('/^[a-z0-9\-_]+$/i', $this->argument('locale'))) {
            $this->components->error('Only alphabetic characters are allowed.');

            return self::FAILURE;
        }

        $locale = $this->argument('locale');

        $this->components->info(sprintf('Translating %s...', $locale));

        $manager = app(Manager::class);

        if ($path = $this->option('path')) {
            $themeName = $this->getThemeNameFromPath($path);

            $langPath = $path;

            if ($this->laravel['files']->exists($translatedPath = lang_path("vendor/themes/$themeName/$locale.json"))) {
                $langPath = $translatedPath;
            }

            $translations = BaseHelper::getFileData($langPath);
        } else {
            $translations = $manager->getThemeTranslations($locale);
        }

        $this->info(sprintf('Translating %d words.', count($translations)));

        $count = 0;
        foreach ($translations as $key => $translation) {
            if ($key != $translation) {
                continue;
            }

            $translated = $manager->translate('en', $locale, $key);

            if ($translated != $key) {
                $this->info(sprintf('Translated "%s" to "%s"', $key, $translated));

                $translations[$key] = $translated;

                $count++;
            }
        }

        if ($path) {
            $themeName = $this->getThemeNameFromPath($path);

            BaseHelper::saveFileData(lang_path("vendor/themes/$themeName/$locale.json"), $translations);
        } else {
            $manager->saveThemeTranslations($locale, $translations);
        }

        $this->components->info(sprintf('Done! %d has been translated.', $count));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('locale', InputArgument::REQUIRED, 'The locale name that you want to translate');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to the original JSON file');
    }

    protected function getThemeNameFromPath(string $path): string
    {
        $themeName = basename(dirname($path, 2));
        if (str_contains($path, 'lang/vendor/themes')) {
            $themeName = basename(dirname($path));
        }

        return $themeName;
    }
}
