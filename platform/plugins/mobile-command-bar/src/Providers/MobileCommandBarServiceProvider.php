<?php

namespace Botble\MobileCommandBar\Providers;

use Botble\MobileCommandBar\Http\Middleware\InjectMobileCommandBar;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Throwable;

class MobileCommandBarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->setNamespace('plugins/mobile-command-bar')
            ->loadHelpers();
    }

    public function boot(): void
    {
        $this->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        $this->publishAssets();
        $this->registerAdminMenu();
        $this->registerFrontendMiddleware();
    }

    /**
     * Push the injection middleware onto the global web stack instead of
     * relying on a specific theme hook name, so the bar renders correctly
     * regardless of the active theme or Botble minor version.
     */
    protected function registerFrontendMiddleware(): void
    {
        try {
            $this->app->make(Kernel::class)->pushMiddleware(InjectMobileCommandBar::class);
        } catch (Throwable) {
            // If the kernel contract changes in a future core version,
            // fail silently rather than breaking the whole application.
        }
    }

    protected function registerAdminMenu(): void
    {
        if (! class_exists(\Botble\Base\Facades\DashboardMenu::class)) {
            return;
        }

        try {
            \Botble\Base\Facades\DashboardMenu::registerItem([
                'id' => 'cms-plugins-mobile-command-bar',
                'priority' => 500,
                'parent_id' => null,
                'name' => 'plugins/mobile-command-bar::mobile-command-bar.menu_name',
                'icon' => 'ti ti-device-mobile',
                'route' => 'mobile-command-bar.settings',
                'permissions' => ['mobile-command-bar.settings'],
            ]);
        } catch (Throwable) {
        }
    }

    /**
     * Minimal, dependency-free replacement for Botble\Base's
     * LoadAndPublishDataTrait, kept local so this plugin has no hard
     * compile-time dependency on trait method signatures that may change
     * between core versions.
     */
    protected string $namespace = 'plugins/mobile-command-bar';

    protected function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    protected function loadHelpers(): static
    {
        $file = dirname(__DIR__, 2) . '/helpers/helpers.php';

        if (file_exists($file)) {
            require_once $file;
        }

        return $this;
    }

    protected function loadAndPublishConfigurations(array $files): static
    {
        foreach ($files as $file) {
            $path = dirname(__DIR__, 2) . '/config/' . $file . '.php';

            if (file_exists($path)) {
                $this->mergeConfigFrom($path, 'plugins.mobile-command-bar.' . $file);
            }
        }

        return $this;
    }

    protected function loadAndPublishTranslations(): static
    {
        $this->loadTranslationsFrom(dirname(__DIR__, 2) . '/resources/lang', $this->namespace);

        return $this;
    }

    protected function loadAndPublishViews(): static
    {
        $this->loadViewsFrom(dirname(__DIR__, 2) . '/resources/views', $this->namespace);

        return $this;
    }

    protected function loadRoutes(array $files): static
    {
        foreach ($files as $file) {
            $path = dirname(__DIR__, 2) . '/routes/' . $file . '.php';

            if (! file_exists($path)) {
                continue;
            }

            try {
                $this->loadRoutesFrom($path);
            } catch (Throwable) {
                // A route-registration helper that doesn't match this
                // core version must never block plugin activation.
            }
        }

        return $this;
    }

    protected function publishAssets(): static
    {
        $source = dirname(__DIR__, 2) . '/public';
        $target = public_path('vendor/core/plugins/mobile-command-bar');

        $this->publishes([
            $source => $target,
        ], 'mobile-command-bar-assets');

        // Also copy the assets automatically so the plugin renders
        // correctly right after activation, without requiring the site
        // owner to remember to run `php artisan vendor:publish`.
        try {
            if (is_dir($source) && ! is_dir($target)) {
                (new \Illuminate\Filesystem\Filesystem())->copyDirectory($source, $target);
            }
        } catch (Throwable) {
        }

        return $this;
    }
}
