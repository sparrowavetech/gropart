<?php

namespace SparroWave\ProductFreeShipping\Forms\Settings;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use SparroWave\ProductFreeShipping\Http\Requests\ProductFreeShippingSettingRequest;

class ProductFreeShippingSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/product-free-shipping::product-free-shipping.settings.title'))
            ->setSectionDescription(trans('plugins/product-free-shipping::product-free-shipping.settings.description'))
            ->setValidatorClass(ProductFreeShippingSettingRequest::class)
            ->add(
                'product_free_shipping_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/product-free-shipping::product-free-shipping.settings.enable_plugin'))
                    ->helperText(trans('plugins/product-free-shipping::product-free-shipping.settings.enable_plugin_help'))
                    ->value((bool) setting('product_free_shipping_enabled', true))
                    ->toArray()
            )
            ->add(
                'product_free_shipping_badge_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/product-free-shipping::product-free-shipping.settings.badge_text'))
                    ->helperText(trans('plugins/product-free-shipping::product-free-shipping.settings.badge_text_help'))
                    ->value(setting('product_free_shipping_badge_text', 'Free Delivery'))
                    ->toArray()
            )
            ->add(
                'product_free_shipping_method_title',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/product-free-shipping::product-free-shipping.settings.method_title'))
                    ->helperText(trans('plugins/product-free-shipping::product-free-shipping.settings.method_title_help'))
                    ->value(setting('product_free_shipping_method_title', 'Free Delivery'))
                    ->toArray()
            )
            ->add(
                'product_free_shipping_hide_other_methods',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/product-free-shipping::product-free-shipping.settings.hide_other_methods'))
                    ->helperText(trans('plugins/product-free-shipping::product-free-shipping.settings.hide_other_methods_help'))
                    ->value((bool) setting('product_free_shipping_hide_other_methods', false))
                    ->toArray()
            );
    }
}
