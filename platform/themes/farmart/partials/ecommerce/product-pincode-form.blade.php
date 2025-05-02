@php
    $fromZipcode  = EcommerceHelper::isZipCodeEnabled() ? get_ecommerce_setting('store_zip_code') : '313001';
@endphp
<div class="pincode-checker mt-4">
    <label class="label-pincode mb-2"><strong>{{ __('Check Delivery') }}:</strong></label>
    <div class="pincode-checker-box input-group">
        <input class="input-text form-control" type="text" minlength="6" maxlength="6" id="topincode" name="pincode" title="Pincode" tabindex="0" placeholder="Enter Pincode" required />
        <input type="hidden" name="product_weight" id="product_weight" class="product_weight" value="{{ $product->weight }}" />
        @if (is_plugin_active('marketplace'))
            <button type="button" name="apply" value="1" onclick="checkPincode('{{$product->store->zip_code}}')" class="btn btn-primary btn-black">{{ __('Check') }}</button>
        @else
            <button type="button" name="apply" value="1" onclick="checkPincode('{{ $fromZipcode }}')" class="btn btn-primary btn-black">{{ __('Check') }}</button>
        @endif
    </div>
    <p class="pincodetext mt-2" style="display: none;"></p>
</div>
