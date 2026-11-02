<div class="mb-3 product-hsn-wrapper">
    <label class="form-label" for="hsn_code">{{ trans('plugins/indian-gst::indian-gst.product.hsn_label') }}</label>
    <input
        type="text"
        name="hsn_code"
        id="hsn_code"
        class="form-control"
        value="{{ $value }}"
        placeholder="{{ trans('plugins/indian-gst::indian-gst.product.hsn_placeholder') }}"
        maxlength="50"
    >
    <small class="form-hint text-muted">{{ trans('plugins/indian-gst::indian-gst.product.hsn_help') }}</small>
</div>
