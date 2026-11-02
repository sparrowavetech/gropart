<div class="card mb-3" style="background: linear-gradient(135deg, rgba(240, 253, 244, 0.8) 0%, rgba(248, 250, 252, 0.9) 100%); border: 1px solid #bbf7d0; border-radius: 8px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <input type="hidden" name="vendor_managed_shipping" value="0">
                <label class="form-check form-switch mb-0">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="vendor_managed_shipping"
                        id="vendor_managed_shipping"
                        value="1"
                        {{ $vendorShippingValue ? 'checked' : '' }}
                    >
                    <span class="form-check-label fw-bold text-dark fs-5">{{ trans('plugins/indian-gst::indian-gst.store.managed_shipping') }}</span>
                </label>
            </div>
            <span class="badge bg-success-lt text-success px-2 py-1"><i class="ti ti-truck-delivery me-1"></i> {{ trans('plugins/indian-gst::indian-gst.store.self_fulfillment') }}</span>
        </div>
        <small class="form-hint text-muted d-block" style="font-size: 12px; font-weight: normal; line-height: 1.5;">
            {{ trans('plugins/indian-gst::indian-gst.store.managed_shipping_help') }}
        </small>
    </div>
</div>
