<div id="license-activation-form" data-action="{{ route('wholesale.license.activate') }}">
    <div class="mb-2">
        <label for="license_purchase_code" class="form-label small mb-1">
            {{ trans('plugins/ecommerce-wholesale::wholesale.license.purchase_code_label') }}
            <span class="text-danger">*</span>
            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank" class="ms-1 text-muted">
                <x-core::icon name="ti ti-help" />
            </a>
        </label>
        <input type="text"
               class="form-control form-control-sm"
               id="license_purchase_code"
               autocomplete="off"
               placeholder="{{ trans('plugins/ecommerce-wholesale::wholesale.license.purchase_code_placeholder') }}">
    </div>

    <div class="form-check mb-2">
        <input class="form-check-input"
               type="checkbox"
               id="license_rules_agreement">
        <label class="form-check-label small" for="license_rules_agreement">
            {{ trans('plugins/ecommerce-wholesale::wholesale.license.agreement_text') }}
        </label>
    </div>

    <button type="button" class="btn btn-primary btn-sm" id="activate-license-btn">
        <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
        {{ trans('plugins/ecommerce-wholesale::wholesale.license.activate') }}
    </button>
</div>
