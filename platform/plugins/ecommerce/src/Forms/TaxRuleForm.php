<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Concerns\HasSubmitButton;
use Botble\Ecommerce\Http\Requests\TaxRuleRequest;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Models\TaxRule;
use Illuminate\Support\Facades\Request;

class TaxRuleForm extends FormAbstract
{
    use HasSubmitButton;

    public function __construct()
    {
        parent::__construct();
        $this->setFormOption('id', 'ecommerce-tax-rule-form');

        if (Request::ajax()) {
            $this->contentOnly();
        }
    }

    public function setup(): void
    {
        $this
            ->setupModel(new TaxRule())
            ->setValidatorClass(TaxRuleRequest::class);

        if (! $this->getModel()->getKey()) {
            $this
                ->when(
                    $taxId = request()->input('tax_id'),
                    fn (FormAbstract $form) => $form->add('tax_id', 'hidden', [
                        'value' => $taxId,
                    ])
                )
                ->when(
                    ! $taxId,
                    function (FormAbstract $form) {
                        $taxes = Tax::query()->pluck('title', 'id')->toArray();
                        $form
                            ->add('tax_id', 'customSelect', [
                                'label' => trans('plugins/ecommerce::tax.tax'),
                                'choices' => $taxes,
                            ]);
                    }
                );
        }

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $this
                ->add('location', 'selectLocation', [
                    'locationKeys' => [
                        'country' => 'country',
                        'state' => 'state',
                        'city' => 'city',
                    ],
                ]);
        } else {
            $this
                ->add('country', 'customSelect', [
                    'label' => trans('plugins/ecommerce::tax.state'),
                    'attr' => [
                        'data-type' => 'country',
                    ],
                    'choices' => EcommerceHelper::getAvailableCountries(),
                ])
                ->add('state', 'text', [
                    'label' => trans('plugins/ecommerce::tax.state'),
                    'attr' => [
                        'placeholder' => trans('plugins/ecommerce::tax.state'),
                    ],
                ])
                ->add('city', 'text', [
                    'label' => trans('plugins/ecommerce::tax.city'),
                    'attr' => [
                        'placeholder' => trans('plugins/ecommerce::tax.city'),
                    ],
                ]);
        }

        if (EcommerceHelper::isZipCodeEnabled()) {
            $this
                ->add('zip_code', 'text', [
                    'label' => trans('plugins/ecommerce::tax.zip_code'),
                ]);
        }

        $this->addSubmitButton(trans('core/base::forms.save'), 'ti ti-device-floppy', [
            'wrapper' => [
                'class' => 'd-grid gap-2',
            ],
        ]);
    }
}
