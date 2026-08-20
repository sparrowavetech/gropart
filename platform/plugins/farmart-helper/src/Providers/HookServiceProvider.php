<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Forms\FieldOptions\PhoneNumberFieldOption;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Forms\Fronts\OrderTrackingForm;
use Botble\Ecommerce\Forms\Settings\GeneralSettingForm;
use Botble\Ecommerce\Forms\Settings\InvoiceSettingForm;
use Botble\Ecommerce\Forms\StoreLocatorForm;
use Botble\Marketplace\Forms\StoreForm;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Marketplace Store Form Extension (Admin & Vendor)
        if (class_exists(StoreForm::class)) {
            StoreForm::extend(function (StoreForm $form) {
                if ($form->has('phone')) {
                    $form->modify(
                        'phone',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(trans('plugins/marketplace::store.forms.phone'))
                            ->placeholder(trans('plugins/marketplace::store.forms.phone_placeholder'))
                            ->required()
                            ->withCountryCodeSelection()
                            ->colspan(6)
                    );
                }
            });
        }

        // 2. Ecommerce Store Locator Form Extension
        if (class_exists(StoreLocatorForm::class)) {
            StoreLocatorForm::extend(function (StoreLocatorForm $form) {
                if ($form->has('phone')) {
                    $form->modify(
                        'phone',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(trans('plugins/ecommerce::ecommerce.phone'))
                            ->required()
                            ->withCountryCodeSelection()
                            ->colspan(3)
                    );
                }
            });
        }

        // 3. Ecommerce General Setting Form Extension
        if (class_exists(GeneralSettingForm::class)) {
            GeneralSettingForm::extend(function (GeneralSettingForm $form) {
                if ($form->has('store_phone')) {
                    $form->modify(
                        'store_phone',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(trans('plugins/ecommerce::setting.general.form.store_phone'))
                            ->placeholder(trans('plugins/ecommerce::setting.general.form.store_phone_placeholder'))
                            ->helperText(trans('plugins/ecommerce::setting.general.form.store_phone_helper'))
                            ->value(get_ecommerce_setting('store_phone'))
                            ->withCountryCodeSelection()
                            ->colspan(3)
                    );
                }
            });
        }

        // 4. Ecommerce Invoice Setting Form Extension
        if (class_exists(InvoiceSettingForm::class)) {
            InvoiceSettingForm::extend(function (InvoiceSettingForm $form) {
                if ($form->has('company_phone_for_invoicing')) {
                    $form->modify(
                        'company_phone_for_invoicing',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(trans('plugins/ecommerce::setting.invoice.form.company_phone'))
                            ->placeholder(trans('plugins/ecommerce::setting.invoice.form.company_phone_placeholder'))
                            ->helperText(trans('plugins/ecommerce::setting.invoice.form.company_phone_helper'))
                            ->value(get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone'))
                            ->withCountryCodeSelection()
                            ->colspan(2)
                    );
                }
            });
        }

        // 5. Frontend Order Tracking Form Extension
        if (class_exists(OrderTrackingForm::class)) {
            OrderTrackingForm::extend(function (OrderTrackingForm $form) {
                if ($form->has('phone')) {
                    $form->modify(
                        'phone',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(__('Phone number'))
                            ->placeholder(__('Enter your phone number'))
                            ->required()
                            ->withCountryCodeSelection()
                    );
                }
            });
        }

        // 6. Intelligent Order Tracking Phone Query Normalizer
        add_filter('ecommerce_order_tracking_query', function ($query) {
            $phone = request()->input('phone') ?: request()->input('phone_display');
            if ($phone) {
                $cleanDigits = preg_replace('/[^0-9]/', '', (string) $phone);
                $last10 = substr($cleanDigits, -10);

                if ($last10) {
                    $query->orWhere(function ($q) use ($phone, $cleanDigits, $last10) {
                        $code = request()->input('order_id');
                        if ($code) {
                            $q->where(function ($sub) use ($code) {
                                $sub->where('ec_orders.code', $code)
                                    ->orWhere('ec_orders.code', '#' . $code);
                            });
                        }
                        $q->where(function ($sub) use ($phone, $cleanDigits, $last10) {
                            $sub->whereHas('address', function ($addr) use ($phone, $cleanDigits, $last10) {
                                $addr->where('phone', 'LIKE', "%{$last10}")
                                    ->orWhere('phone', $phone)
                                    ->orWhere('phone', "+{$cleanDigits}");
                            })->orWhereHas('user', function ($usr) use ($phone, $cleanDigits, $last10) {
                                $usr->where('phone', 'LIKE', "%{$last10}")
                                    ->orWhere('phone', $phone)
                                    ->orWhere('phone', "+{$cleanDigits}");
                            });
                        });
                    });
                }
            }

            return $query;
        });
    }
}
