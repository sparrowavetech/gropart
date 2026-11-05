<?php

namespace SparroWave\SellerInvoicing\Hooks;

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Marketplace\Forms\Settings\MarketplaceSettingForm;
use Botble\Setting\Facades\Setting;
use Illuminate\Http\Request;

class MarketplaceSettingHookListener
{
    public static function extendForm(MarketplaceSettingForm $form): void
    {
        // 0. Platform Fee (%)
        if (! $form->has('marketplace_default_platform_fee') && ! $form->has('default_platform_fee')) {
            $platformFeeValue = (float) (
                setting('marketplace_default_platform_fee') ?:
                setting('default_platform_fee') ?:
                setting('seller_invoicing_platform_fee', 8)
            );

            if ($form->has('fee_per_order')) {
                $form->addAfter(
                    'fee_per_order',
                    'marketplace_default_platform_fee',
                    NumberField::class,
                    NumberFieldOption::make()
                        ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee'))
                        ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee_helper'))
                        ->value($platformFeeValue)
                        ->addAttribute('min', 0)
                        ->addAttribute('max', 100)
                        ->addAttribute('step', '0.01')
                );
            } else {
                $form->add(
                    'marketplace_default_platform_fee',
                    NumberField::class,
                    NumberFieldOption::make()
                        ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee'))
                        ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.platform_fee_helper'))
                        ->value($platformFeeValue)
                        ->addAttribute('min', 0)
                        ->addAttribute('max', 100)
                        ->addAttribute('step', '0.01')
                );
            }
        }

        // 1. System Fees Tax (%)
        if (! $form->has('default_fee_tax')) {
            $taxValue = setting('marketplace_default_fee_tax', setting('default_fee_tax', setting('seller_invoicing_tax_rate', 18)));

            if ($form->has('fee_per_order_fixed')) {
                $form->addAfter(
                    'fee_per_order_fixed',
                    'default_fee_tax',
                    NumberField::class,
                    NumberFieldOption::make()
                        ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.default_fee_tax'))
                        ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.default_fee_tax_helper'))
                        ->value((float) $taxValue)
                        ->addAttribute('min', 0)
                        ->addAttribute('max', 100)
                        ->addAttribute('step', '0.01')
                );
            } else {
                $form->add(
                    'default_fee_tax',
                    NumberField::class,
                    NumberFieldOption::make()
                        ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.default_fee_tax'))
                        ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.default_fee_tax_helper'))
                        ->value((float) $taxValue)
                        ->addAttribute('min', 0)
                        ->addAttribute('max', 100)
                        ->addAttribute('step', '0.01')
                );
            }
        }

        // 2. Authorised Signature Image for Seller Invoice
        if (! $form->has('authorised_signature_image')) {
            $sigValue = setting('marketplace_authorised_signature_image', setting('authorised_signature_image', setting('seller_invoicing_signature_image', '')));

            $form->add(
                'authorised_signature_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.authorised_signature_image'))
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.authorised_signature_image_helper'))
                    ->value($sigValue)
            );
        }

        // 3. Seller Invoice (Standard & Format) Section Header
        if (! $form->has('seller_invoice_format_header')) {
            $form->add(
                'seller_invoice_format_header',
                HtmlField::class,
                [
                    'html' => sprintf(
                        '<div class="col-12 mt-4 pt-3 border-top"><h4 class="text-primary mb-1"><i class="ti ti-file-invoice"></i> %s</h4><p class="text-muted small mb-3">%s</p></div>',
                        e(trans('plugins/seller-invoicing::seller-invoicing.settings.seller_invoice_standard_format')),
                        e(trans('plugins/seller-invoicing::seller-invoicing.settings.seller_invoice_format_desc'))
                    ),
                ]
            );
        }

        // 4. Invoice Code Prefix
        if (! $form->has('seller_invoice_code_prefix')) {
            $prefixValue = setting('seller_invoice_code_prefix', setting('seller_invoicing_prefix', 'SINV-'));

            $form->add(
                'seller_invoice_code_prefix',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_prefix'))
                    ->placeholder('SINV-')
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_prefix_helper'))
                    ->value($prefixValue)
            );
        }

        // 5. Invoice Code Suffix
        if (! $form->has('seller_invoice_code_suffix')) {
            $suffixValue = setting('seller_invoice_code_suffix', '');

            $form->add(
                'seller_invoice_code_suffix',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_suffix'))
                    ->placeholder('-2026')
                    ->helperText(trans('plugins/seller-invoicing::seller-invoicing.settings.invoice_code_suffix_helper'))
                    ->value($suffixValue)
            );
        }
    }

    public static function saveSettings(Request $request): void
    {
        $hasAny = false;

        if ($request->has('marketplace_default_platform_fee') || $request->has('default_platform_fee')) {
            $platformFee = (string) (float) $request->input('marketplace_default_platform_fee', $request->input('default_platform_fee'));
            Setting::set('marketplace_default_platform_fee', $platformFee);
            Setting::set('default_platform_fee', $platformFee);
            Setting::set('seller_invoicing_platform_fee', $platformFee);
            $hasAny = true;
        }

        if ($request->has('default_fee_tax')) {
            $tax = (string) (float) $request->input('default_fee_tax');
            Setting::set('marketplace_default_fee_tax', $tax);
            Setting::set('default_fee_tax', $tax);
            Setting::set('seller_invoicing_tax_rate', $tax);
            $hasAny = true;
        }

        if ($request->has('authorised_signature_image')) {
            $sig = (string) $request->input('authorised_signature_image');
            Setting::set('marketplace_authorised_signature_image', $sig);
            Setting::set('authorised_signature_image', $sig);
            Setting::set('seller_invoicing_signature_image', $sig);
            $hasAny = true;
        }

        if ($request->has('seller_invoice_code_prefix')) {
            $prefix = (string) $request->input('seller_invoice_code_prefix');
            Setting::set('seller_invoice_code_prefix', $prefix);
            Setting::set('seller_invoicing_prefix', $prefix);
            $hasAny = true;
        }

        if ($request->has('seller_invoice_code_suffix')) {
            $suffix = (string) $request->input('seller_invoice_code_suffix');
            Setting::set('seller_invoice_code_suffix', $suffix);
            $hasAny = true;
        }

        if ($hasAny) {
            Setting::save();
        }
    }
}

