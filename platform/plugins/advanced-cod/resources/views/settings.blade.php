<div class="next-setting-box" style="margin-top: 15px;">
    <div class="form-group mb-3">
        <label for="payment_cod_prepayment_percentage" class="control-label">Prepayment Percentage (%)</label>
        <input type="number" 
               name="payment_cod_prepayment_percentage" 
               id="payment_cod_prepayment_percentage" 
               class="form-control" 
               value="{{ $percentage }}" 
               min="1" 
               max="99" 
               placeholder="30">
        <p class="help-block">Enter the percentage of the total amount that customers must pay in advance via online gateways when choosing COD.</p>
    </div>
</div>
<div class="form-group mb-3">
    <label for="payment_cod_admin_full_cod_override" class="control-label">Allow authorized admins to place full COD without advance</label>
    <select class="form-select" name="payment_cod_admin_full_cod_override" id="payment_cod_admin_full_cod_override"><option value="0">Disabled</option><option value="1" @selected(get_payment_setting('admin_full_cod_override','cod',0))>Enabled — separate override permission and reason required</option></select>
    <p class="help-block">Applies only to unpaid incomplete orders without a locked payment attempt. Customer checkout advance requirements stay unchanged.</p>
</div>
