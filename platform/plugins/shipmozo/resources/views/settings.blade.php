@php
$status = setting('shipping_shipmozo_status', 0);
$publicKey = setting('shipping_shipmozo_public_key') ?: '';
$privateKey = setting('shipping_shipmozo_private_key') ?: '';
$logging = setting('shipping_shipmozo_logging', 0);
$rateAdjustmentType = setting('shipping_shipmozo_rate_adjustment_type', 'none');
$rateAdjustmentValue = setting('shipping_shipmozo_rate_adjustment_value', 0);
@endphp

<x-core::card>
    <x-core::table :striped="false" :hover="false">
        <x-core::table.body>
            <x-core::table.body.cell class="border-end" style="width: 5%">
                <x-core::icon name="ti ti-truck-delivery" />
            </x-core::table.body.cell>
            <x-core::table.body.cell style="width: 20%">
                <h4 class="mb-0 text-primary fw-bold" style="font-size: 20px;">ShipMozo</h4>
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                <a href="https://shipping-api.com" target="_blank" class="fw-semibold">Shipmozo Shipping Service</a>
                <p class="mb-0">Calculate accurate shipping rates and sync orders using ShipMozo API.</p>
            </x-core::table.body.cell>
            <x-core::table.body.row class="bg-white">
                <x-core::table.body.cell colspan="3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div @class(['payment-name-label-group', 'd-none'=> ! $status])>
                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                <label class="ws-nm inline-display method-name-label">Shipmozo</label>
                            </div>
                        </div>

                        <x-core::button
                            data-bs-toggle="collapse"
                            href="#collapse-shipping-method-shipmozo"
                            aria-expanded="false"
                            aria-controls="collapse-shipping-method-shipmozo">
                            @if ($status)
                            {{ trans('core/base::layouts.settings') }}
                            @else
                            {{ trans('core/base::forms.edit') }}
                            @endif
                        </x-core::button>
                    </div>
                </x-core::table.body.cell>
            </x-core::table.body.row>
            <x-core::table.body.row class="collapse" id="collapse-shipping-method-shipmozo">
                <x-core::table.body.cell class="border-left" colspan="3">
                    <x-core::form :url="route('ecommerce.shipments.shipmozo.settings.update')">
                        <div class="row">
                            <div class="col-sm-6">
                                <x-core::alert type="info">
                                    <x-slot:title>Configuration Instructions</x-slot:title>
                                    <ul class="ps-3">
                                        <li style="list-style-type: circle;">
                                            <span>Obtain your <strong>Public Key</strong> and <strong>Private Key</strong> from ShipMozo API account.</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>Enter the keys in the fields on the right.</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>Ensure products have length, width, height, and weight values set for accurate rate calculation.</span>
                                        </li>
                                    </ul>
                                </x-core::alert>

                                <h5 class="mt-4">Rate Inflation Settings</h5>
                                <p class="text-muted text-sm">You can artificially inflate the shipping rates returned by ShipMozo before showing them to customers.</p>

                                <x-core::form-group>
                                    <label class="form-label" for="shipping_shipmozo_rate_adjustment_type">Inflation Type</label>
                                    <x-core::form.select
                                        name="shipping_shipmozo_rate_adjustment_type"
                                        id="shipping_shipmozo_rate_adjustment_type"
                                        :options="['none' => 'None', 'fixed' => 'Fixed Amount', 'percent' => 'Percentage']"
                                        :value="$rateAdjustmentType" />
                                </x-core::form-group>

                                <x-core::form-group>
                                    <label class="form-label" for="shipping_shipmozo_rate_adjustment_value">Inflation Value</label>
                                    <x-core::form.text-input
                                        type="number"
                                        step="0.01"
                                        name="shipping_shipmozo_rate_adjustment_value"
                                        id="shipping_shipmozo_rate_adjustment_value"
                                        :value="$rateAdjustmentValue"
                                        placeholder="e.g. 50 (for Rs 50) or 5 (for 5%)" />
                                </x-core::form-group>

                            </div>
                            <div class="col-sm-6">
                                <h5 class="mt-2">API Credentials</h5>
                                <p class="text-muted">Enter your ShipMozo API keys:</p>

                                <x-core::form.text-input
                                    name="shipping_shipmozo_public_key"
                                    label="Public Key"
                                    placeholder="Enter Public Key"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($publicKey, '*', 10) : $publicKey" />

                                <x-core::form.text-input
                                    name="shipping_shipmozo_private_key"
                                    label="Private Key"
                                    placeholder="Enter Private Key"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($privateKey, '*', 10) : $privateKey" />

                                <x-core::form-group class="mt-4">
                                    <x-core::form.toggle
                                        name="shipping_shipmozo_status"
                                        :checked="$status"
                                        label="Activate ShipMozo Shipping plugin" />
                                </x-core::form-group>

                                <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_shipmozo_logging"
                                        :checked="$logging"
                                        label="Enable Logging (Saves API debug logs to system)" />
                                </x-core::form-group>

                                @env('demo')
                                <x-core::alert type="danger">
                                    {{ trans('plugins/shipmozo::shipmozo.disabled_in_demo_mode') }}
                                </x-core::alert>
                                @else
                                <div class="text-end">
                                    <x-core::button type="submit" color="primary">
                                        {{ trans('core/base::forms.update') }}
                                    </x-core::button>
                                </div>
                                @endenv
                            </div>
                        </div>
                    </x-core::form>
                </x-core::table.body.cell>
            </x-core::table.body.row>
        </x-core::table.body>
    </x-core::table>
</x-core::card>
