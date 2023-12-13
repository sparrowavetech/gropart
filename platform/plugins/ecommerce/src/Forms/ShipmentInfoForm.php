<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Http\Requests\ShipmentRequest;
use Botble\Ecommerce\Models\Shipment;
use Illuminate\Support\Facades\Blade;

class ShipmentInfoForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->setupModel(new Shipment())
            ->setValidatorClass(ShipmentRequest::class)
            ->contentOnly()
            ->add('shipping_company_name', 'text', [
                'label' => trans('plugins/ecommerce::shipping.shipping_company_name'),
                'attr' => [
                    'placeholder' => 'Ex: DHL, AliExpress...',
                ],
            ])
            ->add('tracking_id', 'text', [
                'label' => trans('plugins/ecommerce::shipping.tracking_id'),
                'attr' => [
                    'placeholder' => 'Ex: JJD0099999999',
                ],
            ])
            ->add('tracking_link', 'text', [
                'label' => trans('plugins/ecommerce::shipping.tracking_link'),
                'attr' => [
                    'placeholder' => 'Ex: https://mydhl.express.dhl/us/en/tracking.html#/track-by-reference',
                ],
            ])
            ->add('estimate_date_shipped', 'datePicker', [
                'label' => trans('plugins/ecommerce::shipping.estimate_date_shipped'),
            ])
            ->add('note', 'textarea', [
                'label' => trans('plugins/ecommerce::shipping.note'),
                'attr' => [
                    'rows' => 3,
                    'placeholder' => trans('plugins/ecommerce::shipping.add_note'),
                ],
            ])
            ->add('submit', 'html', [
                'html' => Blade::render(sprintf(
                    '<x-core::button type="submit" name="submit" value="apply" color="primary" icon="%s">%s</x-core::button>',
                    'ti ti-circle-check',
                    trans('core/base::forms.save_and_continue')
                )),
            ]);
    }
}
