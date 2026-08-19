document.addEventListener('DOMContentLoaded', function () {
    const skuField = document.querySelector('input[name="sku"], input#sku');
    const hsnField = document.querySelector('.product-hsn-wrapper');

    if (skuField && hsnField) {
        const skuGroup = skuField.closest('.form-group, .mb-3, .col-md-6, .col-12');
        if (skuGroup && skuGroup.parentElement && hsnField.parentElement !== skuGroup.parentElement) {
            skuGroup.insertAdjacentElement('afterend', hsnField);
        }
    }
});
