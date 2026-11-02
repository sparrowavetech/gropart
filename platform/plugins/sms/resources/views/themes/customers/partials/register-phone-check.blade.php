<style>
    .sms-register-phone-error { display: none; color: #dc3545; font-size: .875em; margin-top: .35rem; }
    .sms-register-phone-error.is-visible { display: block; }
</style>
<script>
(function () {
    function initialize() {
        var registerPath = @json(parse_url(route('customer.register.post'), PHP_URL_PATH));
        var form = Array.prototype.find.call(document.querySelectorAll('form[action]'), function (candidate) {
            try { return new URL(candidate.action, window.location.href).pathname === registerPath; }
            catch (ignored) { return false; }
        });
        if (!form) return;
        var display = form.querySelector('[name="phone_display"]');
        var phone = form.querySelector('[name="phone"]');
        var field = display || phone;
        if (!field) return;

        var error = document.createElement('div');
        error.className = 'sms-register-phone-error';
        error.id = 'sms-register-phone-error';
        error.setAttribute('role', 'alert');
        error.textContent = @json(trans('plugins/sms::sms.registration_phone_taken'));
        (field.closest('.form-group') || field.parentElement).appendChild(error);
        field.setAttribute('aria-describedby', error.id);

        var sequence = 0;
        function clear() {
            sequence++;
            field.setCustomValidity('');
            field.classList.remove('is-invalid');
            error.classList.remove('is-visible');
        }
        function check() {
            var full = phone ? phone.value.trim() : '';
            var shown = display ? display.value.trim() : '';
            if ((full || shown).replace(/\D/g, '').length < 7) return;
            var current = ++sequence;
            var data = new FormData();
            data.append('_token', form.querySelector('[name="_token"]').value);
            data.append('phone', full);
            data.append('phone_display', shown);
            fetch(@json(route('customer.register.phone-availability')), {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (response) {
                if (!response.ok) throw new Error('check unavailable');
                return response.json();
            }).then(function (result) {
                if (current !== sequence) return;
                if (!result.available) {
                    field.setCustomValidity(error.textContent);
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                    error.classList.add('is-visible');
                }
            }).catch(function () { /* Registration still validates on the server. */ });
        }
        field.addEventListener('input', clear);
        field.addEventListener('countrychange', function () { clear(); window.setTimeout(check, 0); });
        field.addEventListener('blur', function () { window.setTimeout(check, 0); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
</script>
