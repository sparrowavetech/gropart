<table class="table mt-4 bg-white">
    <tbody>
        <tr class="border-pay-row">
            <td class="border-pay-col">
                <i class="fas fa-shipping-fast"></i>
            </td>
            <td style="width: 20%;">
                <img class="filter-black" src="{{ url('vendor/core/plugins/pickrr/images/logo.svg') }}" alt="Pickrr Logo">
            </td>
            <td class="border-right">
                <ul>
                    <li>
                        <a href="https://www.pickrr.com/" target="_blank">Pickrr Shipping API Configuration</a>
                        <p>{{ trans('plugins/pickrr::pickrr.description') }}</p>
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="bg-white">
            <td colspan="3">
                <div class="float-end">
                    <a class="btn btn-secondary" data-bs-toggle="collapse" href="#collapse-shipping-method-shippo" role="button" aria-expanded="false" aria-controls="collapse-shipping-method-shippo">
                    {{ trans('core/base::forms.edit') }}
                    </a>
                </div>
            </td>
        </tr>
        <tr class="collapse" id="collapse-shipping-method-shippo">
            <td class="border-left" colspan="3">
                {!! Form::open(['route' => 'ecommerce.shipments.pickrr.settings.update']) !!}
                <div class="flexbox-annotated-section">
                    <div class="flexbox-annotated-section-content">
                        <div class="wrapper-content pd-all-20">
                            <div class="form-group mb-3">
                                <label class="text-title-field" for="shipping_pickrr_auth_token">{{ trans('plugins/pickrr::pickrr.settings.auth_token') }}</label>
                                <input data-counter="500" type="text" class="next-input" name="shipping_pickrr_auth_token" id="shipping_pickrr_auth_token" value="{{ setting('shipping_pickrr_auth_token') }}" placeholder="{{ trans('plugins/pickrr::pickrr.settings.auth_token') }}">
                            </div>
                        </div>
                    </div>

                    <div class="flexbox-annotated-section-content">
                        <div class="wrapper-content pd-all-20">
                            <div class="form-group mb-3">
                                <label class="text-title-field" for="shipping_pickrr_status">{{ trans('plugins/pickrr::pickrr.enabled') }}
                                </label>
                                <label class="me-2">
                                    <input type="radio" name="shipping_pickrr_status" value="1" @if (setting('shipping_pickrr_status')) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                                </label>
                                <label>
                                    <input type="radio" name="shipping_pickrr_status" value="0" @if (!setting('shipping_pickrr_status')) checked @endif>{{ trans('core/setting::setting.general.no') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flexbox-annotated-section" style="border: none">
                    <div class="flexbox-annotated-section-annotation">
                        &nbsp;
                    </div>
                    <div class="flexbox-annotated-section-content">
                        <button class="btn btn-info" type="submit">{{ trans('plugins/pickrr::pickrr.save_settings') }}</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </td>
        </tr>
    </tbody>
</table>
