<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Models\BaseModel;
use Botble\Marketplace\Http\Requests\TaxInformationSettingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;

class TaxInformationForm extends FormAbstract
{
    public function setup(): void
    {
        $customer = $this->getModel();

        $this
            ->setupModel(new BaseModel())
            ->setValidatorClass(TaxInformationSettingRequest::class)
            ->contentOnly()
            ->add('tax_info[business_name]', 'text', [
                'label' => __('Business Name'),
                'value' => Arr::get($customer->tax_info, 'business_name'),
                'attr' => [
                    'placeholder' => __('Business Name'),
                ],
            ])
            ->add('tax_info[tax_id]', 'text', [
                'label' => __('Tax ID'),
                'value' => Arr::get($customer->tax_info, 'tax_id'),
                'attr' => [
                    'placeholder' => __('Tax ID'),
                ],
            ])
            ->add('tax_info[address]', 'text', [
                'label' => __('Address'),
                'value' => Arr::get($customer->tax_info, 'address'),
                'attr' =>
                    ['placeholder' => __('Address'),
                ],
            ])
            ->add('submit', 'html', [
                'html' => Blade::render(sprintf(
                    '<x-core::button type="submit" color="primary">%s</x-core::button>',
                    __('Save settings')
                )),
            ]);
    }
}
