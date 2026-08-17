<div class="shipmozo-pincode-checker mb-3">
    <label for="shipmozo-pincode" class="form-label text-muted mb-1" style="font-size: 14px; font-weight: 600;">{{ __('Check Delivery Availability') }}:</label>
    <div class="input-group shipmozo-pincode-group" style="width: 100%;">
        <input type="text" id="shipmozo-pincode" class="form-control shipmozo-pincode-input" placeholder="{{ __('Enter Delivery Pincode') }}" maxlength="6" inputmode="numeric" autocomplete="postal-code" style="min-width: 0;">
        <button type="button" class="btn btn-primary" id="shipmozo-check-btn">{{ __('Check') }}</button>
    </div>
    <div id="shipmozo-pincode-result" class="mt-2"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkBtn = document.getElementById('shipmozo-check-btn');
        const pincodeInput = document.getElementById('shipmozo-pincode');
        const resultDiv = document.getElementById('shipmozo-pincode-result');
        let lastCheckedPincode = '';
        let activeRequest = null;

        if (checkBtn && pincodeInput) {
            const showResult = (message, type) => {
                resultDiv.replaceChildren();

                const icon = type === 'success' ? '✓' : type === 'danger' ? '!' : 'i';
                const result = document.createElement('div');
                result.className = 'alert alert-' + type + ' d-flex align-items-center mb-0';
                result.style.cssText = 'gap: 8px; padding: 7px 10px; font-size: 15px; line-height: 1.35; font-weight: 700;';
                result.innerHTML = '<span aria-hidden="true" style="font-size: 16px; line-height: 1;">' + icon + '</span><span></span>';
                result.querySelector('span:last-child').textContent = message;

                resultDiv.appendChild(result);
            };

            const checkAvailability = () => {
                const pincode = pincodeInput.value.trim();

                if (!/^[1-9][0-9]{5}$/.test(pincode)) {
                    showResult(@json(__("Please enter a valid pincode")), 'danger');
                    return;
                }

                if (pincode === lastCheckedPincode) {
                    return;
                }

                lastCheckedPincode = pincode;
                showResult(@json(__("Checking...")), 'info');
                checkBtn.disabled = true;

                const productId = '{{ isset($product) ? $product->id : "" }}';
                const requestUrl = '{{ route("ecommerce.shipments.shipmozo.check-pincode") }}?pincode=' + pincode + '&product_id=' + productId;

                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new AbortController();

                fetch(requestUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: activeRequest.signal
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            showResult(data.message, 'danger');
                        } else {
                            showResult(data.message, 'success');
                        }
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        lastCheckedPincode = '';
                        showResult(@json(__("An error occurred. Please try again.")), 'danger');
                    })
                    .finally(() => {
                        checkBtn.disabled = false;
                        activeRequest = null;
                    });
            };

            checkBtn.addEventListener('click', function(e) {
                e.preventDefault();
                lastCheckedPincode = '';
                checkAvailability();
            });

            pincodeInput.addEventListener('blur', checkAvailability);

            pincodeInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lastCheckedPincode = '';
                    checkAvailability();
                }
            });
        }
    });
</script>
