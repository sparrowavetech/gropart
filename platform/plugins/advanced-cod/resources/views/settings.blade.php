<div class="next-setting-box" style="margin-top: 15px;">
    <div class="form-group mb-3">
        <label for="payment_cod_prepayment_percentage" class="control-label">{{ trans('plugins/advanced-cod::advanced-cod.prepayment_percentage') }}</label>
        <input type="number" 
               name="payment_cod_prepayment_percentage" 
               id="payment_cod_prepayment_percentage" 
               class="form-control" 
               value="{{ $percentage }}" 
               min="1" 
               max="99" 
               placeholder="30">
        <p class="help-block">{{ trans('plugins/advanced-cod::advanced-cod.prepayment_percentage_help') }}</p>
    </div>

    <div class="form-group mb-3">
        <label for="payment_cod_advance_gateway" class="control-label">{{ trans('plugins/advanced-cod::advanced-cod.advance_gateway') }}</label>
        <select class="form-select" name="payment_cod_advance_gateway" id="payment_cod_advance_gateway">
            <option value="auto" @selected(($selectedGateway ?? 'auto') === 'auto')>
                {{ trans('plugins/advanced-cod::advanced-cod.advance_gateway_auto') }}
            </option>
            @foreach(($onlineGateways ?? []) as $gatewayKey => $gatewayName)
                <option value="{{ $gatewayKey }}" @selected(($selectedGateway ?? '') === $gatewayKey)>
                    {{ $gatewayName }} ({{ $gatewayKey }})
                </option>
            @endforeach
        </select>
        <p class="help-block">{{ trans('plugins/advanced-cod::advanced-cod.advance_gateway_help') }}</p>
    </div>
</div>

<div class="form-group mb-3">
    <label for="payment_cod_admin_full_cod_override" class="control-label">{{ trans('plugins/advanced-cod::advanced-cod.admin_override') }}</label>
    <select class="form-select" name="payment_cod_admin_full_cod_override" id="payment_cod_admin_full_cod_override">
        <option value="0">Disabled</option>
        <option value="1" @selected(get_payment_setting('admin_full_cod_override', 'cod', 0))>
            Enabled — {{ trans('plugins/advanced-cod::advanced-cod.admin_override_details') }}
        </option>
    </select>
    <p class="help-block">{{ trans('plugins/advanced-cod::advanced-cod.admin_override_help') }}</p>
</div>
