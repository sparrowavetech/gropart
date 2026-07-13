<div class="shipmozo-pincode-checker mb-3">
    <label for="shipmozo-pincode" class="form-label text-muted mb-1" style="font-size: 13px;"><?php echo e(__('Check Delivery Availability')); ?>:</label>
    <div class="input-group">
        <input type="text" id="shipmozo-pincode" class="form-control shipmozo-pincode-input" placeholder="<?php echo e(__('Enter Delivery Pincode')); ?>" style="max-width: 200px;">
        <button type="button" class="btn btn-outline-primary" id="shipmozo-check-btn"><?php echo e(__('Check')); ?></button>
    </div>
    <div id="shipmozo-pincode-result" class="mt-2" style="font-size: 13px;"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkBtn = document.getElementById('shipmozo-check-btn');
        const pincodeInput = document.getElementById('shipmozo-pincode');
        const resultDiv = document.getElementById('shipmozo-pincode-result');

        if (checkBtn && pincodeInput) {
            checkBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const pincode = pincodeInput.value.trim();

                if (!pincode) {
                    resultDiv.innerHTML = '<span class="text-danger"><?php echo e(__("Please enter a valid pincode")); ?></span>';
                    return;
                }

                resultDiv.innerHTML = '<span class="text-info"><?php echo e(__("Checking...")); ?></span>';

                // Set loading state on button
                checkBtn.disabled = true;

                const productId = '<?php echo e(isset($product) ? $product->id : ""); ?>';

                fetch('<?php echo e(route("ecommerce.shipments.shipmozo.check-pincode")); ?>?pincode=' + pincode + '&product_id=' + productId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.data && data.data.pickup_pincode) {
                            console.log('Pickup Pincode:', data.data.pickup_pincode);
                        }
                        if (data.data && data.data.delivery_pincode) {
                            console.log('Drop Pincode:', data.data.delivery_pincode);
                        }

                        if (data.error) {
                            resultDiv.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                        } else {
                            resultDiv.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> ' + data.message + '</span>';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<span class="text-danger"><?php echo e(__("An error occurred. Please try again.")); ?></span>';
                    })
                    .finally(() => {
                        checkBtn.disabled = false;
                    });
            });
        }
    });
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/shipmozo/resources/views/pincode-check.blade.php ENDPATH**/ ?>