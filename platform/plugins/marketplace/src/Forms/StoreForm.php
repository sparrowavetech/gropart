<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\Blade;

class StoreForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/location/js/location.js',
            'vendor/core/plugins/marketplace/js/store.js',
        ]);

        $this
            ->setupModel(new Store())
            ->setValidatorClass(StoreRequest::class)
            ->columns(6)
            ->contentOnly()
            ->hasFiles()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 250,
                ],
                'colspan' => 3,
            ])
            ->add('slug', 'html', [
                'html' => view('plugins/marketplace::stores.partials.shop-url-field', ['store' => $this->getModel()])->render(),
                'colspan' => 3,
            ])
            ->add('email', 'email', [
                'label' => trans('plugins/marketplace::store.forms.email'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.email_placeholder'),
                    'data-counter' => 60,
                ],
                'colspan' => 3,
            ])
            ->add('phone', 'text', [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                ],
                'colspan' => 3,
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
                'colspan' => 6,
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'with-short-code' => false,
                ],
                'colspan' => 6,
            ])
            ->when(EcommerceHelper::isUsingInMultipleCountries(), function (FormAbstract $form) {
                $form->add('country', 'customSelect', [
                    'label' => trans('plugins/marketplace::store.forms.country'),
                    'attr' => [
                        'data-type' => 'country',
                    ],
                    'colspan' => 2,
                    'choices' => EcommerceHelper::getAvailableCountries(),
                    'selected' => old('country', $this->getModel()->country),
                ]);
            })
            ->when(EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation(), function (FormAbstract $form) {
                $form
                    ->add('state', 'customSelect', [
                        'label' => trans('plugins/location::city.state'),
                        'attr' => [
                            'class' => 'select-search-full',
                            'data-type' => 'state',
                            'data-url' => route('ajax.states-by-country'),
                        ],
                        'colspan' => 2,
                        'choices' => ['' => trans('plugins/location::city.select_state')] + EcommerceHelper::getAvailableStatesByCountry(old('country', $this->getModel()->country)),
                    ])
                    ->add('city', 'customSelect', [
                        'label' => trans('plugins/location::city.city'),
                        'attr' => [
                            'class' => 'select-search-full',
                            'data-type' => 'city',
                            'data-url' => route('ajax.cities-by-state'),
                        ],
                        'colspan' => 2,
                        'choices' => ['' => trans('plugins/location::city.select_city')] + EcommerceHelper::getAvailableCitiesByState(old('state', $this->getModel()->state)),
                    ]);
            })
            ->when(! EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation(), function (FormAbstract $form) {
                $form
                    ->add('state', 'text', [
                        'label' => trans('plugins/ecommerce::shipping.rule.item.forms.state'),
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.state_placeholder'),
                        ],
                        'colspan' => 2,
                    ])
                    ->add('city', 'text', [
                        'label' => trans('plugins/ecommerce::shipping.rule.item.forms.city'),
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.city_placeholder'),
                        ],
                        'colspan' => 2,
                    ]);
            })
            ->when(EcommerceHelper::isZipCodeEnabled(), function (FormAbstract $form) {
                $form->add('zip_code', 'text', [
                    'label' => trans('plugins/marketplace::store.forms.zip_code'),
                    'attr' => [
                        'placeholder' => trans('plugins/marketplace::store.forms.zip_code_placeholder'),
                        'data-counter' => 120,
                    ],
                    'colspan' => 3,
                ]);
            })
            ->add('address', 'text', [
                'label' => trans('plugins/marketplace::store.forms.address'),
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.address_placeholder'),
                    'data-counter' => 120,
                ],
                'colspan' => 3,
            ])
            ->add('company', 'text', [
                'label' => trans('plugins/marketplace::store.forms.company'),
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.company_placeholder'),
                    'data-counter' => 255,
                ],
                'colspan' => 3,
            ])
            ->add('logo', 'mediaImage', [
                'label' => trans('plugins/marketplace::store.forms.logo'),
                'colspan' => 3,
            ])
            ->add('cover_image', MediaImageField::class, [
                'label' => __('Cover Image'),
                'value' => old('cover_image', MetaBox::getMetaData($this->getModel(), 'cover_image', true)),
                'colspan' => 3,
                'metadata' => true,
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices' => BaseStatusEnum::labels(),
                'help_block' => [
                    'text' => trans('plugins/marketplace::marketplace.helpers.store_status', [
                        'customer' => CustomerStatusEnum::LOCKED()->label(),
                        'status' => BaseStatusEnum::PUBLISHED()->label(),
                    ]),
                ],
                'colspan' => 3,
            ])
            ->add('customer_id', 'customSelect', [
                'label' => trans('plugins/marketplace::store.forms.store_owner'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices' => [0 => trans('plugins/marketplace::store.forms.select_store_owner')] + Customer::query()
                    ->where('is_vendor', true)
                    ->pluck('name', 'id')
                    ->all(),
                'colspan' => 3,
            ])
            ->add('submit', 'html', [
                'html' => Blade::render(sprintf(
                    '<x-core::button type="submit" color="primary">%s</x-core::button>',
                    trans('core/base::forms.save_and_continue')
                )),
                'colspan' => 6,
            ]);
    }
}
