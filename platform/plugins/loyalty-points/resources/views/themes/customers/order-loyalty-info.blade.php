@php
    $loyaltyPoints = $order->loyaltyPoints;
    $earnTransaction = null;
    $redeemTransaction = null;

    if ($loyaltyPoints) {
        $earnTransaction = \Botble\LoyaltyPoints\Models\PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', 'earn')
            ->first();

        $redeemTransaction = \Botble\LoyaltyPoints\Models\PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', 'redeem')
            ->first();
    }

    $hasPointsToShow = $loyaltyPoints && (
        $loyaltyPoints->points_redeemed > 0 ||
        $loyaltyPoints->points_to_earn > 0 ||
        $earnTransaction
    );
@endphp

@if($hasPointsToShow)
    <div class="bb-customer-card mt-3 order-loyalty-detail">
        <div class="bb-customer-card-header d-flex align-items-center gap-2">
            <x-core::icon name="ti ti-award" class="loyalty-header-icon" />
            <h5 class="bb-customer-card-title mb-0">
                {{ trans('plugins/loyalty-points::loyalty-points.thank_you.loyalty_points') }}
            </h5>
        </div>
        <div class="bb-customer-card-body p-0">
            <div class="loyalty-detail-list">
                {{-- Points Redeemed --}}
                @if($loyaltyPoints->points_redeemed > 0)
                    <div class="loyalty-detail-item">
                        <div class="loyalty-detail-icon loyalty-icon-redeem">
                            <x-core::icon name="ti ti-discount-2" />
                        </div>
                        <div class="loyalty-detail-content">
                            <div class="loyalty-detail-label">
                                {{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_redeemed') }}
                            </div>
                            <div class="loyalty-detail-sublabel">
                                {{ trans('plugins/loyalty-points::loyalty-points.thank_you.saved') }}:
                                <span class="text-success">{{ format_price($loyaltyPoints->discount_amount) }}</span>
                                @if($redeemTransaction)
                                    <span class="loyalty-detail-date">
                                        • {{ $redeemTransaction->created_at->translatedFormat('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="loyalty-detail-points text-danger">
                            -{{ number_format($loyaltyPoints->points_redeemed) }}
                        </div>
                    </div>
                @endif

                {{-- Points Earned or Pending --}}
                @if($earnTransaction)
                    {{-- Points already earned --}}
                    <div class="loyalty-detail-item">
                        <div class="loyalty-detail-icon loyalty-icon-earn">
                            <x-core::icon name="ti ti-coin" />
                        </div>
                        <div class="loyalty-detail-content">
                            <div class="loyalty-detail-label">
                                {{ trans('plugins/loyalty-points::loyalty-points.order_detail.points_earned') }}
                            </div>
                            <div class="loyalty-detail-sublabel">
                                <span class="loyalty-detail-date">
                                    {{ $earnTransaction->created_at->translatedFormat('M d, Y \a\t h:i A') }}
                                </span>
                            </div>
                        </div>
                        <div class="loyalty-detail-points text-success">
                            +{{ number_format($earnTransaction->points) }}
                        </div>
                    </div>
                @elseif($loyaltyPoints->points_to_earn > 0)
                    {{-- Points pending (order not yet completed) --}}
                    <div class="loyalty-detail-item">
                        <div class="loyalty-detail-icon loyalty-icon-pending">
                            <x-core::icon name="ti ti-clock" />
                        </div>
                        <div class="loyalty-detail-content">
                            <div class="loyalty-detail-label">
                                {{ trans('plugins/loyalty-points::loyalty-points.order_detail.points_pending') }}
                            </div>
                            <div class="loyalty-detail-sublabel">
                                {{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_earn_notice') }}
                            </div>
                        </div>
                        <div class="loyalty-detail-points text-warning">
                            +{{ number_format($loyaltyPoints->points_to_earn) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
