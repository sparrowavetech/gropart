<?php

namespace Botble\Payment\Forms\Settings;

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Http\Requests\Settings\PaymentMethodSettingRequest;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Setting\Forms\SettingForm;

class PaymentMethodSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->contentOnly()
            ->setSectionTitle(trans('plugins/payment::payment.payment_methods'))
            ->setSectionDescription(trans('plugins/payment::payment.payment_methods_description'))
            ->setValidatorClass(PaymentMethodSettingRequest::class)
            ->setUrl(route('payments.settings'))
            ->add(
                'default_payment_method',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/payment::payment.default_payment_method'))
                    ->choices(PaymentMethodEnum::labels())
                    ->selected(PaymentHelper::defaultPaymentMethod())
                    ->toArray(),
            );
    }
}
