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
