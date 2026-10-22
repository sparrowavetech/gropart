@php
$status = setting('shipping_shipmozo_status', 0);
$publicKey = setting('shipping_shipmozo_public_key') ?: '';
$privateKey = setting('shipping_shipmozo_private_key') ?: '';
$logging = setting('shipping_shipmozo_logging', 0);
$webhooks = setting('shipping_shipmozo_webhooks', 0);
$webhookSecret = setting('shipping_shipmozo_webhook_secret') ?: '';
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
                <h4 class="mb-0 text-primary fw-bold" style="font-size: 20px;">{{ trans('plugins/shipmozo::shipmozo.settings_title') }}</h4>
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                <a href="https://shipping-api.com" target="_blank" class="fw-semibold">{{ trans('plugins/shipmozo::shipmozo.settings_service_name') }}</a>
                <p class="mb-0 text-muted">{{ trans('plugins/shipmozo::shipmozo.settings_service_desc') }}</p>
            </x-core::table.body.cell>
            <x-core::table.body.row class="bg-white">
                <x-core::table.body.cell colspan="3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div @class(['payment-name-label-group', 'd-none'=> ! $status])>
                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                <label class="ws-nm inline-display method-name-label">{{ trans('plugins/shipmozo::shipmozo.name') }}</label>
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
                                    <x-slot:title>{{ trans('plugins/shipmozo::shipmozo.configuration_instructions') }}</x-slot:title>
                                    <ul class="ps-3 mb-0">
                                        <li style="list-style-type: circle;">
                                            <span>{!! trans('plugins/shipmozo::shipmozo.instruction_1') !!}</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>{{ trans('plugins/shipmozo::shipmozo.instruction_2') }}</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>{{ trans('plugins/shipmozo::shipmozo.instruction_3') }}</span>
                                        </li>
                                    </ul>
                                </x-core::alert>

                                <h5 class="mt-4">{{ trans('plugins/shipmozo::shipmozo.rate_inflation_settings') }}</h5>
                                <p class="text-muted text-sm">{{ trans('plugins/shipmozo::shipmozo.rate_inflation_desc') }}</p>

                                <x-core::form-group>
                                    <label class="form-label" for="shipping_shipmozo_rate_adjustment_type">{{ trans('plugins/shipmozo::shipmozo.inflation_type') }}</label>
                                    <x-core::form.select
                                        name="shipping_shipmozo_rate_adjustment_type"
                                        id="shipping_shipmozo_rate_adjustment_type"
                                        :options="[
                                            'none' => trans('plugins/shipmozo::shipmozo.inflation_type_none'),
                                            'fixed' => trans('plugins/shipmozo::shipmozo.inflation_type_fixed'),
                                            'percent' => trans('plugins/shipmozo::shipmozo.inflation_type_percent')
                                        ]"
                                        :value="$rateAdjustmentType" />
                                </x-core::form-group>

                                <x-core::form-group>
                                    <label class="form-label" for="shipping_shipmozo_rate_adjustment_value">{{ trans('plugins/shipmozo::shipmozo.inflation_value') }}</label>
                                    <x-core::form.text-input
                                        type="number"
                                        step="0.01"
                                        name="shipping_shipmozo_rate_adjustment_value"
                                        id="shipping_shipmozo_rate_adjustment_value"
                                        :value="$rateAdjustmentValue"
                                        :placeholder="trans('plugins/shipmozo::shipmozo.inflation_value_placeholder')" />
                                </x-core::form-group>

                                @if (!empty($logFiles))
                                    <div class="mt-4">
                                        <h5>{{ trans('plugins/shipmozo::shipmozo.log_files') }}</h5>
                                        <ul class="list-unstyled">
                                            @foreach ($logFiles as $logFile)
                                                <li>
                                                    <a href="{{ route('ecommerce.shipments.shipmozo.view-log', $logFile) }}" target="_blank" class="text-primary text-decoration-none">
                                                        <x-core::icon name="ti ti-file-text" class="me-1" /> {{ $logFile }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                <h5 class="mt-2">{{ trans('plugins/shipmozo::shipmozo.api_credentials') }}</h5>
                                <p class="text-muted">{{ trans('plugins/shipmozo::shipmozo.api_credentials_desc') }}</p>

                                <x-core::form.text-input
                                    name="shipping_shipmozo_public_key"
                                    :label="trans('plugins/shipmozo::shipmozo.public_key')"
                                    :placeholder="trans('plugins/shipmozo::shipmozo.public_key_placeholder')"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($publicKey, '*', 10) : $publicKey" />

                                <x-core::form.text-input
                                    name="shipping_shipmozo_private_key"
                                    type="password"
                                    :label="trans('plugins/shipmozo::shipmozo.private_key')"
                                    :placeholder="trans('plugins/shipmozo::shipmozo.private_key_placeholder')"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($privateKey, '*', 10) : $privateKey" />

                                <x-core::form.text-input
                                    name="shipping_shipmozo_webhook_secret"
                                    type="password"
                                    :label="trans('plugins/shipmozo::shipmozo.webhook_secret')"
                                    :placeholder="trans('plugins/shipmozo::shipmozo.webhook_secret_placeholder')"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($webhookSecret, '*', 6) : $webhookSecret" />

                                <div class="mb-3">
                                    <label class="form-label text-muted small">{{ trans('plugins/shipmozo::shipmozo.webhook_url') }}</label>
                                    <input type="text" readonly class="form-control bg-light" value="{{ route('shipmozo.webhooks') }}">
                                    <small class="form-hint text-muted">{{ trans('plugins/shipmozo::shipmozo.webhook_url_hint') }}</small>
                                </div>

                                <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_shipmozo_webhooks"
                                        :checked="$webhooks"
                                        :label="trans('plugins/shipmozo::shipmozo.enable_webhooks')" />
                                </x-core::form-group>

                                <x-core::form-group class="mt-4">
                                    <x-core::form.toggle
                                        name="shipping_shipmozo_status"
                                        :checked="$status"
                                        :label="trans('plugins/shipmozo::shipmozo.activate')" />
                                </x-core::form-group>

                                <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_shipmozo_logging"
                                        :checked="$logging"
                                        :label="trans('plugins/shipmozo::shipmozo.enable_logging')" />
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
