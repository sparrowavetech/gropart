<x-plugins-payment::settings-card
    name="SslCommerz"
    :id="SSLCOMMERZ_PAYMENT_METHOD_NAME"
    :logo="url('vendor/core/plugins/sslcommerz/images/sslcommerz.png')"
    url="https://sslcommerz.com"
    :description="__('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'SslCommerz'])"
>
    <x-slot:instructions>
        <ol>
            <li>
                <p>For registration in Sandbox, click the link
                    <a href="https://developer.sslcommerz.com/registration/" target="_blank">
                        https://developer.sslcommerz.com/registration
                    </a>
                </p>
                <p>For registration in Production, click the link
                    <a href="https://signup.sslcommerz.com/register" target="_blank">
                        https://signup.sslcommerz.com/register
                    </a>
                </p>
            </li>
            <li>
                <p>{{ __('After registration at :name, you will have Store ID and Store Password (API/Secret key)', ['name' => 'SslCommerz']) }}</p>
            </li>
            <li>
                <p>{{ __('Enter Store ID and Store Password (API/Secret key) into the box in right hand') }}</p>
            </li>
        </ol>
    </x-slot:instructions>

    <x-slot:fields>
        <x-core::form.text-input
            data-counter="400"
            :name="sprintf('payment_%s_store_id', SSLCOMMERZ_PAYMENT_METHOD_NAME)"
            :label="__('Store ID')"
            :value="get_payment_setting('store_id', SSLCOMMERZ_PAYMENT_METHOD_NAME)"
        />

        <x-core::form.text-input
            type="password"
            :name="sprintf('payment_%s_store_password', SSLCOMMERZ_PAYMENT_METHOD_NAME)"
            :label="__('Store Password (API/Secret key)')"
            :value="get_payment_setting('store_password', SSLCOMMERZ_PAYMENT_METHOD_NAME)"
        />

        <x-core::form.on-off.checkbox
            :name="sprintf('payment_%s_mode', SSLCOMMERZ_PAYMENT_METHOD_NAME)"
            :label="trans('plugins/payment::payment.sandbox_mode')"
            :checked="get_payment_setting('mode', SSLCOMMERZ_PAYMENT_METHOD_NAME, true)"
        />
    </x-slot:fields>
</x-plugins-payment::settings-card>
