<?php

namespace SparroWave\RazorpayEasyEmi\Forms;

use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use SparroWave\RazorpayEasyEmi\Http\Requests\RazorpayEasyEmiSettingRequest;

class RazorpayEasyEmiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.title'))
            ->setSectionDescription(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.description'))
            ->setValidatorClass(RazorpayEasyEmiSettingRequest::class)
            ->add(
                'razorpay_easy_emi_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enabled_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enabled_helper'))
                    ->value(setting('razorpay_easy_emi_enabled', true))
            )
            ->add(
                'razorpay_easy_emi_min_amount',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.min_amount_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.min_amount_helper'))
                    ->value(setting('razorpay_easy_emi_min_amount', 2500))
            )
            ->add(
                'razorpay_easy_emi_default_scheme',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.default_scheme_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.default_scheme_helper'))
                    ->choices([
                        'standard' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard'),
                        'no_cost' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.no_cost'),
                        'low_cost' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.low_cost'),
                        'disabled' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.disabled'),
                    ])
                    ->selected(setting('razorpay_easy_emi_default_scheme', 'standard'))
            )
            ->add(
                'razorpay_easy_emi_auto_detect_key',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.auto_detect_key_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.auto_detect_key_helper'))
                    ->value(setting('razorpay_easy_emi_auto_detect_key', true))
            )
            ->add(
                'razorpay_easy_emi_custom_key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.custom_key_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.custom_key_helper'))
                    ->value(setting('razorpay_easy_emi_custom_key', ''))
            )
            ->add(
                'razorpay_easy_emi_custom_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.custom_secret_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.custom_secret_helper'))
                    ->value(setting('razorpay_easy_emi_custom_secret', ''))
            )
            ->add(
                'razorpay_easy_emi_enable_catalog_badge',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enable_catalog_badge_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enable_catalog_badge_helper'))
                    ->value(setting('razorpay_easy_emi_enable_catalog_badge', true))
            )
            ->add(
                'razorpay_easy_emi_enable_cart_banner',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enable_cart_banner_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.enable_cart_banner_helper'))
                    ->value(setting('razorpay_easy_emi_enable_cart_banner', true))
            )
            ->add(
                'razorpay_easy_emi_auto_select_checkout',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.auto_select_checkout_label'))
                    ->helperText(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.auto_select_checkout_helper'))
                    ->value(setting('razorpay_easy_emi_auto_select_checkout', true))
            );
    }
}

