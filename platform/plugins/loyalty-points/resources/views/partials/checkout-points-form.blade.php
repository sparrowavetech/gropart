<div class="loyalty-points-redemption card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="ti ti-gift"></i>
            {{ trans('plugins/loyalty-points::loyalty-points.checkout.redeem_points') }}
        </h5>
    </div>
    <div class="card-body">
        <p class="mb-3">
            {{ trans('plugins/loyalty-points::loyalty-points.checkout.your_balance') }}:
            <strong class="text-success">{{ number_format($balance) }} {{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</strong>
        </p>

        <div class="form-group">
            <label for="loyalty_points_to_redeem">
                {{ trans('plugins/loyalty-points::loyalty-points.customer.enter_points_to_redeem') }}
            </label>
            <div class="input-group">
                <input
                    type="number"
                    name="loyalty_points_to_redeem"
                    id="loyalty_points_to_redeem"
                    class="form-control"
                    min="0"
                    max="{{ $balance }}"
                    value="0"
                    placeholder="0"
                >
                <button type="button" class="btn btn-primary" id="apply_loyalty_points">
                    {{ trans('plugins/loyalty-points::loyalty-points.checkout.apply_points') }}
                </button>
            </div>
            <small class="form-text text-muted">
                @if($loyaltyHelper->getMinRedeemablePoints() > 0)
                    {{ trans('plugins/loyalty-points::loyalty-points.settings.min_redeemable_points') }}: {{ number_format($loyaltyHelper->getMinRedeemablePoints()) }}
                @endif
                @if($loyaltyHelper->getMaxRedeemablePoints() > 0)
                    | {{ trans('plugins/loyalty-points::loyalty-points.settings.max_redeemable_points') }}: {{ number_format($loyaltyHelper->getMaxRedeemablePoints()) }}
                @endif
            </small>
        </div>

        <div id="loyalty_points_discount_info" class="alert alert-success d-none mt-3">
            <strong>{{ trans('plugins/loyalty-points::loyalty-points.checkout.points_discount') }}:</strong>
            <span id="loyalty_discount_amount"></span>
        </div>
    </div>
</div>

<script>
    (function() {
        const input = document.getElementById('loyalty_points_to_redeem');
        const applyBtn = document.getElementById('apply_loyalty_points');
        const discountInfo = document.getElementById('loyalty_points_discount_info');
        const discountAmount = document.getElementById('loyalty_discount_amount');

        applyBtn?.addEventListener('click', function() {
            const points = parseInt(input.value) || 0;

            if (points <= 0) {
                alert('{{ trans('plugins/loyalty-points::loyalty-points.errors.points_must_be_positive') }}');
                return;
            }

            if (points > {{ $balance }}) {
                alert('{{ trans('plugins/loyalty-points::loyalty-points.errors.insufficient_points') }}');
                return;
            }

            const minPoints = {{ $loyaltyHelper->getMinRedeemablePoints() }};
            if (minPoints > 0 && points < minPoints) {
                alert('{{ trans('plugins/loyalty-points::loyalty-points.errors.points_below_minimum', ['min' => '']) }}' + minPoints);
                return;
            }

            const maxPoints = {{ $loyaltyHelper->getMaxRedeemablePoints() }};
            if (maxPoints > 0 && points > maxPoints) {
                alert('{{ trans('plugins/loyalty-points::loyalty-points.errors.points_above_maximum', ['max' => '']) }}' + maxPoints);
                return;
            }

            // Calculate discount
            const rate = {{ $loyaltyHelper->getRedemptionRate() }};
            const currency = {{ $loyaltyHelper->getRedemptionCurrency() }};
            const discount = (points / rate) * currency;

            discountAmount.textContent = '{{ get_application_currency()->symbol }}' + discount.toLocaleString();
            discountInfo.classList.remove('d-none');

            // Trigger cart recalculation if available
            if (typeof window.updateCart === 'function') {
                window.updateCart();
            }
        });
    })();
</script>
