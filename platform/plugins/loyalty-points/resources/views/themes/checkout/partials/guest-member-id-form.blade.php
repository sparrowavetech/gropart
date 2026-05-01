@php
    $loyaltyHelper = app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class);
    $loyaltyService = app(\Botble\LoyaltyPoints\Services\LoyaltyPointService::class);
    $validatedMemberId = session('loyalty_guest_member_id');
    $validatedCustomer = session('loyalty_guest_customer');

    $customerBalance = 0;
    $pointsToEarn = 0;

    if ($validatedCustomer) {
        $customerBalance = $loyaltyService->getCustomerBalance($validatedCustomer['id']);
        $cartTotal = (float) \Botble\Ecommerce\Facades\Cart::instance('cart')->rawSubTotal();
        $pointsToEarn = $loyaltyHelper->calculatePointsFromAmount($cartTotal);
    }
@endphp

@if($loyaltyHelper->isEnabled())
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/loyalty-points/css/loyalty-checkout.css') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}">

    <div class="loyalty-points-compact mb-3"
         role="region"
         aria-label="{{ trans('plugins/loyalty-points::loyalty-points.member_id.label') }}">
        <div id="loyalty-member-status" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

        @if($validatedMemberId && $validatedCustomer)
            <div class="loyalty-applied">
                <div class="loyalty-applied-content">
                    <x-core::icon name="ti ti-circle-check" />
                    <div>
                        <span class="d-block">{{ trans('plugins/loyalty-points::loyalty-points.member_id.member_found') }}: <strong>{{ $validatedCustomer['name'] }}</strong></span>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="small" style="background: rgba(32, 107, 196, 0.1); color: #206bc4; padding: 4px 8px; border-radius: 4px; display: inline-flex; align-items: center;">
                                <x-core::icon name="ti ti-wallet" class="me-1" style="width: 14px; height: 14px;" />
                                {{ trans('plugins/loyalty-points::loyalty-points.member_id.current_balance') }}: <strong class="ms-1">{{ number_format($customerBalance) }}</strong>&nbsp;{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}
                            </span>
                            @if($pointsToEarn > 0)
                                <span class="small" style="background: rgba(47, 179, 68, 0.1); color: #2fb344; padding: 4px 8px; border-radius: 4px; display: inline-flex; align-items: center;">
                                    <x-core::icon name="ti ti-plus" class="me-1" style="width: 14px; height: 14px;" />
                                    {{ trans('plugins/loyalty-points::loyalty-points.member_id.will_earn') }}: <strong class="ms-1">+{{ number_format($pointsToEarn) }}</strong>&nbsp;{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn-remove"
                    id="remove-member-id-btn"
                    data-url="{{ route('public.loyalty-points.remove-member-id') }}"
                    aria-label="{{ trans('plugins/loyalty-points::loyalty-points.member_id.remove') }}"
                >
                    <x-core::icon name="ti ti-x" />
                </button>
            </div>
        @else
            <div class="loyalty-suggestion">
                <div class="loyalty-header d-flex align-items-center gap-2 mb-2">
                    <x-core::icon name="ti ti-gift" class="text-primary" />
                    <span class="fw-semibold">{{ trans('plugins/loyalty-points::loyalty-points.member_id.earn_points_title') }}</span>
                </div>

                <div class="loyalty-redeem-prompt text-muted small mb-3">
                    {{ trans('plugins/loyalty-points::loyalty-points.member_id.help_guest') }}
                </div>

                <div class="loyalty-member-input-wrapper">
                    <div class="d-flex gap-2">
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            id="loyalty_member_id"
                            name="loyalty_member_id"
                            placeholder="{{ trans('plugins/loyalty-points::loyalty-points.member_id.placeholder_phone_or_id') }}"
                            aria-label="{{ trans('plugins/loyalty-points::loyalty-points.member_id.label') }}"
                            autocomplete="off"
                            style="flex: 1; min-width: 0;"
                        >
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            id="validate-member-id-btn"
                            data-url="{{ route('public.loyalty-points.validate-member-id') }}"
                            style="white-space: nowrap;"
                        >
                            {{ trans('plugins/loyalty-points::loyalty-points.member_id.lookup') }}
                        </button>
                    </div>

                    <div id="member-id-result" class="mt-2" style="display: none;"></div>
                </div>

                <div class="loyalty-earn-preview mt-3">
                    <x-core::icon name="ti ti-info-circle" />
                    <span>{{ trans('plugins/loyalty-points::loyalty-points.member_id.earn_info') }}</span>
                </div>
            </div>
        @endif
    </div>

    <script>
        window.loyaltyMemberIdTranslations = {
            validating: '{{ trans('plugins/loyalty-points::loyalty-points.member_id.validating') }}',
            validate: '{{ trans('plugins/loyalty-points::loyalty-points.member_id.lookup') }}',
            empty: '{{ trans('plugins/loyalty-points::loyalty-points.member_id.empty') }}',
            genericError: '{{ trans('plugins/loyalty-points::loyalty-points.js.generic_error') }}'
        };
    </script>
    <script src="{{ asset('vendor/core/plugins/loyalty-points/js/loyalty-guest-checkout.js') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}"></script>
@endif
