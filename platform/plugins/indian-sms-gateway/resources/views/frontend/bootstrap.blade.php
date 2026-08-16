<style>
.india-sms-modal-backdrop{position:fixed;inset:0;z-index:999999;background:rgba(3,12,23,.62);display:flex;align-items:center;justify-content:center;padding:18px;font-family:inherit}.india-sms-modal{width:min(430px,100%);background:#fff;border-radius:14px;box-shadow:0 24px 70px rgba(0,0,0,.28);overflow:hidden;color:#071525}.india-sms-modal__head{display:flex;align-items:flex-start;justify-content:space-between;padding:22px 22px 12px}.india-sms-modal__title{font-size:22px;font-weight:700;line-height:1.25;margin:0}.india-sms-modal__close{border:0;background:transparent;font-size:28px;line-height:1;color:#667085;cursor:pointer;padding:0 0 0 16px}.india-sms-modal__body{padding:4px 22px 22px}.india-sms-modal__text{font-size:14px;line-height:1.65;color:#667085;margin:0 0 16px}.india-sms-field{margin-bottom:14px}.india-sms-field label{display:block;font-weight:600;font-size:13px;margin-bottom:7px}.india-sms-field input{width:100%;height:48px;border:1px solid #d8dde5;border-radius:8px;padding:0 14px;font-size:16px;outline:0;box-sizing:border-box}.india-sms-field input:focus{border-color:#ff5a00;box-shadow:0 0 0 3px rgba(255,90,0,.12)}.india-sms-code{letter-spacing:8px;text-align:center;font-size:23px!important;font-weight:700}.india-sms-btn{width:100%;height:48px;border:0;border-radius:7px;background:#061727;color:#fff;font-weight:700;cursor:pointer;font-size:15px}.india-sms-btn:hover{background:#ff5a00}.india-sms-btn:disabled{opacity:.55;cursor:not-allowed}.india-sms-btn--outline{background:#fff;color:#061727;border:1px solid #ccd3dc;margin-top:10px}.india-sms-btn--outline:hover{color:#ff5a00;background:#fff;border-color:#ff5a00}.india-sms-message{font-size:13px;padding:10px 12px;border-radius:7px;margin-bottom:13px;display:none}.india-sms-message.is-error{display:block;background:#fff1f0;color:#b42318}.india-sms-message.is-success{display:block;background:#ecfdf3;color:#027a48}.india-sms-resend{display:flex;justify-content:space-between;align-items:center;margin-top:12px;font-size:13px;color:#667085}.india-sms-resend button{border:0;background:transparent;color:#ff5a00;padding:0;cursor:pointer;font-weight:600}.india-sms-resend button:disabled{color:#98a2b3;cursor:not-allowed}.india-sms-integration-note{font-size:13px;line-height:1.45;color:#475467;background:#fff7ed;border:1px solid #fed7aa;padding:10px 12px;border-radius:7px;margin-top:8px}.india-sms-alt-button{display:block;width:100%;min-height:48px;border:1px solid #ff5a00;background:#fff;color:#ff5a00;font-weight:700;margin-top:12px;cursor:pointer;padding:10px 14px}.india-sms-alt-button:hover{background:#ff5a00;color:#fff}.india-sms-divider{display:flex;align-items:center;gap:10px;color:#98a2b3;font-size:12px;margin:14px 0}.india-sms-divider:before,.india-sms-divider:after{content:"";height:1px;background:#e4e7ec;flex:1}.india-sms-toast{position:fixed;right:20px;bottom:20px;z-index:1000000;max-width:360px;background:#061727;color:#fff;padding:13px 16px;border-radius:8px;box-shadow:0 12px 40px rgba(0,0,0,.24);font-size:14px}.india-sms-toast.is-error{background:#b42318}@media(max-width:575px){.india-sms-modal__head{padding:18px 18px 10px}.india-sms-modal__body{padding:4px 18px 18px}.india-sms-modal__title{font-size:20px}}
</style>
<script>
(function () {
    'use strict';

    const config = @json($config);
    const state = { registrationVerified: false, checkoutVerified: false };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    async function post(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload),
        });
        let data = {};
        try { data = await response.json(); } catch (error) { data = {}; }
        if (!response.ok || data.success === false || data.error === true) {
            const errors = data.errors ? Object.values(data.errors).flat() : [];
            throw new Error(data.message || errors[0] || 'The request could not be completed.');
        }
        return data;
    }

    function toast(message, isError) {
        const node = document.createElement('div');
        node.className = 'india-sms-toast' + (isError ? ' is-error' : '');
        node.textContent = message;
        document.body.appendChild(node);
        window.setTimeout(() => node.remove(), 4200);
    }

    function createModal(title, text) {
        const backdrop = document.createElement('div');
        backdrop.className = 'india-sms-modal-backdrop';
        backdrop.innerHTML = '<div class="india-sms-modal" role="dialog" aria-modal="true">' +
            '<div class="india-sms-modal__head"><h3 class="india-sms-modal__title"></h3><button type="button" class="india-sms-modal__close" aria-label="Close">&times;</button></div>' +
            '<div class="india-sms-modal__body"><p class="india-sms-modal__text"></p><div class="india-sms-message"></div><div class="india-sms-modal__content"></div></div></div>';
        backdrop.querySelector('.india-sms-modal__title').textContent = title;
        backdrop.querySelector('.india-sms-modal__text').textContent = text || '';
        document.body.appendChild(backdrop);
        const closeButton = backdrop.querySelector('.india-sms-modal__close');
        const content = backdrop.querySelector('.india-sms-modal__content');
        const message = backdrop.querySelector('.india-sms-message');
        let rejectClose = null;
        function close() { backdrop.remove(); if (rejectClose) rejectClose(new Error('Verification cancelled.')); }
        closeButton.addEventListener('click', close);
        backdrop.addEventListener('click', function (event) { if (event.target === backdrop) close(); });
        return {
            backdrop,
            content,
            message,
            close: function () { rejectClose = null; backdrop.remove(); },
            setReject: function (handler) { rejectClose = handler; },
            showMessage: function (textValue, type) {
                message.textContent = textValue;
                message.className = 'india-sms-message ' + (type === 'success' ? 'is-success' : 'is-error');
            },
            clearMessage: function () { message.className = 'india-sms-message'; message.textContent = ''; },
        };
    }

    function findPhoneInput(root) {
        const selectors = [
            'input[name="phone"]', 'input[name="mobile"]', 'input[name="mobile_number"]',
            'input[name*="[phone]"]', 'input[type="tel"]', 'input[name*="phone" i]'
        ];
        for (const selector of selectors) {
            const input = root.querySelector(selector);
            if (input) return input;
        }
        return null;
    }

    function looksLikePhone(value) {
        return /(?:\+?88)?01[3-9]\d{8}/.test(String(value || '').replace(/[\s-]/g, ''));
    }

    function askPhone(title) {
        return new Promise(function (resolve, reject) {
            const modal = createModal(title, 'Enter the mobile number connected to your account.');
            modal.setReject(reject);
            modal.content.innerHTML = '<div class="india-sms-field"><label>Mobile number</label><input type="tel" autocomplete="tel" placeholder="01XXXXXXXXX"></div><button type="button" class="india-sms-btn">Continue</button>';
            const input = modal.content.querySelector('input');
            const button = modal.content.querySelector('button');
            button.addEventListener('click', function () {
                const phone = input.value.trim();
                if (!looksLikePhone(phone)) { modal.showMessage('Please enter a valid India mobile number.'); return; }
                modal.close(); resolve(phone);
            });
            input.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); button.click(); } });
            window.setTimeout(() => input.focus(), 50);
        });
    }

    async function otpFlow(phone, purpose, title, extra) {
        const requestPayload = Object.assign({ phone, purpose }, extra || {});
        await post(config.requestUrl, requestPayload);
        return new Promise(function (resolve, reject) {
            const modal = createModal(title, 'We sent a verification code to ' + phone + '. Enter the code below.');
            modal.setReject(reject);
            modal.content.innerHTML = '<div class="india-sms-field"><label>Verification code</label><input class="india-sms-code" inputmode="numeric" autocomplete="one-time-code" maxlength="' + Number(config.codeLength || 6) + '" placeholder="••••••"></div>' +
                '<button type="button" class="india-sms-btn india-sms-verify">Verify code</button>' +
                '<div class="india-sms-resend"><span class="india-sms-countdown"></span><button type="button" class="india-sms-resend-btn" disabled>Resend OTP</button></div>';
            const input = modal.content.querySelector('input');
            const verifyButton = modal.content.querySelector('.india-sms-verify');
            const resendButton = modal.content.querySelector('.india-sms-resend-btn');
            const countdown = modal.content.querySelector('.india-sms-countdown');
            let seconds = Number(config.resendCooldown || 60);
            let timer = null;
            function startTimer() {
                seconds = Number(config.resendCooldown || 60);
                resendButton.disabled = true;
                countdown.textContent = 'Resend available in ' + seconds + 's';
                if (timer) window.clearInterval(timer);
                timer = window.setInterval(function () {
                    seconds -= 1;
                    if (seconds <= 0) {
                        window.clearInterval(timer);
                        timer = null;
                        countdown.textContent = '';
                        resendButton.disabled = false;
                    } else {
                        countdown.textContent = 'Resend available in ' + seconds + 's';
                    }
                }, 1000);
            }
            startTimer();
            verifyButton.addEventListener('click', async function () {
                const code = input.value.replace(/\D/g, '');
                if (code.length < 4) { modal.showMessage('Enter the complete OTP code.'); return; }
                verifyButton.disabled = true;
                modal.clearMessage();
                try {
                    const result = await post(config.verifyUrl, { phone, purpose, code });
                    if (timer) window.clearInterval(timer);
                    modal.showMessage(result.message || 'Mobile number verified.', 'success');
                    window.setTimeout(function () { modal.close(); resolve(result.verification_token); }, 450);
                } catch (error) {
                    modal.showMessage(error.message);
                    verifyButton.disabled = false;
                    input.select();
                }
            });
            resendButton.addEventListener('click', async function () {
                resendButton.disabled = true;
                modal.clearMessage();
                try {
                    const result = await post(config.requestUrl, requestPayload);
                    modal.showMessage(result.message || 'A new OTP was sent.', 'success');
                    startTimer();
                } catch (error) {
                    modal.showMessage(error.message);
                    resendButton.disabled = false;
                }
            });
            input.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); verifyButton.click(); } });
            input.addEventListener('input', function () { input.value = input.value.replace(/\D/g, '').slice(0, Number(config.codeLength || 6)); });
            window.setTimeout(() => input.focus(), 50);
        });
    }

    function passwordResetForm(phone, token) {
        return new Promise(function (resolve, reject) {
            const modal = createModal('Set a new password', 'Create a new password for your account.');
            modal.setReject(reject);
            modal.content.innerHTML = '<div class="india-sms-field"><label>New password</label><input type="password" autocomplete="new-password"></div>' +
                '<div class="india-sms-field"><label>Confirm new password</label><input type="password" autocomplete="new-password"></div>' +
                '<button type="button" class="india-sms-btn">Change password</button>';
            const inputs = modal.content.querySelectorAll('input');
            const button = modal.content.querySelector('button');
            button.addEventListener('click', async function () {
                const password = inputs[0].value;
                const confirmation = inputs[1].value;
                if (password.length < 6) { modal.showMessage('Password must be at least 6 characters.'); return; }
                if (password !== confirmation) { modal.showMessage('Password confirmation does not match.'); return; }
                button.disabled = true;
                try {
                    const result = await post(config.resetUrl, { phone, verification_token: token, password, password_confirmation: confirmation });
                    modal.showMessage(result.message || 'Password changed successfully.', 'success');
                    window.setTimeout(function () { modal.close(); resolve(result); }, 600);
                } catch (error) {
                    modal.showMessage(error.message);
                    button.disabled = false;
                }
            });
            window.setTimeout(() => inputs[0].focus(), 50);
        });
    }

    function addHidden(form, name, value) {
        let input = form.querySelector('input[name="' + name + '"]');
        if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = name; form.appendChild(input); }
        input.value = value;
    }

    function resumeSubmit(form, submitter) {
        if (typeof form.requestSubmit === 'function') {
            if (submitter && submitter.form === form && (submitter.type === 'submit' || submitter.tagName === 'BUTTON')) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    }

    function setupRegistration() {
        if (!config.registration) return;
        const path = window.location.pathname.toLowerCase();
        if (!path.includes('register') && !path.includes('sign-up') && !path.includes('signup')) return;
        const forms = Array.from(document.querySelectorAll('form'));
        const form = forms.find(function (item) {
            if (!findPhoneInput(item)) return false;
            const action = String(item.action || '').toLowerCase();
            return action.includes('register') || action.includes('signup') || action.includes('sign-up')
                || item.querySelector('input[type="password"]')
                || item.querySelector('input[name="email"]');
        });
        if (!form || form.dataset.bdSmsRegistration === '1') return;
        form.dataset.bdSmsRegistration = '1';
        const phoneInput = findPhoneInput(form);
        const note = document.createElement('div');
        note.className = 'india-sms-integration-note';
        note.textContent = 'Mobile verification is required. An OTP will be sent when you submit the registration form.';
        (phoneInput.closest('.form-group, .mb-3, .mb-4, .field-wrapper') || phoneInput.parentElement).appendChild(note);
        phoneInput.addEventListener('input', function () {
            state.registrationVerified = false;
            form.querySelector('input[name="india_sms_otp_token"]')?.remove();
        });
        form.addEventListener('submit', async function (event) {
            if (state.registrationVerified || form.querySelector('input[name="india_sms_otp_token"]')?.value) return;
            const phone = phoneInput.value.trim();
            if (!looksLikePhone(phone)) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const submitter = event.submitter;
            try {
                const nameInput = form.querySelector('input[name="name"], input[name="first_name"], input[name*="name" i]');
                const token = await otpFlow(phone, 'registration', 'Verify your mobile number', { customer_name: nameInput?.value?.trim() || '' });
                addHidden(form, 'india_sms_otp_token', token);
                state.registrationVerified = true;
                resumeSubmit(form, submitter);
            } catch (error) {
                if (error.message !== 'Verification cancelled.') toast(error.message, true);
            }
        }, true);
    }

    function setupLogin() {
        if (!config.login) return;
        const path = window.location.pathname.toLowerCase();
        if ((!path.includes('login') && !path.includes('sign-in') && !path.includes('signin')) || path.includes('register')) return;
        const forms = Array.from(document.querySelectorAll('form'));
        const form = forms.find(item => item.querySelector('input[type="password"]') && (item.querySelector('input[name="email"]') || findPhoneInput(item)));
        if (!form || form.dataset.bdSmsLogin === '1') return;
        form.dataset.bdSmsLogin = '1';
        const divider = document.createElement('div');
        divider.className = 'india-sms-divider'; divider.textContent = 'OR';
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'india-sms-alt-button'; button.textContent = 'Login with OTP';
        const submit = form.querySelector('button[type="submit"], input[type="submit"]');
        const anchor = submit?.parentElement || form;
        anchor.insertAdjacentElement('afterend', divider);
        divider.insertAdjacentElement('afterend', button);
        button.addEventListener('click', async function () {
            try {
                let input = findPhoneInput(form) || form.querySelector('input[name="email"]');
                let phone = input?.value?.trim() || '';
                if (!looksLikePhone(phone)) phone = await askPhone('Login with OTP');
                const token = await otpFlow(phone, 'login', 'Login verification');
                button.disabled = true;
                const result = await post(config.loginUrl, { phone, verification_token: token, remember: Boolean(form.querySelector('input[name="remember"]')?.checked) });
                toast(result.message || 'Login successful.');
                window.location.href = result.redirect || '/customer/overview';
            } catch (error) {
                button.disabled = false;
                if (error.message !== 'Verification cancelled.') toast(error.message, true);
            }
        });
    }

    function setupPasswordReset() {
        if (!config.passwordReset) return;
        const path = window.location.pathname.toLowerCase();
        if (!path.includes('password') && !path.includes('forgot')) return;
        const form = Array.from(document.querySelectorAll('form')).find(item => item.querySelector('input[type="email"], input[name="email"]'));
        if (!form || form.dataset.bdSmsReset === '1') return;
        form.dataset.bdSmsReset = '1';
        const divider = document.createElement('div'); divider.className = 'india-sms-divider'; divider.textContent = 'OR';
        const button = document.createElement('button'); button.type = 'button'; button.className = 'india-sms-alt-button'; button.textContent = 'Reset password with mobile OTP';
        form.appendChild(divider); form.appendChild(button);
        button.addEventListener('click', async function () {
            try {
                const phone = await askPhone('Reset password with OTP');
                const token = await otpFlow(phone, 'password_reset', 'Password reset verification');
                const result = await passwordResetForm(phone, token);
                toast(result.message || 'Password changed successfully.');
                window.location.href = result.redirect || '/customer/login';
            } catch (error) {
                if (error.message !== 'Verification cancelled.') toast(error.message, true);
            }
        });
    }

    function setupCheckout() {
        if (!config.checkout) return;
        const path = window.location.pathname.toLowerCase();
        if (!path.includes('checkout')) return;
        const forms = Array.from(document.querySelectorAll('form'));
        const form = forms.find(item => findPhoneInput(item) && (String(item.action).toLowerCase().includes('checkout') || item.querySelector('[name="payment_method"], [name="shipping_method"]')));
        if (!form || form.dataset.bdSmsCheckout === '1') return;
        form.dataset.bdSmsCheckout = '1';
        const phoneInput = findPhoneInput(form);
        phoneInput?.addEventListener('input', function () {
            state.checkoutVerified = false;
            form.querySelector('input[name="india_sms_checkout_token"]')?.remove();
        });
        form.addEventListener('submit', async function (event) {
            if (state.checkoutVerified || form.querySelector('input[name="india_sms_checkout_token"]')?.value) return;
            const phone = phoneInput?.value?.trim() || '';
            if (!looksLikePhone(phone)) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const submitter = event.submitter;
            try {
                const token = await otpFlow(phone, 'checkout', 'Confirm your order');
                addHidden(form, 'india_sms_checkout_token', token);
                state.checkoutVerified = true;
                resumeSubmit(form, submitter);
            } catch (error) {
                if (error.message !== 'Verification cancelled.') toast(error.message, true);
            }
        }, true);
    }

    function boot() {
        setupRegistration();
        setupLogin();
        setupPasswordReset();
        setupCheckout();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
    window.setTimeout(boot, 800);
    window.setTimeout(boot, 2200);

    // Some Botble themes render checkout/auth blocks after AJAX navigation.
    // Re-run the idempotent setup functions when new form nodes appear.
    const observer = new MutationObserver(function (mutations) {
        const hasFormChanges = mutations.some(function (mutation) {
            return Array.from(mutation.addedNodes || []).some(function (node) {
                return node.nodeType === 1 && (node.matches?.('form') || node.querySelector?.('form'));
            });
        });
        if (hasFormChanges) window.setTimeout(boot, 0);
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
