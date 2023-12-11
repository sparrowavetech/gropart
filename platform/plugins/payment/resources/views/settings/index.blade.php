@php
    use Botble\Payment\Enums\PaymentMethodEnum;
    use Botble\Payment\Models\Payment;
    use Botble\Payment\Supports\PaymentHelper;
@endphp

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core-setting::section
        :title="trans('plugins/payment::payment.payment_methods')"
        :description="trans('plugins/payment::payment.payment_methods_description')"
        :card="false"
    >
        @php
            do_action(BASE_ACTION_META_BOXES, 'top', new Payment);
        @endphp

        <x-core::card class="mb-3">
            <x-core::card.body>
                <x-core::form :url="route('payments.settings')">
                    <x-core-setting::select
                        name="default_payment_method"
                        :label="trans('plugins/payment::payment.default_payment_method')"
                        :options="PaymentMethodEnum::labels()"
                        :value="PaymentHelper::defaultPaymentMethod()"
                    />
                    <x-core::button type="button" color="primary" class="button-save-payment-settings">
                        {{ trans('core/base::forms.save') }}
                    </x-core::button>
                </x-core::form>
            </x-core::card.body>
        </x-core::card>

        {!! apply_filters(PAYMENT_METHODS_SETTINGS_PAGE, null) !!}

        <x-core::card class="mb-3">
            <x-core::table :hover="false" :striped="false">
                @php
                    $codStatus = get_payment_setting('status');
                @endphp
                <x-core::table.body>
                    <x-core::table.body.row>
                        <x-core::table.body.cell class="border-end">
                            <x-core::icon name="ti ti-wallet" />
                        </x-core::table.body.cell>
                        <x-core::table.body.cell style="width: 20%">
                            {{ trans('plugins/payment::payment.payment_methods') }}
                        </x-core::table.body.cell>
                        <x-core::table.body.cell>
                            <p class="mb-0">{{ trans('plugins/payment::payment.payment_methods_instruction') }}</p>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                    <x-core::table.body.row>
                        <x-core::table.body.cell colspan="3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="payment-name-label-group">
                                        @if($codStatus)
                                            {{ trans('plugins/payment::payment.use') }}
                                        @endif
                                        <span class="method-name-label">{{ get_payment_setting('name', PaymentMethodEnum::COD()->label()) }}</span>
                                    </div>
                                </div>

                                <x-core::button @class(['toggle-payment-item edit-payment-item-btn-trigger', 'hidden' => !$codStatus])>
                                    {{ trans('plugins/payment::payment.edit') }}
                                </x-core::button>
                                <x-core::button @class(['toggle-payment-item save-payment-item-btn-trigger', 'hidden' => $codStatus])>
                                    {{ trans('plugins/payment::payment.settings') }}
                                </x-core::button>
                            </div>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                    <x-core::table.body.row class="payment-content-item hidden">
                        <x-core::table.body.cell colspan="3">
                            <x-core::form>
                                <input type="hidden" name="type" value="cod" class="payment_type" />

                                <x-core::form.text-input
                                    :label="trans('plugins/payment::payment.method_name')"
                                    :name="sprintf('payment_%s_name', PaymentMethodEnum::COD)"
                                    data-counter="400"
                                    :value="setting(
                                        'payment_cod_name',
                                        PaymentMethodEnum::COD()->label(),
                                    )"
                                />

                                <x-core-setting::form-group style="max-width: 99.8%">
                                    <x-core::form.label for="payment_cod_description">
                                        {{ trans('plugins/payment::payment.payment_method_description') }}
                                    </x-core::form.label>
                                    {!! Form::editor('payment_cod_description', get_payment_setting('description')) !!}
                                </x-core-setting::form-group>

                                {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'cod') !!}

                                <div class="btn-list justify-content-end">
                                    <x-core::button
                                        type="button"
                                        @class(['disable-payment-item', 'hidden' => !$codStatus])
                                    >
                                        {{ trans('plugins/payment::payment.deactivate') }}
                                    </x-core::button>

                                    <x-core::button
                                        @class(['save-payment-item btn-text-trigger-save', 'hidden' => $codStatus])
                                        type="button"
                                        color="info"
                                    >
                                        {{ trans('plugins/payment::payment.activate') }}
                                    </x-core::button>
                                    <x-core::button
                                        type="button"
                                        color="info"
                                        @class(['save-payment-item btn-text-trigger-update', 'hidden' => !$codStatus])
                                    >
                                        {{ trans('plugins/payment::payment.update') }}
                                    </x-core::button>
                                </div>
                            </x-core::form>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                </x-core::table.body>

                @php
                    $bankTransferStatus = setting('payment_bank_transfer_status');
                @endphp
                <x-core::table.body>
                    <x-core::table.body.row>
                        <x-core::table.body.cell colspan="3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="payment-name-label-group">
                                        @if($bankTransferStatus)
                                            {{ trans('plugins/payment::payment.use') }}
                                        @endif
                                        <span class="method-name-label">{{ get_payment_setting('name', 'bank_transfer', PaymentMethodEnum::BANK_TRANSFER()->label()) }}</span>
                                    </div>
                                </div>

                                <x-core::button @class(['toggle-payment-item edit-payment-item-btn-trigger', 'hidden' => !$bankTransferStatus])>
                                    {{ trans('plugins/payment::payment.edit') }}
                                </x-core::button>
                                <x-core::button @class(['toggle-payment-item save-payment-item-btn-trigger', 'hidden' => $bankTransferStatus])>
                                    {{ trans('plugins/payment::payment.settings') }}
                                </x-core::button>
                            </div>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                    <x-core::table.body.row class="payment-content-item hidden">
                        <x-core::table.body.cell colspan="3">
                            <x-core::form>
                                <input type="hidden" name="type" value="{{ PaymentMethodEnum::BANK_TRANSFER }}" class="payment_type" />

                                <x-core::form.text-input
                                    :label="trans('plugins/payment::payment.method_name')"
                                    :name="sprintf('payment_%s_name', PaymentMethodEnum::BANK_TRANSFER)"
                                    data-counter="400"
                                    :value="setting(
                                        'payment_bank_transfer_name',
                                        PaymentMethodEnum::BANK_TRANSFER()->label(),
                                    )"
                                />

                                <x-core-setting::form-group style="max-width: 99.8%">
                                    <x-core::form.label for="payment_bank_transfer_description">
                                        {{ trans('plugins/payment::payment.payment_method_description') }}
                                    </x-core::form.label>
                                    {!! Form::editor('payment_bank_transfer_description', setting('payment_bank_transfer_description')) !!}
                                </x-core-setting::form-group>

                                {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, PaymentMethodEnum::BANK_TRANSFER) !!}

                                <div class="btn-list justify-content-end">
                                    <x-core::button
                                        type="button"
                                        @class(['disable-payment-item', 'hidden' => !$bankTransferStatus])
                                    >
                                        {{ trans('plugins/payment::payment.deactivate') }}
                                    </x-core::button>

                                    <x-core::button
                                        @class(['save-payment-item btn-text-trigger-save', 'hidden' => $bankTransferStatus])
                                        type="button"
                                        color="info"
                                    >
                                        {{ trans('plugins/payment::payment.activate') }}
                                    </x-core::button>
                                    <x-core::button
                                        type="button"
                                        color="info"
                                        @class(['save-payment-item btn-text-trigger-update', 'hidden' => !$bankTransferStatus])
                                    >
                                        {{ trans('plugins/payment::payment.update') }}
                                    </x-core::button>
                                </div>
                            </x-core::form>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                </x-core::table.body>
            </x-core::table>
        </x-core::card>
    </x-core-setting::section>

    @php
        do_action(BASE_ACTION_META_BOXES, 'main', new Payment);
    @endphp

    <div class="row">
        <div class="col-md-9 offset-col-md-3">
            @php
                do_action(BASE_ACTION_META_BOXES, 'advanced', new Payment);
            @endphp
        </div>
    </div>

    {!! apply_filters('payment_method_after_settings', null) !!}
@endsection

@push('footer')
    <x-core::modal.action
        id="confirm-disable-payment-method-modal"
        :title="trans('plugins/payment::payment.deactivate_payment_method')"
        :description="trans('plugins/payment::payment.deactivate_payment_method_description')"
        :submit-button-attrs="['id' => 'confirm-disable-payment-method-button']"
        :submit-button-label="trans('plugins/payment::payment.agree')"
    />
@endpush
