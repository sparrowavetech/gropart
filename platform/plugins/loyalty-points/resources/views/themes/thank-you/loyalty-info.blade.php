<div class="order-loyalty-info mt-3 mt-md-4 mb-0 mb-sm-4">
    <div class="loyalty-info-card">
        <div class="loyalty-info-header">
            <x-core::icon name="ti ti-gift" />
            <span>{{ trans('plugins/loyalty-points::loyalty-points.thank_you.loyalty_points') }}</span>
        </div>

        <div class="loyalty-info-body">
            @if($pointsRedeemed > 0)
                <div class="loyalty-info-row loyalty-redeemed">
                    <div class="loyalty-info-label">
                        <x-core::icon name="ti ti-discount-2" />
                        <span>{{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_redeemed') }}</span>
                    </div>
                    <div class="loyalty-info-value text-danger">
                        <span class="points-amount">-{{ number_format($pointsRedeemed) }} {{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</span>
                    </div>
                </div>
            @endif

            @if($pointsToEarn > 0)
                <div class="loyalty-info-row loyalty-earned">
                    <div class="loyalty-info-label">
                        <x-core::icon name="ti ti-star" />
                        <span>{{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_to_earn') }}</span>
                    </div>
                    <div class="loyalty-info-value text-success">
                        <span class="points-amount">+{{ number_format($pointsToEarn) }} {{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</span>
                    </div>
                </div>

                <div class="loyalty-info-notice">
                    <x-core::icon name="ti ti-info-circle" />
                    <span>{{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_earn_notice') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
