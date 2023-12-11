<x-plugins-payment::settings-card
    name="Stripe"
    :id="STRIPE_PAYMENT_METHOD_NAME"
    :logo="url('vendor/core/plugins/stripe/images/stripe.svg')"
    url="https://stripe.com"
    :description="trans('plugins/payment::payment.stripe_description')"
>
    <x-slot:instructions>
        <ol>
            <li>
                <p>
                    <a href="https://dashboard.stripe.com/register" target="_blank">
                        {{ trans('plugins/payment::payment.service_registration', ['name' => 'Stripe']) }}
                    </a>
                </p>
            </li>
            <li>
                <p>{{ trans('plugins/payment::payment.stripe_after_service_registration_msg', ['name' => 'Stripe']) }}</p>
            </li>
            <li>
                <p>{{ trans('plugins/payment::payment.stripe_enter_client_id_and_secret') }}</p>
            </li>
        </ol>
    </x-slot:instructions>

    <x-slot:fields>
        <x-core::form.text-input
            name="payment_stripe_client_id"
            data-counter="400"
            :label="trans('plugins/payment::payment.stripe_key')"
            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('client_id', 'stripe')"
            placeholder="pk_*************"
        />

        <x-core::form.text-input
            name="payment_stripe_secret"
            type="password"
            :label="trans('plugins/payment::payment.stripe_secret')"
            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('secret', 'stripe')"
            placeholder="sk_*************"
        />

        <x-core::form.select
            :name="'payment_' . STRIPE_PAYMENT_METHOD_NAME . '_payment_type'"
            :label="__('Payment Type')"
            :options="[
                'stripe_api_charge' => 'Stripe API Charge',
                'stripe_checkout' => 'Stripe Checkout',
            ]"
            :value="get_payment_setting(
                'payment_type',
                STRIPE_PAYMENT_METHOD_NAME,
                'stripe_api_charge',
            )"
        />
    </x-slot:fields>
</x-plugins-payment::settings-card>
