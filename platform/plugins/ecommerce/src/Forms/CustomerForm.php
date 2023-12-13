<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Http\Requests\CustomerCreateRequest;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;

class CustomerForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/address.js')
            ->addScriptsDirectly('vendor/core/plugins/location/js/location.js')
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/customer-admin.css')
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        $this
            ->setupModel(new Customer())
            ->setValidatorClass(CustomerCreateRequest::class)
            ->template('plugins/ecommerce::customers.form')
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'text', [
                'label' => trans('plugins/ecommerce::customer.email'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::customer.email_placeholder'),
                    'data-counter' => 60,
                ],
            ])
            ->add('phone', 'text', [
                'label' => trans('plugins/ecommerce::customer.phone'),
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::customer.phone_placeholder'),
                    'data-counter' => 20,
                ],
            ])
            ->add('dob', 'datePicker', [
                'label' => trans('plugins/ecommerce::customer.dob'),
                'default_value' => BaseHelper::formatDate(Carbon::now()),
            ])
            ->add('is_change_password', 'onOff', [
                'label' => trans('plugins/ecommerce::customer.change_password'),
                'value' => 0,
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '#password-collapse',
                ],
            ])
            ->add('openRow1', 'html', [
                'html' => '<div class="row" id="password-collapse" data-bb-value="1"' . ($this->getModel()->id ? ' style="display: none"' : '') . '>',
            ])
            ->add('password', 'password', [
                'label' => trans('plugins/ecommerce::customer.password'),
                'required' => true,
                'attr' => [
                    'data-counter' => 60,
                ],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
            ])
            ->add('password_confirmation', 'password', [
                'label' => trans('plugins/ecommerce::customer.password_confirmation'),
                'required' => true,
                'attr' => [
                    'data-counter' => 60,
                ],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
            ])
            ->add('closeRow1', 'html', [
                'html' => '</div>',
            ])
            ->add('status', SelectField::class, StatusFieldOption::make()->choices(CustomerStatusEnum::labels())->toArray())
            ->add('avatar', 'mediaImage')
            ->setBreakFieldPoint('status');

        if ($this->getModel()->id) {
            $this
                ->addMetaBoxes([
                    'addresses' => [
                        'title' => trans('plugins/ecommerce::addresses.addresses'),
                        'content' => view('plugins/ecommerce::customers.addresses.addresses', [
                            'addresses' => $this->model->addresses()->get(),
                        ])->render(),
                        'header_actions' => view('plugins/ecommerce::customers.addresses.address-actions')->render(),
                        'wrap' => true,
                        'has_table' => true,
                    ],
                ])
                ->addMetaBoxes([
                    'payments' => [
                        'title' => trans('plugins/ecommerce::payment.name'),
                        'content' => view('plugins/ecommerce::customers.payments.payments', [
                            'payments' => $this->model->payments()->get(),
                        ])->render(),
                        'wrap' => true,
                        'has_table' => true,
                    ],
                ]);
        }
    }
}
