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

<!-- Progress Loader Modal -->
<div class="modal fade" id="modal-progress-regenerate-invoices" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-primary">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <x-core::icon name="ti ti-refresh" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_title') }}
                </h5>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary mb-2" role="status" id="regen-spinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div id="regen-status-heading" class="fw-bold fs-4 text-dark">
                        {{ trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_title') }}
                    </div>
                    <div class="text-muted small" id="regen-status-subheading">
                        {{ trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_desc') }}
                    </div>
                </div>

                <div class="progress mb-2" style="height: 22px;">
                    <div id="regen-progress-bar"
                         class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold"
                         role="progressbar"
                         style="width: 0%; transition: width 0.3s ease;"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        0%
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
                    <span>
                        <x-core::icon name="ti ti-file-invoice" class="me-1" />
                        <span id="regen-processed-count">0</span> / <span id="regen-total-count">...</span>
                    </span>
                    <span id="regen-percentage-text" class="fw-bold text-primary">0%</span>
                </div>

                <div class="alert alert-warning py-2 px-3 small mb-0 d-flex align-items-center" id="regen-warning-box">
                    <x-core::icon name="ti ti-alert-triangle" class="me-2 flex-shrink-0 fs-3" />
                    <div>{{ trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_warning') }}</div>
                </div>

                <div class="alert alert-danger py-2 px-3 small mb-0 mt-3 d-none" id="regen-error-box">
                    <x-core::icon name="ti ti-alert-circle" class="me-1" />
                    <span id="regen-error-msg"></span>
                </div>
            </div>
            <div class="modal-footer d-none" id="regen-completed-footer">
                <button type="button" class="btn btn-success" id="btn-regen-done" onclick="window.location.reload();">
                    <x-core::icon name="ti ti-check" class="me-1" /> OK
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
        const confirmModalEl = document.getElementById('modal-confirm-regenerate-invoices');
        const progressModalEl = document.getElementById('modal-progress-regenerate-invoices');

        let bsConfirmModal = null;
        let bsProgressModal = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bsConfirmModal = new bootstrap.Modal(confirmModalEl);
            bsProgressModal = new bootstrap.Modal(progressModalEl, { backdrop: 'static', keyboard: false });
        }

        let isConfirmed = false;

        const beforeUnloadHandler = function (e) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        };

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

                if (bsConfirmModal) {
                    bsConfirmModal.show();
                } else if (confirm("{{ addslashes(trans('plugins/seller-invoicing::seller-invoicing.settings.modal_desc')) }}")) {
                    startBatchRegeneration();
                } else {
                    regenInput.value = '0';
                    isConfirmed = true;
                    form.submit();
                }
            }
        });

        document.getElementById('btn-save-only')?.addEventListener('click', function () {
            regenInput.value = '0';
            isConfirmed = true;
            if (bsConfirmModal) bsConfirmModal.hide();
            form.submit();
        });

        document.getElementById('btn-save-and-regenerate')?.addEventListener('click', function () {
            if (bsConfirmModal) bsConfirmModal.hide();
            startBatchRegeneration();
        });

        function startBatchRegeneration() {
            if (bsProgressModal) {
                bsProgressModal.show();
            }

            window.addEventListener('beforeunload', beforeUnloadHandler);

            // Step 1: Save settings first via AJAX without running the heavy synchronous regeneration loop
            regenInput.value = '0';
            const formData = new FormData(form);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              form.querySelector('input[name="_token"]')?.value;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to save settings. Status: ' + response.status);
                }
                return response.json();
            })
            .then(function () {
                // Step 2: Now run the batch regeneration loop
                let offset = 0;
                const limit = 25;

                function processBatch() {
                    fetch('{{ route('seller-invoicing.settings.regenerate-batch') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ offset: offset, limit: limit })
                    })
                    .then(function (r) {
                        if (!r.ok) {
                            throw new Error('Batch failed. Status: ' + r.status);
                        }
                        return r.json();
                    })
                    .then(function (json) {
                        const data = json.data || json;
                        const total = data.total || 0;
                        const processed = data.processed || 0;
                        const percentage = data.percentage || 0;

                        document.getElementById('regen-total-count').innerText = total;
                        document.getElementById('regen-processed-count').innerText = processed;
                        document.getElementById('regen-percentage-text').innerText = percentage + '%';

                        const bar = document.getElementById('regen-progress-bar');
                        bar.style.width = percentage + '%';
                        bar.innerText = percentage + '%';
                        bar.setAttribute('aria-valuenow', percentage);

                        if (data.finished || processed >= total || total === 0) {
                            // Finished successfully
                            window.removeEventListener('beforeunload', beforeUnloadHandler);

                            bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                            bar.classList.remove('bg-primary');
                            bar.classList.add('bg-success');

                            document.getElementById('regen-spinner').classList.add('d-none');
                            document.getElementById('regen-status-heading').innerText = "{{ addslashes(trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_completed')) }}";
                            document.getElementById('regen-status-subheading').innerText = "{{ addslashes(trans('plugins/seller-invoicing::seller-invoicing.settings.regenerating_completed_desc')) }}";
                            document.getElementById('regen-warning-box').classList.add('d-none');
                            document.getElementById('regen-completed-footer').classList.remove('d-none');

                            if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                                Botble.showSuccess("{{ addslashes(trans('plugins/seller-invoicing::seller-invoicing.settings.regenerated_success', ['count' => ''])) }}" + total);
                            }

                            setTimeout(function () {
                                window.location.reload();
                            }, 1800);
                        } else {
                            offset = data.next_offset;
                            processBatch();
                        }
                    })
                    .catch(function (batchErr) {
                        window.removeEventListener('beforeunload', beforeUnloadHandler);
                        document.getElementById('regen-spinner').classList.add('d-none');
                        const errBox = document.getElementById('regen-error-box');
                        errBox.classList.remove('d-none');
                        document.getElementById('regen-error-msg').innerText = batchErr.message || 'Error occurred during invoice regeneration.';
                    });
                }

                processBatch();
            })
            .catch(function (saveErr) {
                window.removeEventListener('beforeunload', beforeUnloadHandler);
                document.getElementById('regen-spinner').classList.add('d-none');
                const errBox = document.getElementById('regen-error-box');
                errBox.classList.remove('d-none');
                document.getElementById('regen-error-msg').innerText = saveErr.message || 'Error saving settings.';
            });
        }
    });
</script>
