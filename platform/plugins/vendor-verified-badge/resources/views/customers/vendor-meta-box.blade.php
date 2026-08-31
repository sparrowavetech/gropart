<div class="vendor-verification-card">
    <div class="mb-3">
        <label class="form-label fw-bold" for="vendor-shop-category">
            <x-core::icon name="ti ti-building-store" class="me-1 text-primary" />
            {{ trans('plugins/vendor-verified-badge::vendor-badge.shop_type') }}
        </label>
        <select class="form-select form-control" id="vendor-shop-category" name="shop_category">
            <option value="">{{ __('Select Shop Type') }}</option>
            @foreach(\SparroWave\VendorVerifiedBadge\Enums\ShopTypeEnum::labels() as $value => $label)
                <option value="{{ $value }}" @selected($store->shop_category === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted mt-1 small">
            {{ __('Select the vendor category (Manufacturer, Wholesaler, or Retailer) to show relevant badge on store and products.') }}
        </div>
    </div>

    <div class="hr my-3"></div>

    <div class="mb-2">
        <div class="d-flex align-items-center justify-content-between">
            <label class="form-label fw-bold mb-0" for="vendor-is-verified-toggle">
                <img src="{{ asset('vendor/core/plugins/vendor-verified-badge/images/verified.png') }}" alt="Verified" style="height: 18px; width: auto; vertical-align: middle; margin-right: 4px;" />
                {{ trans('plugins/vendor-verified-badge::vendor-badge.is_verified_label') }}
            </label>
            <div class="form-check form-switch m-0">
                <input type="hidden" name="is_verified" value="0">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="vendor-is-verified-toggle" 
                    name="is_verified" 
                    value="1" 
                    @checked($store->is_verified)
                    style="cursor: pointer; width: 2.5em; height: 1.3em;"
                >
            </div>
        </div>
        
        <div class="form-text text-muted mt-2 small">
            {{ __('Turn ON to award the official verified seller badge. It will display a green tick with GST/Business authentication tooltip.') }}
        </div>

        @if($store->is_verified && $store->verified_at)
            <div class="alert alert-success d-flex align-items-center py-2 px-3 mt-3 mb-0" style="font-size: 12px; border-radius: 6px;">
                <x-core::icon name="ti ti-shield-check" class="me-2 text-success" />
                <div>
                    <strong>{{ __('Verified on:') }}</strong> {{ $store->verified_at->format('M d, Y H:i') }}
                    @if($store->verifiedBy)
                        <br><span class="text-muted">{{ __('By:') }} {{ $store->verifiedBy->name }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
