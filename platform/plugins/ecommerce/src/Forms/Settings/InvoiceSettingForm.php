<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\Fields\GoogleFontsField;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\Settings\InvoiceSettingRequest;
use Botble\Setting\Forms\SettingForm;

class InvoiceSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.invoice.company_settings'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.invoice.company_settings_description'))
            ->setValidatorClass(InvoiceSettingRequest::class)
            ->add('company_name_for_invoicing', 'text', [
                'label' => trans('plugins/ecommerce::setting.invoice.form.company_name'),
                'value' => get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name'),
            ])
            ->add('company_address_for_invoicing', 'text', [
                'label' => trans('plugins/ecommerce::setting.invoice.form.company_address'),
                'value' => get_ecommerce_setting('company_address_for_invoicing') ?:
                    implode(
                        ', ',
                        array_filter([
                            get_ecommerce_setting('store_address'),
                            get_ecommerce_setting('store_city'),
                            get_ecommerce_setting('store_state'),
                            EcommerceHelper::getCountryNameById(get_ecommerce_setting('store_country')),
                        ]),
                    ),
            ]);

        if (EcommerceHelper::isZipCodeEnabled()) {
            $this->add('company_zipcode_for_invoicing', 'text', [
                'label' => trans('plugins/ecommerce::setting.invoice.form.company_zipcode'),
                'value' => get_ecommerce_setting('company_zipcode_for_invoicing') ?: get_ecommerce_setting('store_zip_code'),
            ]);
        }

        $this->add('company_email_for_invoicing', 'text', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.company_email'),
            'value' => get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email'),
        ])
        ->add('company_phone_for_invoicing', 'text', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.company_phone'),
            'value' => get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone'),
        ])
        ->add('company_tax_id_for_invoicing', 'text', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.company_tax_id'),
            'value' => get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting('store_vat_number'),
        ])
        ->add('company_logo_for_invoicing', 'mediaImage', [
            'value' => get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option('logo_in_invoices') ?: theme_option('logo')),
            'allow_thumb' => false,
        ])
        ->add('using_custom_font_for_invoice', 'onOffCheckbox', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.using_custom_font_for_invoice'),
            'value' => get_ecommerce_setting('using_custom_font_for_invoice', false),
            'attr' => [
                'data-bb-toggle' => 'collapse',
                'data-bb-target' => '.custom-font-settings',
            ],
        ])
        ->add('open_fieldset_custom_font_settings', 'html', [
            'html' => sprintf(
                '<fieldset class="form-fieldset custom-font-settings" style="display: %s;" data-bb-value="1">',
                get_ecommerce_setting('using_custom_font_for_invoice', false) ? 'block' : 'none'
            ),
        ])
        ->add('invoice_font_family', GoogleFontsField::class, [
            'label' => trans('plugins/ecommerce::setting.invoice.form.invoice_font_family'),
            'selected' => get_ecommerce_setting('invoice_font_family'),
        ])
        ->add('close_fieldset_custom_font_settings', 'html', [
            'html' => '</fieldset>',
        ])
        ->add('invoice_support_arabic_language', 'onOffCheckbox', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.invoice_support_arabic_language'),
            'value' => get_ecommerce_setting('invoice_support_arabic_language', false),
        ])
        ->add('enable_invoice_stamp', 'onOffCheckbox', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.enable_invoice_stamp'),
            'value' => get_ecommerce_setting('enable_invoice_stamp', true),
        ])
        ->add('invoice_code_prefix', 'text', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.invoice_code_prefix'),
            'value' => get_ecommerce_setting('invoice_code_prefix', 'INV-'),
        ])
        ->add('disable_order_invoice_until_order_confirmed', 'onOffCheckbox', [
            'label' => trans('plugins/ecommerce::setting.invoice.form.disable_order_invoice_until_order_confirmed'),
            'value' => EcommerceHelper::disableOrderInvoiceUntilOrderConfirmed(),
        ]);
    }
}
