@php
    $customer = auth('customer')->user();
    $loyaltyService = app(\Botble\LoyaltyPoints\Services\LoyaltyPointService::class);
    $currentPoints = $customer ? $loyaltyService->getCustomerBalance($customer->id) : 0;
    $appliedPoints = session('applied_loyalty_points', 0);
    $loyaltyHelper = app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class);
    $minRedeemable = $loyaltyHelper->getMinRedeemablePoints();
    $maxRedeemable = $loyaltyHelper->getMaxRedeemablePoints();

    $maxPointsToRedeem = min($currentPoints, $maxRedeemable > 0 ? $maxRedeemable : $currentPoints);
    $maxDiscountAmount = $loyaltyHelper->calculateDiscountFromPoints($maxPointsToRedeem);

    $balance = $customer->pointBalance;
    $level = $balance ? $balance->level : null;

    $presetOptions = [];
    foreach ([25, 50, 100] as $percent) {
        $points = $percent === 100
            ? $maxPointsToRedeem
            : (int) floor($maxPointsToRedeem * ($percent / 100));

        if ($points >= $minRedeemable && $points > 0) {
            $presetOptions[$percent] = [
                'points' => $points,
                'discount' => $loyaltyHelper->calculateDiscountFromPoints($points),
            ];
        }
    }

    $cartSubtotal = 0;
    if (class_exists('Cart')) {
        $cartSubtotal = \Cart::instance('cart')->rawSubTotal();
    }
    $basePointsToEarn = $loyaltyHelper->calculatePointsFromAmount($cartSubtotal);
    $tierMultiplier = ($level && $level->earning_rate > 1) ? $level->earning_rate : 1;
    $pointsToEarn = (int) floor($basePointsToEarn * $tierMultiplier);
@endphp

@if($customer && $loyaltyHelper->isEnabled())
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/loyalty-points/css/loyalty-checkout.css') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}">

    <div class="loyalty-points-compact mb-3"
         role="region"
         aria-label="{{ trans('plugins/loyalty-points::loyalty-points.checkout.redeem_points') }}">
        <div id="loyalty-status" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

        @if($appliedPoints > 0)
            <div class="loyalty-applied">
                <div class="loyalty-applied-content">
                    <x-core::icon name="ti ti-circle-check" />
                    <span>{{ trans('plugins/loyalty-points::loyalty-points.customer.discount_applied', [
                        'amount' => format_price($loyaltyHelper->calculateDiscountFromPoints($appliedPoints)),
                        'points' => number_format($appliedPoints)
                    ]) }}</span>
                </div>
                <button
                    type="button"
                    class="btn-remove"
                    id="remove-loyalty-points-btn"
                    data-url="{{ route('customer.loyalty-points.remove') }}"
                    aria-label="{{ trans('plugins/loyalty-points::loyalty-points.checkout.remove_points') }}"
                >
                    <x-core::icon name="ti ti-x" />
                </button>
            </div>
        @elseif($currentPoints > 0 && $currentPoints >= $minRedeemable)
            <div class="loyalty-suggestion">
                <div class="loyalty-header d-flex align-items-center gap-2 mb-2">
                    <x-core::icon name="ti ti-gift" class="text-primary" />
                    <span class="fw-semibold">{{ trans('plugins/loyalty-points::loyalty-points.customer.points_available', ['points' => number_format($currentPoints)]) }}</span>
                </div>

                @if(count($presetOptions) > 1)
                    <div class="loyalty-redeem-prompt text-muted small mb-2">
                        {{ trans('plugins/loyalty-points::loyalty-points.checkout.redeem_prompt') }}
                    </div>

                    <div class="loyalty-preset-buttons">
                        @foreach($presetOptions as $percent => $option)
                            <button
                                type="button"
                                class="loyalty-preset-btn"
                                data-url="{{ route('customer.loyalty-points.apply') }}"
                                data-points="{{ $option['points'] }}"
                                aria-label="{{ trans('plugins/loyalty-points::loyalty-points.checkout.use_percent', ['percent' => $percent]) }} - {{ number_format($option['points']) }} points for {{ format_price($option['discount']) }} discount"
                            >
                                <span class="preset-label">
                                    @if($percent === 100)
                                        {{ trans('plugins/loyalty-points::loyalty-points.checkout.use_all_points') }}
                                    @else
                                        {{ trans('plugins/loyalty-points::loyalty-points.checkout.use_percent', ['percent' => $percent]) }}
                                    @endif
                                </span>
                                <span class="preset-points">{{ number_format($option['points']) }} {{ trans('plugins/loyalty-points::loyalty-points.checkout.pts') }}</span>
                                <span class="preset-discount">-{{ format_price($option['discount']) }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="loyalty-redeem-info mt-1 mb-2">
                        {{ trans('plugins/loyalty-points::loyalty-points.customer.redeem_info', [
                            'points' => number_format($maxPointsToRedeem),
                            'amount' => format_price($maxDiscountAmount)
                        ]) }}
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm w-100"
                        id="quick-apply-loyalty-points-btn"
                        data-url="{{ route('customer.loyalty-points.apply') }}"
                        data-points="{{ $maxPointsToRedeem }}"
                    >
                        {{ trans('plugins/loyalty-points::loyalty-points.checkout.apply_points') }}
                    </button>
                @endif
            </div>
        @else
            {{-- Customer has no points or below minimum - show earn-only info --}}
            <div class="loyalty-no-points">
                <div class="loyalty-header d-flex align-items-center gap-2">
                    <x-core::icon name="ti ti-gift" class="text-muted" />
                    <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.checkout.no_points_available') }}</span>
                </div>
            </div>
        @endif

        @if($pointsToEarn > 0)
            <div class="loyalty-earn-preview mt-2">
                <x-core::icon name="ti ti-star" class="text-warning" />
                <span>{{ trans('plugins/loyalty-points::loyalty-points.checkout.points_to_earn', ['points' => number_format($pointsToEarn)]) }}</span>
            </div>
        @endif

        @if($level && $level->earning_rate > 1)
            <div class="loyalty-tier-boost mt-2">
                <x-core::icon name="ti ti-crown" class="text-success me-2" />
                <small class="text-success fw-bold">
                    {{ trans('plugins/loyalty-points::loyalty-points.checkout.earning_boost', ['name' => $level->name, 'rate' => $level->earning_rate]) }}
                </small>
            </div>
        @endif
    </div>

    @if($currentPoints > 0 && $currentPoints >= $minRedeemable)
        <script>
            window.loyaltyPointsTranslations = {
                pointsApplied: '{{ trans('plugins/loyalty-points::loyalty-points.js.points_applied') }}',
                pointsRemoved: '{{ trans('plugins/loyalty-points::loyalty-points.js.points_removed') }}',
                invalidPointsAmount: '{{ trans('plugins/loyalty-points::loyalty-points.js.invalid_points_amount') }}',
                genericError: '{{ trans('plugins/loyalty-points::loyalty-points.js.generic_error') }}'
            };
        </script>
        <script src="{{ asset('vendor/core/plugins/loyalty-points/js/loyalty-checkout.js') }}?v={{ \Botble\LoyaltyPoints\Plugin::ASSETS_VERSION }}"></script>
    @endif
@endif
