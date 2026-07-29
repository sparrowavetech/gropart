@php
    use Botble\LoyaltyPoints\Enums\ProductInfoBoxStyleEnum;
    $boxStyle = $boxStyle ?? ProductInfoBoxStyleEnum::DEFAULT;
    $appearance = $appearanceSettings ?? [];
    $isValidColor = fn ($color) => ! empty($color) && $color !== 'transparent';
    $customStyles = '';
    if ($isValidColor($appearance['bg_color'] ?? '')) {
        $customStyles .= 'background-color: ' . e($appearance['bg_color']) . ';';
    }
    if ($isValidColor($appearance['text_color'] ?? '')) {
        $customStyles .= 'color: ' . e($appearance['text_color']) . ';';
    }
    if ($isValidColor($appearance['border_color'] ?? '')) {
        $customStyles .= 'border-color: ' . e($appearance['border_color']) . ';';
    }
    if (($appearance['border_radius'] ?? '') !== '' && $appearance['border_radius'] !== null) {
        $customStyles .= 'border-radius: ' . (int) $appearance['border_radius'] . 'px;';
    }
    if (($appearance['padding'] ?? '') !== '' && $appearance['padding'] !== null) {
        $customStyles .= 'padding: ' . (int) $appearance['padding'] . 'px;';
    }
    $iconColor = $isValidColor($appearance['icon_color'] ?? '') ? 'color: ' . e($appearance['icon_color']) . ';' : '';

    // Resolved colors used by the scoped style block below so the chosen colors
    // actually take effect on every box style. Several styles paint inner panels
    // and text with hard-coded Bootstrap utilities (bg-white, bg-light,
    // text-muted, ...) that carry !important and sit on top of $customStyles
    // applied only to the outer wrapper, so the settings looked ignored.
    $bgColor = $isValidColor($appearance['bg_color'] ?? '') ? e($appearance['bg_color']) : '';
    $textColor = $isValidColor($appearance['text_color'] ?? '') ? e($appearance['text_color']) : '';
    // Border color also needs a scoped !important override: the styled boxes carry
    // Bootstrap's .border utility (border-color: var(--bs-border-color) !important),
    // which beats the plain inline border-color in $customStyles, so the setting
    // looked ignored on every style except Card (which uses .border-0).
    $borderColor = $isValidColor($appearance['border_color'] ?? '') ? e($appearance['border_color']) : '';
@endphp

@if($bgColor !== '' || $textColor !== '' || $borderColor !== '')
    @once
        <style>
            @if($bgColor !== '')
            {{-- :not(.bg-opacity-10) keeps the subtle tier-boost panels (Card/Banner
                 styles) translucent instead of forcing them to a solid box color. --}}
            .loyalty-product-info-box .bg-white,
            .loyalty-product-info-box .bg-light,
            .loyalty-product-info-box .bg-success:not(.bg-opacity-10),
            .loyalty-product-info-box .bg-primary:not(.bg-opacity-10) {
                background-color: {{ $bgColor }} !important;
            }
            @endif
            @if($textColor !== '')
            .loyalty-product-info-box,
            .loyalty-product-info-box h4,
            .loyalty-product-info-box h5,
            .loyalty-product-info-box h6,
            .loyalty-product-info-box p,
            .loyalty-product-info-box small,
            .loyalty-product-info-box span,
            .loyalty-product-info-box div,
            .loyalty-product-info-box .text-muted,
            .loyalty-product-info-box .text-primary,
            .loyalty-product-info-box .text-success,
            .loyalty-product-info-box .text-info {
                color: {{ $textColor }} !important;
            }
            @endif
            @if($borderColor !== '')
            .loyalty-product-info-box .border {
                border-color: {{ $borderColor }} !important;
            }
            @endif
        </style>
    @endonce
@endif

@if(isset($loyaltyConfig))
@once
<script>
window.loyaltyPointsConfig = @json($loyaltyConfig);
</script>
@endonce
@endif

<div class="loyalty-product-info-box"
     data-product-price="{{ $productPrice }}"
     data-points-to-earn="{{ $pointsToEarn }}"
     data-max-points="{{ $maxPointsCanUse }}"
     data-max-discount="{{ $maxDiscount }}">

@if($boxStyle === ProductInfoBoxStyleEnum::MINIMAL)
    {{-- Minimal Style --}}
    <div class="loyalty-product-info-minimal mt-4 mb-4">
        <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white" @if($customStyles) style="{{ $customStyles }}" @endif>
            <div class="d-flex align-items-center gap-2">
                <x-core::icon name="ti ti-gift" class="text-dark" style="width: 24px; height: 24px;{{ $iconColor }}" />
                <span class="fw-medium">{{ trans('plugins/loyalty-points::loyalty-points.product_info.title_minimal') }}</span>
            </div>
            <div class="loyalty-points-badge-minimal loyalty-earn-section px-3 py-2 rounded-2 bg-light" @if($pointsToEarn <= 0) style="display: none;" @endif>
                <span class="fw-bold loyalty-points-earn">+{{ number_format($pointsToEarn) }}</span>
                <span class="text-muted ms-1">{{ trans('plugins/loyalty-points::loyalty-points.product_info.point_label') }}</span>
            </div>
        </div>
    </div>

@elseif($boxStyle === ProductInfoBoxStyleEnum::COMPACT)
    {{-- Compact Style --}}
    <div class="loyalty-product-info-compact mt-3 mb-3">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 border rounded-pill bg-light" @if($customStyles) style="{{ $customStyles }}" @endif>
            <x-core::icon name="ti ti-gift" class="text-success" style="width: 18px; height: 18px;{{ $iconColor }}" />
            <span class="small loyalty-earn-section" @if($pointsToEarn <= 0) style="display: none;" @endif>
                <span class="fw-bold text-success loyalty-points-earn">+{{ number_format($pointsToEarn) }}</span>
                <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</span>
            </span>
            <span class="text-muted loyalty-redeem-separator" @if(!($maxPointsCanUse > 0 && $maxDiscount > 0)) style="display: none;" @endif>|</span>
            <span class="small text-muted loyalty-redeem-section" @if(!($maxPointsCanUse > 0 && $maxDiscount > 0)) style="display: none;" @endif>
                {{ trans('plugins/loyalty-points::loyalty-points.product_info.max_discount_label') }}:
                <span class="fw-bold text-primary loyalty-max-discount">{{ format_price($maxDiscount) }}</span>
            </span>
        </div>
    </div>

@elseif($boxStyle === ProductInfoBoxStyleEnum::CARD)
    {{-- Card Style --}}
    <div class="loyalty-product-info-card mt-4 mb-4">
        <div class="card shadow-sm border-0" @if($customStyles) style="{{ $customStyles }}" @endif>
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="loyalty-icon-wrapper d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width: 48px; height: 48px;">
                            <x-core::icon name="ti ti-gift" class="text-success" style="width: 24px; height: 24px;{{ $iconColor }}" />
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">{{ trans('plugins/loyalty-points::loyalty-points.product_info.title') }}</h6>
                            <p class="mb-0 small text-muted loyalty-earn-label" @if($pointsToEarn <= 0) style="display: none;" @endif>{{ trans('plugins/loyalty-points::loyalty-points.product_info.earn_points_label') }}</p>
                        </div>
                    </div>
                    <div class="text-end loyalty-earn-section" @if($pointsToEarn <= 0) style="display: none;" @endif>
                        <div class="fs-4 fw-bold text-success loyalty-points-earn">+{{ number_format($pointsToEarn) }}</div>
                        <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</small>
                    </div>
                </div>
                <div class="loyalty-redeem-section" @if(!($maxPointsCanUse > 0 && $maxDiscount > 0)) style="display: none;" @endif>
                    <hr class="my-3">
                    <div class="d-flex align-items-center justify-content-between small">
                        <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.product_info.max_discount_label') }}</span>
                        <span class="fw-bold text-primary loyalty-max-discount">{{ format_price($maxDiscount) }}</span>
                    </div>
                </div>
                @if(isset($customerLevel) && $customerLevel && $customerLevel->earning_rate > 1)
                    <div class="mt-2 p-2 bg-success bg-opacity-10 rounded-2 d-flex align-items-center small">
                        <x-core::icon name="ti ti-crown" class="text-success me-2" style="width: 16px; height: 16px;" />
                        <span class="text-success fw-medium">
                            {{ trans('plugins/loyalty-points::loyalty-points.checkout.earning_boost', ['name' => $customerLevel->name, 'rate' => $customerLevel->earning_rate]) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

@elseif($boxStyle === ProductInfoBoxStyleEnum::BANNER)
    {{-- Banner Style --}}
    <div class="loyalty-product-info-banner mt-4 mb-4">
        <div class="d-flex align-items-stretch rounded-3 overflow-hidden border" @if($customStyles) style="{{ $customStyles }}" @endif>
            <div class="d-flex align-items-center justify-content-center px-4 bg-success text-white">
                <x-core::icon name="ti ti-gift" style="width: 28px; height: 28px;{{ $iconColor }}" />
            </div>
            <div class="flex-grow-1 d-flex align-items-center justify-content-between p-3 bg-white">
                <div>
                    <h6 class="mb-0 fw-bold">{{ trans('plugins/loyalty-points::loyalty-points.product_info.title') }}</h6>
                    <small class="text-muted loyalty-tip-text loyalty-earn-section"
                           data-tip-earn="{{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_earn', ['points' => ':points']) }}"
                           @if($pointsToEarn <= 0) style="display: none;" @endif>{{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_earn', ['points' => number_format($pointsToEarn)]) }}</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center loyalty-earn-section" @if($pointsToEarn <= 0) style="display: none;" @endif>
                        <div class="fs-5 fw-bold text-success loyalty-points-earn">+{{ number_format($pointsToEarn) }}</div>
                        <small class="text-muted text-uppercase" style="font-size: 10px;">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</small>
                    </div>
                    <div class="border-start ps-3 text-center loyalty-redeem-section" @if(!($maxPointsCanUse > 0 && $maxDiscount > 0)) style="display: none;" @endif>
                        <div class="fs-5 fw-bold text-primary loyalty-max-discount">{{ format_price($maxDiscount) }}</div>
                        <small class="text-muted text-uppercase" style="font-size: 10px;">{{ trans('plugins/loyalty-points::loyalty-points.product_info.max_discount_label') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($customerLevel) && $customerLevel && $customerLevel->earning_rate > 1)
            <div class="mt-2 p-2 bg-success bg-opacity-10 rounded-2 d-flex align-items-center small">
                <x-core::icon name="ti ti-crown" class="text-success me-2" style="width: 16px; height: 16px;" />
                <span class="text-success fw-medium">
                    {{ trans('plugins/loyalty-points::loyalty-points.checkout.earning_boost', ['name' => $customerLevel->name, 'rate' => $customerLevel->earning_rate]) }}
                </span>
            </div>
        @endif
    </div>

@else
    {{-- Default Style --}}
    <div class="loyalty-product-info mt-4 mb-4 p-4 border rounded-3 bg-light" @if($customStyles) style="{{ $customStyles }}" @endif>
        <div class="d-flex align-items-center mb-3">
            <x-core::icon name="ti ti-gift" class="text-success me-2" style="width: 24px; height: 24px;{{ $iconColor }}" />
            <h5 class="mb-0 text-success fw-bold">{{ trans('plugins/loyalty-points::loyalty-points.product_info.title') }}</h5>
        </div>

        <div class="row g-3">
            <div class="col-md-6 loyalty-earn-section" @if($pointsToEarn <= 0) style="display: none;" @endif>
                <div class="points-earn-info">
                    <p class="mb-2 text-muted small">{{ trans('plugins/loyalty-points::loyalty-points.product_info.earn_points_label') }}</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white fs-6 px-3 py-2 loyalty-points-earn">+{{ number_format($pointsToEarn) }}</span>
                        <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 loyalty-redeem-section" @if(!($maxPointsCanUse > 0 && $maxDiscount > 0)) style="display: none;" @endif>
                <div class="points-redeem-info">
                    <p class="mb-2 text-muted small">{{ trans('plugins/loyalty-points::loyalty-points.product_info.max_discount_label') }}</p>
                    <div class="fw-bold text-primary fs-5 loyalty-max-discount">
                        {{ format_price($maxDiscount) }}
                    </div>
                    <small class="text-muted loyalty-max-points-text" data-template="{{ trans('plugins/loyalty-points::loyalty-points.product_info.using_points', ['points' => ':points']) }}">{{ trans('plugins/loyalty-points::loyalty-points.product_info.using_points', ['points' => number_format($maxPointsCanUse)]) }}</small>
                </div>
            </div>
        </div>

        @if(isset($customerLevel) && $customerLevel && $customerLevel->earning_rate > 1)
            <div class="loyalty-tier-boost mt-3 p-2 bg-success bg-opacity-10 rounded-2 d-flex align-items-center">
                <x-core::icon name="ti ti-crown" class="text-success me-2" style="width: 18px; height: 18px;" />
                <small class="text-success fw-bold">
                    {{ trans('plugins/loyalty-points::loyalty-points.checkout.earning_boost', ['name' => $customerLevel->name, 'rate' => $customerLevel->earning_rate]) }}
                </small>
            </div>
        @endif

        <div class="loyalty-product-tip mt-3 p-3 bg-white rounded-2 border">
            <div class="d-flex align-items-start gap-2">
                <x-core::icon name="ti ti-info-circle" class="text-info mt-1" style="width: 18px; height: 18px;" />
                <div class="small text-muted loyalty-tip-text"
                     data-tip-both="{{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_both', ['earn_points' => ':earn_points', 'max_discount' => ':max_discount']) }}"
                     data-tip-earn="{{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_earn', ['points' => ':points']) }}"
                     data-tip-redeem="{{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_redeem', ['discount' => ':discount']) }}">
                    @if($pointsToEarn > 0 && $maxPointsCanUse > 0)
                        {{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_both', ['earn_points' => number_format($pointsToEarn), 'max_discount' => format_price($maxDiscount)]) }}
                    @elseif($pointsToEarn > 0)
                        {{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_earn', ['points' => number_format($pointsToEarn)]) }}
                    @else
                        {{ trans('plugins/loyalty-points::loyalty-points.product_info.tip_redeem', ['discount' => format_price($maxDiscount)]) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

</div>
