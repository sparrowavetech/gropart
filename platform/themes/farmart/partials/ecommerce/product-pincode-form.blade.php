@php
    $fromZipcode  = EcommerceHelper::isZipCodeEnabled() ? get_ecommerce_setting('store_zip_code') : '313001';
@endphp
<div class="pincode-checker">
    <label class="label-pincode"><strong>{{ __('Check Delivery ') }}:</strong></label>
    <div class="pincode-checker-box input-group">
        <input class="input-text form-control" type="text" minlength="6" maxlength="6" id="pincodetext" name="pincode" title="Pincode" tabindex="0" placeholder="Enter Pincode" required />
        @if (is_plugin_active('marketplace'))
            <button type="button" name="apply" value="1" onclick="checkPincode('{{$product->store->zip_code}}')" class="btn btn-primary btn-black">{{ __('Check') }}</button>
        @else
            <button type="button" name="apply" value="1" onclick="checkPincode('{{ $fromZipcode }}')" class="btn btn-primary btn-black">{{ __('Check') }}</button>
        @endif
    </div>
    <p class="picodetext mt-2" style="display: none;"></p>
</div>
