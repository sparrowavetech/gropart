<div class="mb-3 product-hsn-wrapper">
    <label class="form-label" for="hsn_code">{{ __('HSN / SAC Code') }}</label>
    <input
        type="text"
        name="hsn_code"
        id="hsn_code"
        class="form-control"
        value="{{ $value }}"
        placeholder="{{ __('e.g. 84099941') }}"
        maxlength="50"
    >
    <small class="form-hint text-muted">{{ __('Harmonized System Nomenclature code for GST reporting.') }}</small>
</div>
