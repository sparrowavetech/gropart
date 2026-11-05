<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\PhoneNumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Forms\Fronts\OrderTrackingForm;
use Botble\Ecommerce\Forms\Settings\GeneralSettingForm;
use Botble\Ecommerce\Forms\Settings\InvoiceSettingForm;
use Botble\Ecommerce\Forms\StoreLocatorForm;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Marketplace\Forms\StoreForm;
use Illuminate\Support\Facades\Route;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PROCESS_CHECKOUT_RULES_REQUEST_ECOMMERCE, function (array $rules): array {
            foreach (['address', 'billing_address'] as $group) {
                if (! isset($rules["$group.address"])) {
                    continue;
                }

                $country = request()->input("$group.country") ?: EcommerceHelper::getDefaultCountryId();
                if (strtoupper(trim((string) $country)) === 'IN') {
                    $rules["$group.zip_code"] = ['required', 'regex:/^[1-9][0-9]{5}$/'];
                }
            }

            return $rules;
        }, 200);

        add_filter(PROCESS_CHECKOUT_MESSAGES_REQUEST_ECOMMERCE, function (array $messages): array {
            foreach (['address', 'billing_address'] as $group) {
                $messages["$group.zip_code.regex"] = trans('plugins/farmart-helper::pincode.invalid');
                $messages["$group.zip_code.required"] = trans('plugins/farmart-helper::pincode.invalid');
            }

            return $messages;
        }, 200);

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

        // Contain intl-tel-input in admin dialogs and responsive columns
        add_filter(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, function (?string $head): string {
            $css = <<<CSS
<style>
    .iti {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        box-sizing: border-box !important;
    }
    .iti input,
    .iti input[type=tel],
    .iti input[type=text],
    .iti .form-control {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .modal .modal-body {
        overflow-x: hidden;
    }
    .modal .col-lg-6,
    .modal .col-md-6,
    .modal [class*="col-"] {
        min-width: 0 !important;
    }
    .modal .iti {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        box-sizing: border-box !important;
    }
    .modal .iti input,
    .modal .iti .form-control {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
</style>
CSS;

            return ($head ?? '') . $css;
        }, 100);

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
                            ->wrapperAttributes([
                                'style' => 'max-width: 100%; min-width: 0;',
                            ])
                            ->attributes([
                                'style' => 'max-width: 100%; box-sizing: border-box;',
                            ])
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
                $form->columns(12);

                // Switch any existing full-width fields to span 12 in the new 12-column layout
                foreach ($form->getFields() as $fieldName => $field) {
                    if ($field->getOption('colspan') === 6) {
                        $field->setOption('colspan', 12);
                    }
                }

                if ($form->has('company_name_for_invoicing')) {
                    $form->getField('company_name_for_invoicing')->setOption('colspan', 12);
                }

                if ($form->has('company_address_for_invoicing')) {
                    $form->getField('company_address_for_invoicing')->setOption('colspan', 12);
                }

                $country = InvoiceHelper::getCompanyCountry() ?: EcommerceHelper::getFirstCountryId();
                $state = InvoiceHelper::getCompanyState();
                $city = InvoiceHelper::getCompanyCity();

                $loadLocationsFromPlugin = EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation();

                if ($loadLocationsFromPlugin) {
                    Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');

                    if ($form->has('company_country_for_invoicing')) {
                        $form->modify(
                            'company_country_for_invoicing',
                            SelectField::class,
                            SelectFieldOption::make()
                                ->label(trans('plugins/ecommerce::ecommerce.country'))
                                ->choices(EcommerceHelper::getAvailableCountries())
                                ->selected($country)
                                ->searchable()
                                ->attributes([
                                    'data-type' => 'country',
                                    'class' => 'form-select select-search-location',
                                ])
                                ->colspan(4),
                            true
                        );
                    }

                    if ($form->has('company_state_for_invoicing')) {
                        $states = EcommerceHelper::getAvailableStatesByCountry($country);
                        if ($state && ! is_numeric($state) && ($foundId = array_search($state, $states)) !== false) {
                            $state = (string) $foundId;
                        }

                        $form->modify(
                            'company_state_for_invoicing',
                            SelectField::class,
                            SelectFieldOption::make()
                                ->label(trans('plugins/ecommerce::ecommerce.state'))
                                ->emptyValue(trans('plugins/ecommerce::ecommerce.select_state'))
                                ->choices(['' => trans('plugins/ecommerce::ecommerce.select_state')] + $states)
                                ->selected($state)
                                ->searchable()
                                ->attributes([
                                    'data-type' => 'state',
                                    'data-url' => Route::has('ajax.states-by-country') ? route('ajax.states-by-country') : url('ajax/states-by-country'),
                                    'class' => 'form-select select-search-location',
                                ])
                                ->colspan(4),
                            true
                        );
                    }

                    if ($form->has('company_city_for_invoicing')) {
                        $cities = EcommerceHelper::getAvailableCitiesByState($state, $country);
                        if ($city && ! is_numeric($city) && ($foundCityId = array_search($city, $cities)) !== false) {
                            $city = (string) $foundCityId;
                        }

                        $form->modify(
                            'company_city_for_invoicing',
                            SelectField::class,
                            SelectFieldOption::make()
                                ->label(trans('plugins/ecommerce::ecommerce.city'))
                                ->emptyValue(trans('plugins/ecommerce::ecommerce.select_city'))
                                ->choices(['' => trans('plugins/ecommerce::ecommerce.select_city')] + $cities)
                                ->selected($city)
                                ->searchable()
                                ->attributes([
                                    'data-type' => 'city',
                                    'data-url' => Route::has('ajax.cities-by-state') ? route('ajax.cities-by-state') : url('ajax/cities-by-state'),
                                    'class' => 'form-select select-search-location',
                                ])
                                ->colspan(4),
                            true
                        );
                    }
                } else {
                    if ($form->has('company_country_for_invoicing')) {
                        $form->getField('company_country_for_invoicing')->setOption('colspan', 4);
                    }
                    if ($form->has('company_state_for_invoicing')) {
                        $form->getField('company_state_for_invoicing')->setOption('colspan', 4);
                    }
                    if ($form->has('company_city_for_invoicing')) {
                        $form->getField('company_city_for_invoicing')->setOption('colspan', 4);
                    }
                }

                // Row: Zipcode (3) + Email (3) + Phone (3) + Tax ID (3) = 12 (One Single Row)
                if ($form->has('company_zipcode_for_invoicing')) {
                    $form->getField('company_zipcode_for_invoicing')->setOption('colspan', 3);
                }

                if ($form->has('company_email_for_invoicing')) {
                    $form->getField('company_email_for_invoicing')->setOption('colspan', 3);
                }

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
                            ->colspan(3)
                    );
                }

                if ($form->has('company_tax_id_for_invoicing')) {
                    $form->getField('company_tax_id_for_invoicing')->setOption('colspan', 3);
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

        // Tracking authentication stays with Ecommerce's grouped order-code and
        // configured email/phone predicates. Never append a top-level phone OR.
    }
}
