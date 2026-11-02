<div class="mb-3 sms-otp-field" data-sms-otp>
    <label class="form-label" for="otp">{{ trans('plugins/sms::sms.otp_label') }}</label>
    <input
        id="otp"
        class="form-control sms-otp-fallback @if ($errors->has('otp')) is-invalid @endif"
        type="text"
        name="otp"
        inputmode="numeric"
        pattern="[0-9]{6}"
        minlength="6"
        maxlength="6"
        autocomplete="one-time-code"
        placeholder="{{ trans('plugins/sms::sms.enter_otp') }}"
        required
        data-sms-otp-value
    >
    <div class="sms-otp-digits" role="group" aria-label="{{ trans('plugins/sms::sms.otp_label') }}" data-sms-otp-digits hidden>
        @for ($position = 1; $position <= 6; $position++)
            <input
                class="sms-otp-digit"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                aria-label="{{ trans('plugins/sms::sms.otp_digit', ['number' => $position]) }}"
                @if ($position === 1) autocomplete="one-time-code" @else autocomplete="off" @endif
                data-sms-otp-digit
            >
        @endfor
    </div>
    @if ($errors->has('otp'))
        <div class="text-danger small mt-2">{{ $errors->first('otp') }}</div>
    @endif
    <div class="text-danger small mt-2" data-sms-otp-error role="alert" hidden>{{ trans('plugins/sms::sms.otp_six_digits_required') }}</div>
</div>

<style>
    .sms-otp-digits[hidden], [data-sms-otp-error][hidden] { display: none !important; }
    .sms-otp-digits { display: flex; justify-content: space-between; gap: clamp(.35rem, 1.6vw, .75rem); max-width: 29rem; margin-inline: auto; }
    .sms-otp-digit { width: min(15%, 4.25rem); aspect-ratio: 1; border: 1px solid #c9d0d8; border-radius: .7rem; background: #fff; color: #202428; text-align: center; font-size: clamp(1.25rem, 3vw, 1.8rem); font-weight: 600; line-height: 1; outline: none; transition: border-color .15s, box-shadow .15s; }
    .sms-otp-digit:focus { border-color: #2582db; box-shadow: 0 0 0 .2rem rgba(37, 130, 219, .16); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-sms-otp]').forEach(function (widget) {
        var valueField = widget.querySelector('[data-sms-otp-value]');
        var group = widget.querySelector('[data-sms-otp-digits]');
        var boxes = Array.from(widget.querySelectorAll('[data-sms-otp-digit]'));
        var error = widget.querySelector('[data-sms-otp-error]');
        if (!valueField || boxes.length !== 6) return;

        valueField.type = 'hidden';
        valueField.required = false;
        group.hidden = false;
        widget.querySelector('label').addEventListener('click', function () { boxes[0].focus(); });

        function sync() {
            valueField.value = boxes.map(function (box) { return box.value; }).join('');
            error.hidden = true;
        }

        function fill(start, text) {
            var digits = String(text).replace(/\D/g, '');
            if (!digits) return;
            if (digits.length >= 6) {
                start = 0;
                boxes.forEach(function (box) { box.value = ''; });
            }
            digits = digits.slice(0, 6 - start);
            for (var i = 0; i < digits.length; i++) boxes[start + i].value = digits[i];
            sync();
            boxes[Math.min(start + digits.length, 5)].focus();
        }

        boxes.forEach(function (box, index) {
            box.addEventListener('focus', function () { box.select(); });
            box.addEventListener('input', function () {
                var digits = box.value.replace(/\D/g, '');
                if (digits.length > 1) return fill(index, digits);
                box.value = digits;
                sync();
                if (digits && index < 5) boxes[index + 1].focus();
            });
            box.addEventListener('paste', function (event) {
                event.preventDefault();
                fill(index, event.clipboardData.getData('text'));
            });
            box.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace' && !box.value && index > 0) {
                    boxes[index - 1].value = '';
                    boxes[index - 1].focus();
                    sync();
                } else if (event.key === 'ArrowLeft' && index > 0) {
                    boxes[index - 1].focus();
                } else if (event.key === 'ArrowRight' && index < 5) {
                    boxes[index + 1].focus();
                }
            });
        });

        widget.closest('form').addEventListener('submit', function (event) {
            sync();
            if (!/^[0-9]{6}$/.test(valueField.value)) {
                event.preventDefault();
                error.hidden = false;
                (boxes.find(function (box) { return !box.value; }) || boxes[0]).focus();
            }
        });
    });
});
</script>
