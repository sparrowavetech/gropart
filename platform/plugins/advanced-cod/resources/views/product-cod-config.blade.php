<div class="form-group">
    <label for="is_cod_eligible" class="control-label">Eligible for Cash on Delivery (COD)?</label>
    <div class="onoffswitch">
        <input type="hidden" name="is_cod_eligible" value="0">
        <input type="checkbox" name="is_cod_eligible" class="onoffswitch-checkbox" id="is_cod_eligible" value="1" {{ $model->is_cod_eligible ? 'checked' : '' }}>
        <label class="onoffswitch-label" for="is_cod_eligible">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
    <p class="help-block">If enabled, this product will be available for Cash on Delivery. If the cart contains any product where this is disabled, COD will be hidden at checkout.</p>
</div>
