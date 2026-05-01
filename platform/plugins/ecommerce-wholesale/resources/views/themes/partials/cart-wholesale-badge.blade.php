@php
    use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;

    $savingsFormatted = format_price($savings);
    $isPercentage = $discount_type === PricingDiscountTypeEnum::PERCENTAGE;
    $discountLabel = $isPercentage ? ($discount_value . '%') : $savingsFormatted;
@endphp
<div class="cart-wholesale-badge mt-2">
    <span
        style="background: #206bc4; color: #ffffff; display: inline-flex; align-items: center; max-width: 100%; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; line-height: 1.4;"
        title="{{ trans('plugins/ecommerce-wholesale::wholesale.cart.wholesale_price_applied_detail', ['discount' => $discountLabel, 'savings' => $savingsFormatted]) }}"
    >
        <x-core::icon name="ti ti-building-store" style="width: 14px; height: 14px; flex-shrink: 0;" class="me-1" />
        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ trans('plugins/ecommerce-wholesale::wholesale.cart.wholesale_price_applied', ['discount' => $discountLabel]) }}
        </span>
    </span>
</div>
