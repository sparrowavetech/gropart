(function () {
    'use strict';

    const VALIDATE_URL = '/admin/loyalty-points/validate-member';

    let memberIdField = null;
    let validatedCustomerId = null;

    function createMemberIdSection() {
        const section = document.createElement('div');
        section.id = 'loyalty-member-id-section';
        section.className = 'card mb-3';
        section.style.display = 'none';
        section.innerHTML = `
            <div class="card-header">
                <h4 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/><path d="M4 8V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2"/><path d="M12 4v16"/><path d="M2 8h20"/></svg>
                    Loyalty Member ID
                </h4>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Enter customer's loyalty member ID to award points for this order when it's completed.</p>
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="loyalty_member_id">Member ID</label>
                        <input type="text"
                               class="form-control"
                               id="loyalty_member_id"
                               name="loyalty_member_id"
                               placeholder="Enter member ID (e.g., L000000123 or scan QR)">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" id="validate-member-id-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Validate
                        </button>
                    </div>
                </div>
                <div id="member-id-result" class="mt-2" style="display: none;"></div>
                <input type="hidden" name="loyalty_member_customer_id" id="loyalty_member_customer_id" value="">
            </div>
        `;
        return section;
    }

    function showSection() {
        if (memberIdField) {
            memberIdField.style.display = 'block';
        }
    }

    function hideSection() {
        if (memberIdField) {
            memberIdField.style.display = 'none';
            // Clear the field when hiding
            const input = document.getElementById('loyalty_member_id');
            const hiddenInput = document.getElementById('loyalty_member_customer_id');
            const resultDiv = document.getElementById('member-id-result');
            if (input) input.value = '';
            if (hiddenInput) hiddenInput.value = '';
            if (resultDiv) {
                resultDiv.style.display = 'none';
                resultDiv.innerHTML = '';
            }
            validatedCustomerId = null;
        }
    }

    function showResult(message, isValid) {
        const resultDiv = document.getElementById('member-id-result');
        if (!resultDiv) return;

        resultDiv.style.display = 'block';
        resultDiv.className = 'mt-2 alert ' + (isValid ? 'alert-success' : 'alert-danger');
        resultDiv.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                ${isValid
                    ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
                    : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'
                }
            </svg>
            ${message}
        `;
    }

    async function validateMemberId() {
        const input = document.getElementById('loyalty_member_id');
        const hiddenInput = document.getElementById('loyalty_member_customer_id');
        const btn = document.getElementById('validate-member-id-btn');

        if (!input || !btn) return;

        const memberId = input.value.trim();
        if (!memberId) {
            showResult('Please enter a member ID', false);
            return;
        }

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validating...';

        try {
            const response = await fetch(VALIDATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ member_id: memberId })
            });

            const data = await response.json();

            if (data.valid) {
                showResult(`Valid member: <strong>${data.customer_name}</strong> (${data.customer_email})<br><small>Points will be awarded to this customer when the order is completed.</small>`, true);
                validatedCustomerId = data.customer_id;
                if (hiddenInput) hiddenInput.value = data.customer_id;
            } else {
                showResult(data.message || 'Invalid member ID', false);
                validatedCustomerId = null;
                if (hiddenInput) hiddenInput.value = '';
            }
        } catch (error) {
            showResult('Error validating member ID. Please try again.', false);
            validatedCustomerId = null;
            if (hiddenInput) hiddenInput.value = '';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg> Validate';
        }
    }

    function observeCustomerSelection() {
        // The Vue component updates a hidden input or uses v-model
        // We need to watch for changes in the customer selection
        const checkCustomerSelection = () => {
            // Look for the customer select element in the Vue component
            const customerInput = document.querySelector('input[name="customer_id"]')
                || document.querySelector('[name="customer_id"]')
                || document.querySelector('.customer-select-input');

            // Also check Vue's reactive data if accessible
            const vueApp = document.querySelector('#app')?.__vue__;

            let hasCustomer = false;

            if (customerInput) {
                hasCustomer = customerInput.value && parseInt(customerInput.value) > 0;
            }

            // Check for customer name display which indicates selection
            const customerDisplay = document.querySelector('.selected-customer-info, .customer-info-display');
            if (customerDisplay && customerDisplay.textContent.trim()) {
                hasCustomer = true;
            }

            if (hasCustomer) {
                hideSection();
            } else {
                showSection();
            }
        };

        // Check periodically for customer selection changes
        setInterval(checkCustomerSelection, 500);

        // Also check on any input changes
        document.addEventListener('change', checkCustomerSelection);
        document.addEventListener('input', checkCustomerSelection);
    }

    function init() {
        // Wait for the page to fully load
        const checkReady = setInterval(() => {
            const orderForm = document.querySelector('form') || document.querySelector('.order-create-form');
            const cardBody = document.querySelector('.card-body');

            if (cardBody) {
                clearInterval(checkReady);

                // Create and insert the member ID section
                memberIdField = createMemberIdSection();

                // Find a good place to insert (after customer info section)
                const firstCard = document.querySelector('.card');
                if (firstCard && firstCard.parentNode) {
                    firstCard.parentNode.insertBefore(memberIdField, firstCard.nextSibling);
                } else if (cardBody) {
                    cardBody.parentNode.insertBefore(memberIdField, cardBody);
                }

                // Bind validation button
                const validateBtn = document.getElementById('validate-member-id-btn');
                if (validateBtn) {
                    validateBtn.addEventListener('click', validateMemberId);
                }

                // Bind enter key on input
                const memberIdInput = document.getElementById('loyalty_member_id');
                if (memberIdInput) {
                    memberIdInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            validateMemberId();
                        }
                    });
                }

                // Start observing customer selection
                observeCustomerSelection();

                // Show section by default (will be hidden if customer is selected)
                showSection();
            }
        }, 100);

        // Timeout after 10 seconds
        setTimeout(() => clearInterval(checkReady), 10000);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
