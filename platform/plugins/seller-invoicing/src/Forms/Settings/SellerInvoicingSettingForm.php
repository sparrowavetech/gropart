<?php

namespace SparroWave\SellerInvoicing\Forms\Settings;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use SparroWave\SellerInvoicing\Http\Requests\SellerInvoicingSettingRequest;

class SellerInvoicingSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(SellerInvoicingSettingRequest::class)
            ->setSectionTitle(trans('plugins/seller-invoicing::seller-invoicing.settings.title'))
            ->setSectionDescription(trans('plugins/seller-invoicing::seller-invoicing.settings.description'))
            ->add(
                'seller_invoicing_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.enable'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.enable_helper'))
                    ->value((bool) setting('seller_invoicing_enabled', true))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_prefix',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.prefix'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.prefix_helper'))
                    ->value(in_array(setting('seller_invoicing_prefix'), ['GPINV-', 'GPINV']) ? 'GP' : setting('seller_invoicing_prefix', setting('seller_invoice_code_prefix', 'GP')))
                    ->toArray()
            )
            ->add(
                'seller_invoice_code_suffix',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_suffix'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_suffix_helper'))
                    ->value(setting('seller_invoice_code_suffix', 'SIN'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_tax_mode',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_mode'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_mode_helper'))
                    ->choices([
                        'auto' => trans('plugins/seller-invoicing::seller-invoicing.settings.tax_modes.auto'),
                        'dual_gst' => trans('plugins/seller-invoicing::seller-invoicing.settings.tax_modes.dual_gst'),
                        'flat_tax' => trans('plugins/seller-invoicing::seller-invoicing.settings.tax_modes.flat_tax'),
                        'exempt' => trans('plugins/seller-invoicing::seller-invoicing.settings.tax_modes.exempt'),
                    ])
                    ->selected(setting('seller_invoicing_tax_mode', 'auto'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_fee_tax_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.fee_tax_type'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.fee_tax_type_helper'))
                    ->choices([
                        'exclusive' => trans('plugins/seller-invoicing::seller-invoicing.settings.fee_tax_types.exclusive'),
                        'inclusive' => trans('plugins/seller-invoicing::seller-invoicing.settings.fee_tax_types.inclusive'),
                    ])
                    ->selected(setting('seller_invoicing_fee_tax_type', 'exclusive'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_tax_rate',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_rate'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_rate_helper'))
                    ->value(setting('seller_invoicing_tax_rate', 18))
                    ->step(0.01)
                    ->toArray()
            )
            ->add(
                'seller_invoicing_platform_fee',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee_helper'))
                    ->value((float) (setting('marketplace_default_platform_fee') ?: setting('default_platform_fee') ?: setting('seller_invoicing_platform_fee', 8)))
                    ->addAttribute('min', 0)
                    ->addAttribute('max', 100)
                    ->step(0.01)
                    ->toArray()
            )
            ->add(
                'seller_invoicing_tax_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_label'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_label_helper'))
                    ->value(setting('seller_invoicing_tax_label', 'GST'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_tax_id_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_id_label'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.tax_id_label_helper'))
                    ->value(setting('seller_invoicing_tax_id_label', 'GSTIN'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_sac_commission',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.sac_commission'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.sac_commission_helper'))
                    ->value(setting('seller_invoicing_sac_commission', '998311'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_sac_shipping',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.sac_shipping'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.sac_shipping_helper'))
                    ->value(setting('seller_invoicing_sac_shipping', '9965'))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_signatory_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.signatory_name'))
                    ->value(setting('seller_invoicing_signatory_name', ''))
                    ->toArray()
            )
            ->add(
                'seller_invoicing_signature_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.signature_image'))
                    ->value(setting('seller_invoicing_signature_image', ''))
                    ->toArray()
            )
            ->add(
                'modal_regenerate_script',
                HtmlField::class,
                [
                    'html' => view('plugins/seller-invoicing::settings-modal')->render(),
                ]
            );
    }
}

