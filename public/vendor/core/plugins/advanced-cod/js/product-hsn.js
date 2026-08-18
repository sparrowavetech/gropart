(function () {
    'use strict';

    function closestColumn(element) {
        return element ? element.closest('.col-md-6, .col-lg-6, .col-12, [class*="col-"]') : null;
    }

    function normalizeProductHsnFields() {
        document.querySelectorAll('.product-hsn-code-field').forEach(function (field) {
            var container = field.closest('form, .modal-body, .modal-content') || document;
            var skuInput = container.querySelector('input[name="sku"]');
            var skuColumn = closestColumn(skuInput);

            if (skuColumn && skuColumn.nextElementSibling !== field) {
                skuColumn.insertAdjacentElement('afterend', field);
            }

            var barcodeInput = container.querySelector('input[name="barcode"]');
            var barcodeWrapper = barcodeInput ? barcodeInput.closest('.mb-3, .form-group, .position-relative') : null;
            var barcodeLabel = barcodeWrapper ? barcodeWrapper.querySelector('label') : null;

            if (barcodeLabel) {
                barcodeLabel.textContent = 'Barcode';
            }
        });
    }

    function scheduleNormalize() {
        window.setTimeout(normalizeProductHsnFields, 100);
        window.setTimeout(normalizeProductHsnFields, 350);
    }

    document.addEventListener('DOMContentLoaded', scheduleNormalize);
    document.addEventListener('shown.bs.modal', scheduleNormalize);
    document.addEventListener('click', function () {
        scheduleNormalize();
    });

    if (window.jQuery) {
        window.jQuery(document).ajaxComplete(scheduleNormalize);
    }
})();
