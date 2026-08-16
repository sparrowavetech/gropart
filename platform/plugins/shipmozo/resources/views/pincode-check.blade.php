<div class="shipmozo-pincode-checker mb-3">
    <label for="shipmozo-pincode" class="form-label text-muted mb-1" style="font-size: 13px;">{{ __('Check Delivery Availability') }}:</label>
    <div class="input-group">
        <input type="text" id="shipmozo-pincode" class="form-control shipmozo-pincode-input" placeholder="{{ __('Enter Delivery Pincode') }}" style="max-width: 200px;">
        <button type="button" class="btn btn-outline-primary" id="shipmozo-check-btn">{{ __('Check') }}</button>
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

                const showResult = (message, className) => {
                    resultDiv.replaceChildren();
                    const result = document.createElement('span');
                    result.className = className;
                    result.textContent = message;
                    resultDiv.appendChild(result);
                };

                if (!/^[1-9][0-9]{5}$/.test(pincode)) {
                    showResult(@json(__("Please enter a valid pincode")), 'text-danger');
                    return;
                }

                showResult(@json(__("Checking...")), 'text-info');

                // Set loading state on button
                checkBtn.disabled = true;

                const productId = '{{ isset($product) ? $product->id : "" }}';

                fetch('{{ route("ecommerce.shipments.shipmozo.check-pincode") }}?pincode=' + pincode + '&product_id=' + productId, {
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
                        if (data.error) {
                            showResult(data.message, 'text-danger');
                        } else {
                            showResult(data.message, 'text-success');
                        }
                    })
                    .catch(() => {
                        showResult(@json(__("An error occurred. Please try again.")), 'text-danger');
                    })
                    .finally(() => {
                        checkBtn.disabled = false;
                    });
            });
        }
    });
</script>
