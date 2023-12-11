<x-plugins-payment::settings-card
    name="Paystack"
    :id="PAYSTACK_PAYMENT_METHOD_NAME"
    :logo="url('vendor/core/plugins/paystack/images/paystack.png')"
    url="https://paystack.com"
    :description="__('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'Paystack'])"
>
    <x-slot:instructions>
        <ol>
            <li>
                <p>
                    <a
                        href="https://paystack.com"
                        target="_blank"
                    >
                        {{ __('Register an account on :name', ['name' => 'Paystack']) }}
                    </a>
                </p>
            </li>
            <li>
                <p>
                    {{ __('After registration at :name, you will have Public & Secret keys', ['name' => 'Paystack']) }}
                </p>
            </li>
            <li>
                <p>
                    {{ __('Enter Public, Secret into the box in right hand') }}
                </p>
            </li>
        </ol>
    </x-slot:instructions>

    <x-slot:fields>
        <x-core::form.text-input
            :name="sprintf('payment_%s_public', PAYSTACK_PAYMENT_METHOD_NAME)"
            :label="__('Public Key')"
            :value="get_payment_setting('public', PAYSTACK_PAYMENT_METHOD_NAME)"
            placeholder="pk_****"
        />

        <x-core::form.text-input
            type="password"
            :name="sprintf('payment_%s_secret', PAYSTACK_PAYMENT_METHOD_NAME)"
            :label="__('Secret Key')"
            :value="get_payment_setting('secret', PAYSTACK_PAYMENT_METHOD_NAME)"
            placeholder="sk_****"
        />
    </x-slot:fields>
</x-plugins-payment::settings-card>
