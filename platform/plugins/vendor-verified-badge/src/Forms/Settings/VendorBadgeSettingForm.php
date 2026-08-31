<?php

namespace SparroWave\VendorVerifiedBadge\Forms\Settings;

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use SparroWave\VendorVerifiedBadge\Http\Requests\VendorBadgeSettingRequest;

class VendorBadgeSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/vendor-verified-badge::vendor-badge.settings.title'))
            ->setSectionDescription(trans('plugins/vendor-verified-badge::vendor-badge.settings.description'))
            ->setValidatorClass(VendorBadgeSettingRequest::class)
            ->add(
                'vendor_verified_badge_icon',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.settings.badge_icon'))
                    ->helperText(trans('plugins/vendor-verified-badge::vendor-badge.settings.badge_icon_helper'))
                    ->value(setting('vendor_verified_badge_icon'))
            )
            ->add(
                'vendor_verified_tooltip_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.settings.tooltip_text'))
                    ->helperText(trans('plugins/vendor-verified-badge::vendor-badge.settings.tooltip_text_helper'))
                    ->value(setting('vendor_verified_tooltip_text', '✓ Gropart Verified Seller — Business & GST Authenticated'))
            )
            ->add(
                'vendor_verified_application_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.settings.application_url'))
                    ->helperText(trans('plugins/vendor-verified-badge::vendor-badge.settings.application_url_helper'))
                    ->value(setting('vendor_verified_application_url', 'https://forms.gle/41n8QhD2hA7q3cE38'))
            )
            ->add(
                'vendor_verified_menu_gating_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.settings.menu_gating_enabled'))
                    ->helperText(trans('plugins/vendor-verified-badge::vendor-badge.settings.menu_gating_enabled_helper'))
                    ->value(setting('vendor_verified_menu_gating_enabled', true))
            )
            ->add(
                'vendor_verified_completion_threshold',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.settings.completion_threshold'))
                    ->helperText(trans('plugins/vendor-verified-badge::vendor-badge.settings.completion_threshold_helper'))
                    ->value(setting('vendor_verified_completion_threshold', 80))
            );
    }
}
