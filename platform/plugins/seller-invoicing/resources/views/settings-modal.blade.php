<input type="hidden" name="regenerate_existing_invoices" id="regenerate_existing_invoices" value="0">

<!-- Settings Changed Confirmation Modal -->
<div class="modal fade" id="modal-confirm-regenerate-invoices" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-warning">
            <div class="modal-header bg-warning-subtle text-warning-emphasis">
                <h5 class="modal-title fw-bold">
                    <x-core::icon name="ti ti-alert-triangle" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.settings.modal_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="fs-4 mb-2 text-dark">
                    {{ trans('plugins/seller-invoicing::seller-invoicing.settings.modal_desc') }}
                </p>
                <div class="alert alert-info py-2 px-3 small mb-0 mt-3">
                    <x-core::icon name="ti ti-info-circle" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.settings.modal_note') }}
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="btn-save-only">
                    {{ trans('plugins/seller-invoicing::seller-invoicing.settings.btn_save_only') }}
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-and-regenerate">
                    <x-core::icon name="ti ti-refresh" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.settings.btn_save_and_regenerate') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sensitiveFields = [
            'seller_invoicing_tax_mode',
            'seller_invoicing_fee_tax_type',
            'seller_invoicing_tax_rate',
            'seller_invoicing_platform_fee',
            'marketplace_default_platform_fee',
            'seller_invoicing_prefix',
            'seller_invoice_code_suffix'
        ];

        const initialValues = {};
        sensitiveFields.forEach(function (name) {
            const el = document.querySelector('[name="' + name + '"]');
            if (el) {
                initialValues[name] = el.value;
            }
        });

        const form = document.querySelector('form.setting-form') || document.querySelector('form[action*="seller-invoicing/settings"]');
        if (!form) return;

        const regenInput = document.getElementById('regenerate_existing_invoices');
        const modalEl = document.getElementById('modal-confirm-regenerate-invoices');
        let bsModal = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bsModal = new bootstrap.Modal(modalEl);
        }

        let isConfirmed = false;

        form.addEventListener('submit', function (e) {
            if (isConfirmed) return;

            let changed = false;
            sensitiveFields.forEach(function (name) {
                const el = document.querySelector('[name="' + name + '"]');
                if (el && String(el.value).trim() !== String(initialValues[name] || '').trim()) {
                    changed = true;
                }
            });

            if (changed) {
                e.preventDefault();
                e.stopPropagation();

                if (bsModal) {
                    bsModal.show();
                } else if (confirm("{{ addslashes(trans('plugins/seller-invoicing::seller-invoicing.settings.modal_desc')) }}")) {
                    regenInput.value = '1';
                    isConfirmed = true;
                    form.submit();
                } else {
                    regenInput.value = '0';
                    isConfirmed = true;
                    form.submit();
                }
            }
        });

        document.getElementById('btn-save-and-regenerate')?.addEventListener('click', function () {
            regenInput.value = '1';
            isConfirmed = true;
            if (bsModal) bsModal.hide();
            form.submit();
        });

        document.getElementById('btn-save-only')?.addEventListener('click', function () {
            regenInput.value = '0';
            isConfirmed = true;
            if (bsModal) bsModal.hide();
            form.submit();
        });
    });
</script>

