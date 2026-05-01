(function () {
    'use strict';

    const translations = window.loyaltyMemberIdTranslations || {};

    function showResult(message, isSuccess) {
        const resultDiv = document.getElementById('member-id-result');
        if (!resultDiv) return;

        resultDiv.style.display = 'block';
        resultDiv.className = 'mt-2 small ' + (isSuccess ? 'text-success' : 'text-danger');

        const icon = isSuccess
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

        resultDiv.innerHTML = icon + message;
    }

    function hideResult() {
        const resultDiv = document.getElementById('member-id-result');
        if (resultDiv) {
            resultDiv.style.display = 'none';
        }
    }

    async function validateMemberId(url) {
        const input = document.getElementById('loyalty_member_id');
        const btn = document.getElementById('validate-member-id-btn');

        if (!input || !btn) return;

        const memberId = input.value.trim();
        if (!memberId) {
            showResult(translations.empty || 'Please enter a phone number or member ID', false);
            return;
        }

        // Show loading state
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ member_id: memberId })
            });

            const data = await response.json();

            if (data.error === false || data.error === undefined) {
                // Success - reload the page to show the validated state
                window.location.reload();
            } else {
                showResult(data.message || 'Member not found', false);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        } catch (error) {
            showResult(translations.genericError || 'An error occurred. Please try again.', false);
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }

    async function removeMemberId(url) {
        const btn = document.getElementById('remove-member-id-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('loading');
        }

        try {
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                }
            });
            window.location.reload();
        } catch (error) {
            window.location.reload();
        }
    }

    function init() {
        // Validate button
        const validateBtn = document.getElementById('validate-member-id-btn');
        if (validateBtn) {
            validateBtn.addEventListener('click', function () {
                const url = this.dataset.url;
                validateMemberId(url);
            });
        }

        // Enter key on input
        const memberIdInput = document.getElementById('loyalty_member_id');
        if (memberIdInput) {
            memberIdInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const validateBtn = document.getElementById('validate-member-id-btn');
                    if (validateBtn) {
                        const url = validateBtn.dataset.url;
                        validateMemberId(url);
                    }
                }
            });

            // Clear error on input
            memberIdInput.addEventListener('input', function () {
                hideResult();
            });
        }

        // Remove button
        const removeBtn = document.getElementById('remove-member-id-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                const url = this.dataset.url;
                removeMemberId(url);
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
