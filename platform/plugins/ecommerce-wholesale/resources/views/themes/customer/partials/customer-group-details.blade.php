@php
    $tierSlug = \Illuminate\Support\Str::slug($group->name);
    $isPercentage = $group->discount_type->getValue() === \Botble\EcommerceWholesale\Enums\DiscountTypeEnum::PERCENTAGE;
    $discountDisplay = $isPercentage
        ? rtrim(rtrim(number_format($group->discount_value, 2), '0'), '.') . '%'
        : format_price($group->discount_value);
@endphp

<div class="ws-member-level-section mb-4">
    <div class="row g-3">
        {{-- Current Level Card --}}
        <div class="col-md-4">
            <div class="ws-level-card h-100 tier-{{ $tierSlug }}">
                <div class="ws-level-card-body">
                    <div class="d-flex align-items-center">
                        <div class="ws-level-icon-wrapper">
                            <x-core::icon name="ti ti-crown" />
                        </div>
                        <div class="ws-level-info">
                            <h6 class="ws-level-label">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_group') }}</h6>
                            <h4 class="ws-level-name">{{ $group->name }}</h4>
                            @if ($group->description)
                                <small class="ws-level-desc d-block">{{ $group->description }}</small>
                            @endif
                            @if ($groupAssignment)
                                <small class="ws-level-since d-block">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.member_since') }} {{ $groupAssignment->assigned_at?->translatedFormat('M d, Y') ?? $groupAssignment->created_at->translatedFormat('M d, Y') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <x-core::icon name="ti ti-crown" class="ws-level-badge-icon" />
            </div>
        </div>

        {{-- Discount Info Card --}}
        <div class="col-md-5">
            <div class="ws-discount-card h-100">
                <div class="ws-discount-card-body">
                    <h5 class="ws-discount-title">
                        <x-core::icon name="ti ti-discount-2" class="me-2" />
                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.your_discount') }}
                    </h5>
                    <div class="ws-discount-value-wrapper mt-3">
                        <span class="ws-discount-value">{{ $discountDisplay }}</span>
                        <span class="badge bg-indigo-lt ms-2">
                            {{ $isPercentage ? trans('plugins/ecommerce-wholesale::wholesale.discount_types.percentage') : trans('plugins/ecommerce-wholesale::wholesale.discount_types.fixed') }}
                        </span>
                    </div>
                    <div class="mt-3">
                        @if ($group->min_order_quantity)
                            <div class="ws-discount-detail">
                                <x-core::icon name="ti ti-package" class="text-muted me-1" />
                                {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.min_order_quantity', ['qty' => $group->min_order_quantity]) }}
                            </div>
                        @endif
                        @if ($group->min_order_value)
                            <div class="ws-discount-detail">
                                <x-core::icon name="ti ti-coin" class="text-muted me-1" />
                                {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.min_order_value', ['value' => format_price($group->min_order_value)]) }}
                            </div>
                        @endif
                    </div>

                    @if ($groupAssignment && $groupAssignment->expires_at)
                        <div class="mt-3 pt-2 border-top">
                            <x-core::icon name="ti ti-calendar" class="text-muted me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.expires') }} {{ $groupAssignment->expires_at->translatedFormat('M d, Y') }}
                            @if ($groupAssignment->isExpired())
                                <span class="badge bg-red-lt ms-1">{{ trans('plugins/ecommerce-wholesale::wholesale.frontend.expired') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Benefits Card --}}
        <div class="col-md-3">
            <div class="ws-benefits-card h-100">
                <div class="ws-benefits-card-body">
                    <h5 class="ws-benefits-title">
                        <x-core::icon name="ti ti-gift" class="me-2" />
                        {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.benefits') }}
                    </h5>
                    <ul class="ws-benefits-list">
                        <li>
                            <x-core::icon name="ti ti-check" class="text-success me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_pricing') }}
                        </li>
                        <li>
                            <x-core::icon name="ti ti-check" class="text-success me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.discount_on_orders', ['discount' => $discountDisplay]) }}
                        </li>
                        <li>
                            <x-core::icon name="ti ti-check" class="text-success me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.volume_tier_pricing') }}
                        </li>
                        <li>
                            <x-core::icon name="ti ti-check" class="text-success me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.exclusive_products') }}
                        </li>
                        <li>
                            <x-core::icon name="ti ti-check" class="text-success me-1" />
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.priority_support') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Wholesale Member Level Section */
.ws-member-level-section .ws-level-card,
.ws-member-level-section .ws-discount-card,
.ws-member-level-section .ws-benefits-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.25rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.ws-member-level-section .ws-level-card:hover,
.ws-member-level-section .ws-discount-card:hover,
.ws-member-level-section .ws-benefits-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

/* Level card gradient */
.ws-member-level-section .ws-level-card {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #fff;
}

.ws-member-level-section .ws-level-card.tier-bronze {
    background: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%);
}

.ws-member-level-section .ws-level-card.tier-silver {
    background: linear-gradient(135deg, #c0c0c0 0%, #808080 100%);
}

.ws-member-level-section .ws-level-card.tier-gold {
    background: linear-gradient(135deg, #ffd700 0%, #ffb300 100%);
}

.ws-member-level-section .ws-level-card.tier-platinum {
    background: linear-gradient(135deg, #e5e4e2 0%, #a8a8a8 100%);
    color: #333;
}

.ws-member-level-section .ws-level-card.tier-retailers {
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
}

.ws-member-level-section .ws-level-icon-wrapper {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.ws-member-level-section .ws-level-icon-wrapper svg {
    width: 24px;
    height: 24px;
}

.ws-member-level-section .ws-level-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.ws-member-level-section .ws-level-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.ws-member-level-section .ws-level-desc,
.ws-member-level-section .ws-level-since {
    font-size: 0.7rem;
    opacity: 0.85;
}

.ws-member-level-section .ws-level-badge-icon {
    position: absolute;
    right: -10px;
    bottom: -10px;
    width: 80px;
    height: 80px;
    opacity: 0.15;
}

/* Discount card */
.ws-member-level-section .ws-discount-title,
.ws-member-level-section .ws-benefits-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.ws-member-level-section .ws-discount-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.ws-member-level-section .ws-discount-detail {
    font-size: 0.85rem;
    color: #666;
    padding: 0.2rem 0;
    display: flex;
    align-items: center;
}

.ws-member-level-section .ws-discount-detail svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

/* Benefits card */
.ws-member-level-section .ws-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ws-member-level-section .ws-benefits-list li {
    padding: 0.35rem 0;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
}

.ws-member-level-section .ws-benefits-list li svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .ws-member-level-section .ws-level-card,
    .ws-member-level-section .ws-discount-card,
    .ws-member-level-section .ws-benefits-card {
        padding: 1rem;
    }

    .ws-member-level-section .ws-level-name {
        font-size: 1.1rem;
    }

    .ws-member-level-section .ws-level-icon-wrapper {
        width: 40px;
        height: 40px;
    }

    .ws-member-level-section .ws-discount-value {
        font-size: 1.5rem;
    }
}
</style>
