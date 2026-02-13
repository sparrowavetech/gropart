<?php

namespace Shaqi\LlmsOptimizer\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\ServiceProvider;
use Shaqi\LlmsOptimizer\Facades\LlmsGenerator;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['events']->listen(CreatedContentEvent::class, function ($event): void {
            $this->clearCacheIfRelevant($event);
        });

        $this->app['events']->listen(UpdatedContentEvent::class, function ($event): void {
            $this->clearCacheIfRelevant($event);
        });

        $this->app['events']->listen(DeletedContentEvent::class, function ($event): void {
            $this->clearCacheIfRelevant($event);
        });
    }

    protected function clearCacheIfRelevant($event): void
    {
        if (! llms_optimizer_is_enabled()) {
            return;
        }

        // Get all slugable models dynamically
        $relevantClasses = array_keys(SlugHelper::supportedModels());

        $model = $event->data ?? null;

        if ($model && in_array(get_class($model), $relevantClasses)) {
            LlmsGenerator::clearCache();

            $outputMode = get_llms_optimizer_setting('output_mode', llms_optimizer_config('defaults.output_mode', 'dynamic'));

            if (in_array($outputMode, ['static', 'both'])) {
                LlmsGenerator::saveStaticFile();
            }
        }
    }
}

