<div class="card mb-3 mt-3">
    <div class="card-header">
        <h4 class="card-title mb-0">
            <x-core::icon name="ti ti-gift" class="me-1" />
            {{ trans('plugins/loyalty-points::loyalty-points.thank_you.loyalty_points') }}
        </h4>
    </div>
    <div class="card-body">
        @if($orderLoyalty->points_redeemed > 0)
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_redeemed') }}:</span>
                    <span class="badge bg-red text-red-fg fs-6">
                        <x-core::icon name="ti ti-minus" class="icon-sm" />
                        {{ number_format($orderLoyalty->points_redeemed) }} {{ trans('plugins/loyalty-points::loyalty-points.points.points') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.checkout.points_discount') }}:</span>
                    <strong class="text-red">-{{ format_price($orderLoyalty->discount_amount) }}</strong>
                </div>
            </div>
        @endif

        @if($orderLoyalty->points_to_earn > 0)
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_to_earn') }}:</span>
                    <span class="badge bg-green text-green-fg fs-6">
                        <x-core::icon name="ti ti-plus" class="icon-sm" />
                        {{ number_format($orderLoyalty->points_to_earn) }} {{ trans('plugins/loyalty-points::loyalty-points.points.points') }}
                    </span>
                </div>
                @if($order->status->getValue() !== 'completed')
                    <div class="alert alert-info mb-0 mt-2">
                        <small>
                            <x-core::icon name="ti ti-info-circle" class="icon-sm me-1" />
                            {{ trans('plugins/loyalty-points::loyalty-points.thank_you.points_earn_notice') }}
                        </small>
                    </div>
                @endif
            </div>
        @endif

        @if($orderLoyalty->points_redeemed <= 0 && $orderLoyalty->points_to_earn <= 0)
            <p class="text-muted mb-0">{{ trans('plugins/loyalty-points::loyalty-points.customer.no_transactions') }}</p>
        @endif
    </div>
</div>
