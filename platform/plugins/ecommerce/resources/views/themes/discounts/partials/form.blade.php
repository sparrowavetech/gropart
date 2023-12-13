@if ($discounts->isNotEmpty())
    <div class="checkout__coupon-list">
        @if ($discounts->count() > 2)
            <div class="checkout__coupon-navigation">
                <button class="checkout__coupon-prev" onclick="navigateDiscounts('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="checkout__coupon-next" onclick="navigateDiscounts('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
        @endif

        @foreach ($discounts as $discount)
            <div
                @class(['checkout__coupon-item', 'active' => session()->has('applied_coupon_code') && session()->get('applied_coupon_code') === $discount->code])
                data-discount-code="{{ $discount->code }}"
            >
                <div class="checkout__coupon-item-icon"></div>
                <div class="checkout__coupon-item-content">
                    <div class="checkout__coupon-item-title">
                        <h4>{{ $discount->code }}</h4>
                        @if($discount->quantity > 0)
                            <span class="checkout__coupon-item-count">
                                ({{ __('Left :left', ['left' => $discount->left_quantity]) }})
                            </span>
                        @endif
                    </div>
                    @if ($discount->description)
                        <div class="checkout__coupon-item-description">
                            {{ $discount->description }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<div
    class="checkout-discount-section"
    @if (session()->has('applied_coupon_code')) style="display: none;" @endif
>
    <a class="btn-open-coupon-form" href="#">
        {{ __('You have a coupon code?') }}
    </a>
</div>
<div
    class="coupon-wrapper mt-2"
    @if (!session()->has('applied_coupon_code')) style="display: none;" @endif
>
    @if (!session()->has('applied_coupon_code'))
        @include('plugins/ecommerce::themes.discounts.partials.apply-coupon')
    @else
        @include('plugins/ecommerce::themes.discounts.partials.remove-coupon')
    @endif
</div>
<div class="clearfix"></div>
