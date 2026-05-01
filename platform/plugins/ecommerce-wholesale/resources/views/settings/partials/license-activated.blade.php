<div data-deactivate-url="{{ route('wholesale.license.deactivate') }}">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-green text-green-fg">
                <x-core::icon name="ti ti-circle-check" class="me-1" />
                {{ trans('plugins/ecommerce-wholesale::wholesale.license.activated_title') }}
            </span>

            @if(isset($licenseData['purchase_code']))
                @php
                    $purchaseCode = $licenseData['purchase_code'];

                    if (\Botble\Base\Facades\BaseHelper::hasDemoModeEnabled()) {
                        $purchaseCode = 'DEMO_PURCHASE_CODE';
                    }
                @endphp
                <code class="small" id="purchase-code-display">{{ Str::mask($purchaseCode, '*', 4) ?? 'N/A' }}</code>

                <button type="button" class="btn btn-sm btn-link p-0" id="toggle-purchase-code"
                        data-full-code="{{ $purchaseCode }}"
                        data-masked-code="{{ Str::mask($purchaseCode, '*', 4) }}"
                        title="{{ trans('plugins/ecommerce-wholesale::wholesale.license.toggle_visibility') }}">
                    <span id="show-icon"><x-core::icon name="ti ti-eye" /></span>
                    <span id="hide-icon" style="display: none;"><x-core::icon name="ti ti-eye-off" /></span>
                </button>
            @endif

            <span class="text-muted small">{{ $licenseData['activated_at'] }}</span>
        </div>

        <button type="button" class="btn btn-outline-danger btn-sm" id="deactivate-license-btn">
            {{ trans('plugins/ecommerce-wholesale::wholesale.license.deactivate') }}
        </button>
    </div>
</div>
