<x-plugins-payment::settings-card
    name="PayPal"
    :id="PAYPAL_PAYMENT_METHOD_NAME"
    :logo="url('vendor/core/plugins/paypal/images/paypal.svg')"
    url="https://paypal.com"
    :description="trans('plugins/payment::payment.paypal_description')"
    :default-description-value="__('You will be redirected to :name to complete the payment.', ['name' => 'PayPal'])"
>
    <x-slot:instructions>
        <ol>
            <li>
                <p>
                    <a
                        href="https://www.paypal.com/vn/merchantsignup/applicationChecklist?signupType=CREATE_NEW_ACCOUNT&amp;productIntentId=email_payments"
                        target="_blank"
                    >
                        {{ trans('plugins/payment::payment.service_registration', ['name' => 'PayPal']) }}
                    </a>
                </p>
            </li>
            <li>
                <p>
                    {{ trans('plugins/payment::payment.after_service_registration_msg', ['name' => 'PayPal']) }}
                </p>
            </li>
            <li>
                <p>
                    {{ trans('plugins/payment::payment.enter_client_id_and_secret') }}
                </p>
            </li>
        </ol>
    </x-slot:instructions>

    <x-slot:fields>
        <x-core::form.text-input
            :name="sprintf('payment_%s_client_id', PAYPAL_PAYMENT_METHOD_NAME)"
            :label="trans('plugins/payment::payment.client_id')"
            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('client_id', 'paypal')"
        />

        <x-core::form.text-input
            type="password"
            :name="sprintf('payment_%s_client_secret', PAYPAL_PAYMENT_METHOD_NAME)"
            :label="trans('plugins/payment::payment.client_secret')"
            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('client_secret', 'paypal')"
        />

        <x-core::form.on-off.checkbox
            :name="sprintf('payment_%s_mode', PAYPAL_PAYMENT_METHOD_NAME)"
            :label="trans('plugins/payment::payment.sandbox_mode')"
            :checked="get_payment_setting('mode', PAYPAL_PAYMENT_METHOD_NAME, true)"
        />
    </x-slot:fields>
</x-plugins-payment::settings-card>
