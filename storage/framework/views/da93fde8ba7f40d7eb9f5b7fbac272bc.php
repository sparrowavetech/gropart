<?php
    $isLicenseVerified = false;
    $licenseData = null;

    $licenseStatus = setting('wholesale_license_status');
    $purchaseCode = setting('wholesale_license_purchase_code');
    $activatedAt = setting('wholesale_license_activated_at');

    if ($licenseStatus === 'activated' && $purchaseCode && $activatedAt) {
        $isLicenseVerified = true;

        \Botble\EcommerceWholesale\Services\LicenseEncryptionService::migrateExistingPurchaseCode();

        $decryptedPurchaseCode = \Botble\EcommerceWholesale\Services\LicenseEncryptionService::decryptPurchaseCode($purchaseCode);

        $licenseData = [
            'purchase_code' => $decryptedPurchaseCode,
            'activated_at' => \Carbon\Carbon::parse($activatedAt)->format('M d Y'),
        ];
    }
?>

<div id="license-section" class="border rounded p-3 mb-4 bg-light">
    <h6 class="mb-3">
        <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-key'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
        <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.license.title')); ?>

    </h6>

    <?php if($isLicenseVerified && $licenseData): ?>
        <?php echo $__env->make('plugins/ecommerce-wholesale::settings.partials.license-activated', ['licenseData' => $licenseData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('plugins/ecommerce-wholesale::settings.partials.license-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>

<script>
(function() {
    var translations = {
        somethingWentWrong: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.something_went_wrong'), 15, 512) ?>,
        deactivateLicenseConfirm: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.deactivate_license_confirm'), 15, 512) ?>,
        showPurchaseCode: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.show_purchase_code'), 15, 512) ?>,
        hidePurchaseCode: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.hide_purchase_code'), 15, 512) ?>,
        enterPurchaseCode: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.enter_purchase_code'), 15, 512) ?>,
        acceptAgreement: <?php echo json_encode(trans('plugins/ecommerce-wholesale::wholesale.license.js.accept_agreement'), 15, 512) ?>
    };

    function getTranslation(key) {
        return translations[key] || key;
    }

    function initLicenseActivation() {
        var container = document.getElementById("license-activation-form");
        var activateBtn = document.getElementById("activate-license-btn");
        var deactivateBtn = document.getElementById("deactivate-license-btn");
        var toggleBtn = document.getElementById("toggle-purchase-code");

        if (activateBtn && container) {
            activateBtn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                var purchaseCode = container.querySelector("#license_purchase_code");
                var agreement = container.querySelector("#license_rules_agreement");
                var spinner = activateBtn.querySelector(".spinner-border");

                if (!purchaseCode || !purchaseCode.value.trim()) {
                    Botble.showError(getTranslation("enterPurchaseCode"));
                    purchaseCode && purchaseCode.focus();
                    return;
                }

                if (!agreement || !agreement.checked) {
                    Botble.showError(getTranslation("acceptAgreement"));
                    return;
                }

                var formData = new FormData();
                formData.append("purchase_code", purchaseCode.value.trim());
                formData.append("license_rules_agreement", "1");

                activateBtn.disabled = true;
                spinner && spinner.classList.remove("d-none");

                fetch(container.dataset.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.error) {
                        Botble.showError(data.message);
                    } else {
                        Botble.showSuccess(data.message);
                        setTimeout(function() { window.location.reload(); }, 1000);
                    }
                })
                .catch(function() {
                    Botble.showError(getTranslation("somethingWentWrong"));
                })
                .finally(function() {
                    activateBtn.disabled = false;
                    spinner && spinner.classList.add("d-none");
                });
            });
        }

        if (deactivateBtn) {
            deactivateBtn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!confirm(getTranslation("deactivateLicenseConfirm"))) {
                    return;
                }

                var urlContainer = document.querySelector("[data-deactivate-url]");
                var url = urlContainer ? urlContainer.dataset.deactivateUrl : null;

                if (!url) {
                    Botble.showError(getTranslation("somethingWentWrong"));
                    return;
                }

                deactivateBtn.disabled = true;

                fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.error) {
                        Botble.showError(data.message);
                    } else {
                        Botble.showSuccess(data.message);
                        setTimeout(function() { window.location.reload(); }, 1000);
                    }
                })
                .catch(function() {
                    Botble.showError(getTranslation("somethingWentWrong"));
                })
                .finally(function() {
                    deactivateBtn.disabled = false;
                });
            });
        }

        if (toggleBtn) {
            var isVisible = false;
            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                var display = document.getElementById("purchase-code-display");
                var showIcon = document.getElementById("show-icon");
                var hideIcon = document.getElementById("hide-icon");

                if (!display || !this.dataset.fullCode || !this.dataset.maskedCode) return;

                if (isVisible) {
                    display.textContent = this.dataset.maskedCode;
                    showIcon && (showIcon.style.display = "inline");
                    hideIcon && (hideIcon.style.display = "none");
                    isVisible = false;
                } else {
                    display.textContent = this.dataset.fullCode;
                    showIcon && (showIcon.style.display = "none");
                    hideIcon && (hideIcon.style.display = "inline");
                    isVisible = true;
                }
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initLicenseActivation);
    } else {
        initLicenseActivation();
    }
})();
</script>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\plugins\ecommerce-wholesale\/resources/views/settings/license-section.blade.php ENDPATH**/ ?>