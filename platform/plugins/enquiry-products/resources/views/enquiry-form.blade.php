<section class="ep-shell ep-request-page">
    <h1>{{ __('plugins/enquiry-products::messages.enquiry_form') }}</h1>
    <div class="ep-request-layout">
        <article class="ep-request-product">
            <a href="{{ route('public.enquiry-products.show', $product->slugable->key) }}"><img src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}"></a>
            <div><h2>{{ $product->name }}</h2>
                @if($product->brand)<p>{{ __('plugins/enquiry-products::messages.brand') }}: <span>{{ $product->brand->name }}</span></p>@endif
                @include('plugins/enquiry-products::partials.seller')
            </div>
        </article>
        <div>            @if($errors->any())<div class="ep-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form class="ep-form ep-request-form" method="post" action="{{ route('public.enquiry-products.submit', $product->id) }}" enctype="multipart/form-data">
                @csrf <input type="hidden" name="token" value="{{ old('token', $token) }}">
                <div class="ep-trap" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <label>{{ __('plugins/enquiry-products::messages.full_name_f11b3') }} *<input name="name" required minlength="2" maxlength="120" value="{{ old('name', auth('customer')->user()?->name) }}" autocomplete="name"></label>
                <label>{{ __('plugins/enquiry-products::messages.email') }} *<input type="email" name="email" required maxlength="80" value="{{ old('email', auth('customer')->user()?->email) }}" autocomplete="email"></label>
                <label for="ep-phone">{{ __('plugins/enquiry-products::messages.phone') }} *
                    <input type="tel" id="ep-phone" name="phone_display" class="js-phone-number-mask" data-country-code-selection="true" required maxlength="25" value="{{ old('phone_display', old('phone')) }}" autocomplete="tel">
                    <input type="hidden" name="phone" class="js-phone-number-full" data-phone-field="phone_display" id="ep-phone-full" value="{{ old('phone') }}">
                </label>
                <div class="ep-location-row">
                    @if($countries->count() > 1)
                        <label>{{ __('plugins/enquiry-products::messages.country') }}<select name="country" id="ep-country"><option value="">{{ __('plugins/enquiry-products::messages.select_country') }}</option>@foreach($countries as $country)<option value="{{ $country->id }}" @selected(old('country') == $country->id)>{{ $country->name }}</option>@endforeach</select></label>
                    @elseif($countries->count() === 1)
                        <input type="hidden" name="country" id="ep-country" value="{{ $countries->first()->id }}">
                    @endif
                    @if($countries->isNotEmpty())
                        <label>{{ __('plugins/enquiry-products::messages.state') }}<select name="state" id="ep-state" data-selected="{{ old('state') }}"><option value="">{{ __('plugins/enquiry-products::messages.select_state') }}</option></select></label>
                        <label>{{ __('plugins/enquiry-products::messages.city') }}<select name="city" id="ep-city" data-selected="{{ old('city') }}"><option value="">{{ __('plugins/enquiry-products::messages.select_city') }}</option></select></label>
                    @endif
                    <label>{{ __('plugins/enquiry-products::messages.pin_postal_code') }}<input name="zip_code" maxlength="20" value="{{ old('zip_code') }}" autocomplete="postal-code"></label>
                </div>
                <label>{{ __('plugins/enquiry-products::messages.address') }} *<textarea name="address" required maxlength="1000" autocomplete="street-address">{{ old('address') }}</textarea></label>
                <label>{{ __('plugins/enquiry-products::messages.requirement_details') }}<textarea name="description" rows="4" maxlength="5000">{{ old('description') }}</textarea></label>
                <label>{{ __('plugins/enquiry-products::messages.attachment_jpg_png_or_pdf_up_to_5_mb') }}<input type="file" name="attachment" accept="image/jpeg,image/png,application/pdf"></label>
                @if(is_plugin_active('captcha') && \Botble\Captcha\Facades\Captcha::reCaptchaEnabled())
                    <div class="ep-captcha">{!! \Botble\Captcha\Facades\Captcha::display() !!}</div>
                @endif
                <button class="ep-button" type="submit">{{ __('plugins/enquiry-products::messages.send_requirement') }}</button>
            </form>
</div>
    </div>
</section>
@include('core/base::forms.fields.phone-number-script')
@if($countries->isNotEmpty())
<script>
(() => {
    const country = document.getElementById('ep-country'), state = document.getElementById('ep-state'), city = document.getElementById('ep-city');
    const load = async (target, endpoint, key, value) => {
        target.length = 1;
        if (!value) return;
        try {
            const url = new URL(endpoint, location.origin); url.searchParams.set(key, value);
            const response = await fetch(url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const body = await response.json();
            (body.data || []).filter(item => Number(item.id) > 0).forEach(item => target.add(new Option(item.name, item.id, false, String(item.id) === target.dataset.selected)));
        } catch (_) { target.length = 1; }
    };
    country.addEventListener('change', async () => { city.length = 1; await load(state, @json(route('ajax.states-by-country')), 'country_id', country.value); });
    state.addEventListener('change', () => load(city, @json(route('ajax.cities-by-state')), 'state_id', state.value));
    if (country.value) load(state, @json(route('ajax.states-by-country')), 'country_id', country.value).then(() => state.value && load(city, @json(route('ajax.cities-by-state')), 'state_id', state.value));
})();
</script>
@endif
<div class="ep-submit-overlay" id="ep-submit-overlay" hidden role="status" aria-live="polite" aria-atomic="true">
    <span class="ep-submit-spinner" aria-hidden="true"></span>
    <span>{{ __('plugins/enquiry-products::messages.submitting_your_enquiry_please_wait') }}</span>
</div>
<script>
(() => {
    const form = document.querySelector('.ep-request-form');
    const overlay = document.getElementById('ep-submit-overlay');
    const button = form.querySelector('button[type="submit"]');
    const originalLabel = button.textContent;
    let submitting = false;
    const reset = () => {
        submitting = false;
        overlay.hidden = true;
        form.removeAttribute('aria-busy');
        button.disabled = false;
        button.textContent = originalLabel;
    };
    form.addEventListener('submit', event => {
        if (submitting) { event.preventDefault(); return; }
        const phone = document.getElementById('ep-phone');
        const full = document.getElementById('ep-phone-full');
        const instance = window.intlTelInput?.getInstance?.(phone);
        if (instance) full.value = instance.getNumber();
        else full.value = phone.value;
        const valid = instance && window.intlTelInputUtils ? instance.isValidNumber() : /^\+?[1-9][0-9 ()-]{7,24}$/.test(full.value);
        phone.setCustomValidity(valid ? '' : @json(__('plugins/enquiry-products::messages.enter_a_valid_phone_number_with_country_code')));
        if (event.defaultPrevented || !form.checkValidity()) { event.preventDefault(); form.reportValidity(); return; }
        submitting = true;
        overlay.hidden = false;
        form.setAttribute('aria-busy', 'true');
        button.disabled = true;
        button.textContent = @json(__('plugins/enquiry-products::messages.submitting'));
        // Keep native form submission and server validation/redirects intact.
        queueMicrotask(() => { if (event.defaultPrevented) reset(); });
    });
    document.getElementById('ep-phone').addEventListener('input', event => event.target.setCustomValidity(''));
    document.getElementById('ep-phone').addEventListener('countrychange', event => event.target.setCustomValidity(''));
    window.addEventListener('pageshow', reset);
})();
</script>
