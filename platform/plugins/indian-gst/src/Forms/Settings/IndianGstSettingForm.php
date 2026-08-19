<?php

namespace SparroWave\IndianGst\Forms\Settings;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use SparroWave\IndianGst\Http\Requests\IndianGstSettingRequest;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class IndianGstSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $states = IndianGstHelper::getIndianStates();

        $this
            ->setSectionTitle(trans('plugins/indian-gst::indian-gst.settings.title'))
            ->setSectionDescription(trans('plugins/indian-gst::indian-gst.settings.description'))
            ->setValidatorClass(IndianGstSettingRequest::class)
            ->add(
                'indian_gst_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/indian-gst::indian-gst.settings.enable_gst'))
                    ->helperText(trans('plugins/indian-gst::indian-gst.settings.enable_gst_helper'))
                    ->value((bool) setting('indian_gst_enabled', true))
                    ->toArray()
            )
            ->add(
                'indian_gst_display_inclusive_price',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/indian-gst::indian-gst.settings.inclusive_price'))
                    ->helperText(trans('plugins/indian-gst::indian-gst.settings.inclusive_price_helper'))
                    ->value((bool) setting('indian_gst_display_inclusive_price', true))
                    ->toArray()
            )
            ->add(
                'indian_gst_default_company_state',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/indian-gst::indian-gst.settings.company_state'))
                    ->choices($states)
                    ->selected(setting('indian_gst_default_company_state', 'Rajasthan'))
                    ->toArray()
            )
            ->add(
                'indian_gst_default_company_gstin',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/indian-gst::indian-gst.settings.company_gstin'))
                    ->placeholder('e.g. 08AUBPA5903F1Z5')
                    ->value(setting('indian_gst_default_company_gstin', '08AUBPA5903F1Z5'))
                    ->toArray()
            );
    }
}
