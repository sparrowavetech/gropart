@php
    Theme::asset()->usePath(false)->add('ecommerce-customer-css', 'vendor/core/plugins/ecommerce/css/customer.css');
    Theme::asset()->usePath(false)->add('loyalty-points-css', 'vendor/core/plugins/loyalty-points/css/loyalty-points.css');
@endphp

@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/loyalty-points::loyalty-points.page_titles.loyalty_points'))

@section('content')
    <div class="bb-customer-content-wrapper loyalty-points-page">
        {{-- Points Balance Cards --}}
        <div class="row g-3 mb-4">
            {{-- Current Balance --}}
            <div class="col-md-4">
                <div class="bb-customer-card h-100">
                    <div class="bb-customer-card-body">
                        <div class="d-flex align-items-center">
                            <div class="loyalty-icon-wrapper loyalty-icon-primary rounded-circle me-3">
                                <x-core::icon name="ti ti-gift" class="icon-lg" />
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.points.current_balance') }}</h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($balance->total_points) }}</h2>
                                <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lifetime Points --}}
            <div class="col-md-4">
                <div class="bb-customer-card h-100">
                    <div class="bb-customer-card-body">
                        <div class="d-flex align-items-center">
                            <div class="loyalty-icon-wrapper loyalty-icon-success rounded-circle me-3">
                                <x-core::icon name="ti ti-star" class="icon-lg" />
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.points.lifetime') }}</h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($balance->lifetime_points) }}</h2>
                                <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.points.earned_total') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current Tier --}}
            <div class="col-md-4">
                <div class="bb-customer-card h-100 tier-card {{ $balance->level ? 'tier-' . \Illuminate\Support\Str::slug($balance->level->name) : '' }}">
                    <div class="bb-customer-card-body position-relative z-1">
                        <div class="d-flex align-items-center">
                            @if($balance->level && $balance->level->badge)
                                <div class="loyalty-level-badge me-3">
                                    <img src="{{ RvMedia::getImageUrl($balance->level->badge) }}" alt="{{ $balance->level->name }}" class="loyalty-badge-img" style="width: 64px; height: 64px; object-fit: contain;">
                                </div>
                            @else
                                <div class="loyalty-icon-wrapper loyalty-icon-warning rounded-circle me-3">
                                    <x-core::icon name="ti ti-crown" class="icon-lg" />
                                </div>
                            @endif
                            <div>
                                <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.levels.menu_name') }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $balance->level ? $balance->level->name : trans('plugins/loyalty-points::loyalty-points.levels.default_member') }}</h3>
                                @if($balance->level)
                                    @if($balance->level->earning_rate > 1)
                                        <small class="text-muted d-block">{{ trans('plugins/loyalty-points::loyalty-points.levels.earning_rate') }}: x{{ $balance->level->earning_rate }}</small>
                                    @endif
                                    @if($balance->level_updated_at)
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">{{ trans('plugins/loyalty-points::loyalty-points.levels.member_since') }} {{ $balance->level_updated_at->translatedFormat('M d, Y') }}</small>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($balance->level && $balance->level->badge)
                        <img src="{{ RvMedia::getImageUrl($balance->level->badge) }}" alt="" class="tier-badge-img" style="position: absolute; bottom: 10px; right: 10px; width: 48px; height: 48px; opacity: 0.15; object-fit: contain;">
                    @else
                        <x-core::icon name="ti ti-crown" class="tier-badge" />
                    @endif
                </div>
            </div>
        </div>

        {{-- Loyalty Card Section --}}
        @if(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->isLoyaltyCardEnabled())
            @include('plugins/loyalty-points::themes.partials.loyalty-card')
        @endif

        {{-- Tier Progress & Benefits --}}
        @if($balance->level || isset($nextLevel))
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                     <div class="bb-customer-card h-100">
                        <div class="bb-customer-card-body">
                            <h5 class="bb-customer-card-title mb-3 d-flex align-items-center">
                                <span>
                                    <x-core::icon name="ti ti-trending-up" class="me-2" />
                                    @if($nextLevel)
                                        {{ trans('plugins/loyalty-points::loyalty-points.levels.next_level', ['name' => $nextLevel->name]) }}
                                    @else
                                        {{ trans('plugins/loyalty-points::loyalty-points.levels.top_tier_reached') }}
                                    @endif
                                </span>
                                <span class="ms-2" data-bs-toggle="tooltip" title="{{ trans('plugins/loyalty-points::loyalty-points.levels.tier_rules_tooltip') }}">
                                    <x-core::icon name="ti ti-help" class="text-muted cursor-pointer" style="width: 16px; height: 16px;" />
                                </span>
                            </h5>
                            @if($nextLevel)
                                <div class="tier-progress-wrapper mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">{{ trans('plugins/loyalty-points::loyalty-points.levels.current_points', ['points' => number_format($balance->lifetime_points)]) }}</span>
                                        <span class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.levels.target_points', ['points' => number_format($nextLevel->min_points)]) }}</span>
                                    </div>
                                    <div class="progress" style="height: 12px;">
                                        @php
                                            $percent = $nextLevel->min_points > 0 ? min(100, ($balance->lifetime_points / $nextLevel->min_points) * 100) : 100;
                                        @endphp
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        {{ trans('plugins/loyalty-points::loyalty-points.levels.points_needed', ['points' => number_format($nextLevel->min_points - $balance->lifetime_points), 'name' => $nextLevel->name]) }}
                                    </div>
                                </div>
                            @else
                                <div class="tier-max-reached">
                                    <x-core::icon name="ti ti-confetti" class="tier-max-icon" />
                                    <span>{{ trans('plugins/loyalty-points::loyalty-points.levels.top_tier_message') }}</span>
                                </div>
                            @endif
                        </div>
                     </div>
                </div>
                <div class="col-md-4">
                    <div class="bb-customer-card h-100">
                        <div class="bb-customer-card-body">
                             <h5 class="bb-customer-card-title mb-3">
                                <x-core::icon name="ti ti-gift" class="me-2" />
                                {{ trans('plugins/loyalty-points::loyalty-points.levels.benefits') }}
                            </h5>
                            @if($balance->level && $balance->level->benefits)
                                <ul class="benefits-list">
                                    @foreach(explode(PHP_EOL, $balance->level->benefits) as $benefit)
                                        @if(trim($benefit))
                                            <li><i class="ti ti-check"></i> {{ trim($benefit) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small mb-0">{{ trans('plugins/loyalty-points::loyalty-points.levels.no_benefits') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->isEnabled())
            {{-- Program Information --}}
            <div class="bb-customer-card mb-4">
                <div class="bb-customer-card-body">
                    <h5 class="bb-customer-card-title mb-3">
                        <x-core::icon name="ti ti-info-circle" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.customer.program_info') }}
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <x-core::icon name="ti ti-coins" class="text-primary me-2 mt-1 flex-shrink-0" />
                                <div>
                                    <strong class="d-block mb-1">{{ trans('plugins/loyalty-points::loyalty-points.customer.earning_title') }}</strong>
                                    <p class="mb-0 text-muted small">
                                        {{ trans('plugins/loyalty-points::loyalty-points.customer.earning_info', [
                                            'points' => number_format(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getEarningRate()),
                                            'currency' => format_price(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getEarningCurrency())
                                        ]) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <x-core::icon name="ti ti-discount-2" class="text-success me-2 mt-1 flex-shrink-0" />
                                <div>
                                    <strong class="d-block mb-1">{{ trans('plugins/loyalty-points::loyalty-points.customer.redemption_title') }}</strong>
                                    <p class="mb-0 text-muted small">
                                        {{ trans('plugins/loyalty-points::loyalty-points.customer.redemption_info', [
                                            'points' => number_format(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getRedemptionRate()),
                                            'currency' => format_price(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getRedemptionCurrency())
                                        ]) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Transaction History --}}
        @if($transactions->count() > 0)
            <div class="bb-customer-card">
                <div class="bb-customer-card-header">
                    <h5 class="bb-customer-card-title mb-0">
                        <x-core::icon name="ti ti-history" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.transaction.history') }}
                    </h5>
                </div>
                <div class="bb-customer-card-body p-0">
                    <div class="transaction-list">
                        @foreach($transactions as $transaction)
                            <div class="transaction-item">
                                <div class="transaction-icon
                                    @if($transaction->type->getValue() === 'earn') transaction-icon-earn
                                    @elseif($transaction->type->getValue() === 'redeem') transaction-icon-redeem
                                    @elseif($transaction->type->getValue() === 'adjust') transaction-icon-adjust
                                    @else transaction-icon-default
                                    @endif">
                                    @if($transaction->type->getValue() === 'earn')
                                        <x-core::icon name="ti ti-plus" />
                                    @elseif($transaction->type->getValue() === 'redeem')
                                        <x-core::icon name="ti ti-minus" />
                                    @elseif($transaction->type->getValue() === 'adjust')
                                        <x-core::icon name="ti ti-adjustments" />
                                    @else
                                        <x-core::icon name="ti ti-arrows-exchange" />
                                    @endif
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-main">
                                        <span class="transaction-type">{{ $transaction->type->label() }}</span>
                                        @if($transaction->order)
                                            <a href="{{ route('customer.orders.view', $transaction->order->id) }}" class="transaction-order">
                                                {{ $transaction->order->code }}
                                            </a>
                                        @endif
                                    </div>
                                    @if($transaction->note)
                                        <div class="transaction-note">{{ $transaction->note }}</div>
                                    @endif
                                    <div class="transaction-date">
                                        {{ $transaction->created_at->translatedFormat('M d, Y') }} • {{ $transaction->created_at->translatedFormat('h:i A') }}
                                    </div>
                                </div>
                                <div class="transaction-points @if($transaction->points > 0) text-success @else text-danger @endif">
                                    {{ $transaction->formatted_points }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($transactions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {!! $transactions->links() !!}
                </div>
            @endif
        @else
            @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
                'title' => trans('plugins/loyalty-points::loyalty-points.customer.no_transactions'),
                'subtitle' => trans('plugins/loyalty-points::loyalty-points.customer.start_shopping'),
                'actionUrl' => route('public.products'),
                'actionLabel' => trans('plugins/loyalty-points::loyalty-points.customer.shop_now'),
            ])
        @endif
    </div>
@endsection
