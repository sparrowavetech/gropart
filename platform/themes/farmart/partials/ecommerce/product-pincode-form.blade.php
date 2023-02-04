<div class="quantity">
    <label class="label-quantity">{{ __('Check Delivery ') }}:</label>
    <div class="qty-box">
    <input class="input-text "
            type="text"
            minlength="6"
            maxlength="6"
            id="pincodetext"
            name="pincode"
            title="Pincode"
            tabindex="0" required>
        <button type="button" name="apply" value="1" onclick="checkPincode('{{$product->store->zip_code}}')" class="btn btn-primary btn-black mb-2 " >
                        <span class=" ms-2">{{ __('Check') }}</span>
                    </button>
    </div>
    <p class="picodetext" style="display: none;"></p>
</div>
