<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Rules\OnOffRule;
use Botble\Ecommerce\Forms\Settings\ShoppingSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\ShoppingSettingRequest;
use Illuminate\Support\ServiceProvider;

class ProductDescriptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ShoppingSettingForm::extend(function (ShoppingSettingForm $form): void {
            $form->add('enable_full_page_product_description', OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label('Enable Full Page Product Description')
                    ->helperText('Show product information as full-width sections. Turn off to use tabs.')
                    ->value(get_ecommerce_setting('enable_full_page_product_description', false))
            );
        });

        add_filter('core_request_rules', function (array $rules, $request): array {
            if ($request instanceof ShoppingSettingRequest) {
                $rules['enable_full_page_product_description'] = new OnOffRule();
            }
            return $rules;
        }, 20, 2);

        foreach (['farmart', 'farmart-child'] as $theme) {
            $this->app['view']->composer("theme.{$theme}::views.ecommerce.product", function ($view) use ($theme): void {
                $view->setPath(plugin_path("farmart-helper/resources/views/product-description/{$theme}.blade.php"));
            });
        }
    }
}
