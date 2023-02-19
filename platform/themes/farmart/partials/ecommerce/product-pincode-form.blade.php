<div class="pincode-checker">
    <label class="label-pincode"><strong>{{ __('Check Delivery ') }}:</strong></label>
    <div class="pincode-checker-box input-group">
        <input class="input-text form-control" type="text" minlength="6" maxlength="6" id="pincodetext" name="pincode" title="Pincode" tabindex="0" placeholder="Enter Pincode" required />
        <button type="button" name="apply" value="1" onclick="checkPincode('{{$product->store->zip_code}}')" class="btn btn-primary btn-black">{{ __('Check') }}</button>
    </div>
    <p class="picodetext mt-2" style="display: none;"></p>
</div>
