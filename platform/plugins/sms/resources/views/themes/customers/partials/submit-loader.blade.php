<style>
    .sms-submit-spinner { display: inline-block; width: 1em; height: 1em; margin-inline-end: .55em; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; vertical-align: -.15em; animation: sms-submit-spin .7s linear infinite; }
    .sms-submit-status { margin-top: .7rem; color: #5d6670; font-size: .9rem; text-align: center; }
    @keyframes sms-submit-spin { to { transform: rotate(360deg); } }
</style>
<script>
(function () {
    var actions = [
        { path: @json(parse_url(route('customer.register.post'), PHP_URL_PATH)), label: @json($registrationOtpEnabled ? trans('plugins/sms::sms.register_sending_otp') : trans('plugins/sms::sms.register_processing')) },
        { path: @json(parse_url(route('customer.login.otp.send'), PHP_URL_PATH)), label: @json(trans('plugins/sms::sms.sending_otp')) },
        { path: @json(parse_url(route('customer.resend'), PHP_URL_PATH)), label: @json(trans('plugins/sms::sms.resending_otp')) },
        { path: @json(parse_url(route('customer.otp.changePhone'), PHP_URL_PATH)), label: @json(trans('plugins/sms::sms.sending_otp')) }
    ];

    function initialize() {
        document.querySelectorAll('form[action]').forEach(function (form) {
            if (form.dataset.smsLoaderReady || form.method.toUpperCase() !== 'POST') return;
            var actionPath;
            try { actionPath = new URL(form.action, window.location.href).pathname; } catch (error) { return; }
            var action = actions.find(function (item) { return item.path === actionPath; });
            if (!action) return;
            form.dataset.smsLoaderReady = '1';
            var button = form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button) return;
            var spinner;
            var status;
            var originalValue;
            function reset() {
                form.dataset.smsSubmitting = '';
                button.disabled = false;
                button.removeAttribute('aria-busy');
                if (originalValue !== undefined) button.value = originalValue;
                if (spinner) spinner.remove();
                if (status) status.remove();
                spinner = status = null;
            }
            function show() {
                if (status) return;
                spinner = document.createElement('span');
                spinner.className = 'sms-submit-spinner';
                spinner.setAttribute('aria-hidden', 'true');
                if (button.tagName.toLowerCase() === 'input') {
                    originalValue = button.value;
                    button.value = action.label;
                    button.insertAdjacentElement('beforebegin', spinner);
                } else {
                    button.prepend(spinner);
                }
                status = document.createElement('div');
                status.className = 'sms-submit-status';
                status.setAttribute('role', 'status');
                status.textContent = action.label;
                button.insertAdjacentElement('afterend', status);
                window.setTimeout(function () {
                    if (form.dataset.smsSubmitting && status) status.textContent = @json(trans('plugins/sms::sms.still_waiting'));
                }, 20000);
            }
            button.addEventListener('click', function (event) {
                if (status) { event.preventDefault(); return; }
                show();
                // Client-side validation can cancel a click without a submit event.
                window.setTimeout(function () {
                    if (!form.dataset.smsSubmitting && status) reset();
                }, 8000);
            }, true);
            form.addEventListener('submit', function (event) {
                if (form.dataset.smsSubmitting) { event.preventDefault(); return; }
                show();
                form.dataset.smsSubmitting = '1';
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            }, true);
            form.addEventListener('invalid', reset, true);
            if (window.jQuery) window.jQuery(form).on('invalid-form', reset);
            window.addEventListener('pageshow', reset);
            if (window.jQuery) {
                window.jQuery(document).ajaxComplete(function (_event, _xhr, settings) {
                    try {
                        if (new URL(settings.url, window.location.href).pathname === actionPath) reset();
                    } catch (error) { /* Keep fallback timeout. */ }
                });
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
</script>
