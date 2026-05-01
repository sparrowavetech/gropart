@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="loyalty-reports-page">
    <div class="row">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-blue-lt text-blue rounded-circle p-3">
                                <x-core::icon name="ti ti-users" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.total_customers') }}</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalCustomers) }}</h2>
                            <small class="text-green">
                                {{ number_format($activeCustomers) }} {{ trans('plugins/loyalty-points::loyalty-points.reports.active') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-green-lt text-green rounded-circle p-3">
                                <x-core::icon name="ti ti-gift" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.total_points_earned') }}</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalPointsEarned) }}</h2>
                            <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.lifetime') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-orange-lt text-orange rounded-circle p-3">
                                <x-core::icon name="ti ti-discount-2" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.total_points_redeemed') }}</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalPointsRedeemed) }}</h2>
                            <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.lifetime') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-cyan-lt text-cyan rounded-circle p-3">
                                <x-core::icon name="ti ti-coins" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.points_in_circulation') }}</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalPointsInCirculation) }}</h2>
                            <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.current_balance') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-purple-lt text-purple rounded-circle p-3">
                                <x-core::icon name="ti ti-chart-bar" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.redemption_rate') }}</h6>
                            <h2 class="mb-0 fw-bold">
                                @if($totalPointsEarned > 0)
                                    {{ number_format(($totalPointsRedeemed / $totalPointsEarned) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </h2>
                            <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.of_earned') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-yellow-lt text-yellow rounded-circle p-3">
                                <x-core::icon name="ti ti-star" class="icon-lg" />
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">{{ trans('plugins/loyalty-points::loyalty-points.reports.avg_points_per_customer') }}</h6>
                            <h2 class="mb-0 fw-bold">
                                @if($activeCustomers > 0)
                                    {{ number_format($totalPointsInCirculation / $activeCustomers, 0) }}
                                @else
                                    0
                                @endif
                            </h2>
                            <small class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.per_active_customer') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity by Type (Last 30 Days) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">
                        <x-core::icon name="ti ti-activity" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.reports.activity_last_30_days') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach(['earn', 'redeem', 'adjust', 'reverse'] as $type)
                            @php
                                $activity = $pointsActivityByType->get($type);
                                $typeLabel = trans('plugins/loyalty-points::loyalty-points.transaction_types.' . $type);
                                $iconMap = [
                                    'earn' => ['icon' => 'ti ti-plus', 'color' => 'green'],
                                    'redeem' => ['icon' => 'ti ti-minus', 'color' => 'orange'],
                                    'adjust' => ['icon' => 'ti ti-edit', 'color' => 'blue'],
                                    'reverse' => ['icon' => 'ti ti-refresh', 'color' => 'red'],
                                ];
                            @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="text-center p-3 rounded bg-{{ $iconMap[$type]['color'] }}-lt">
                                    <x-core::icon name="{{ $iconMap[$type]['icon'] }}" class="icon-xl text-{{ $iconMap[$type]['color'] }} mb-2" />
                                    <h6 class="text-muted mb-1">{{ $typeLabel }}</h6>
                                    <h3 class="mb-0 fw-bold">{{ number_format($activity->total_points ?? 0) }}</h3>
                                    <small class="text-muted">{{ number_format($activity->count ?? 0) }} {{ trans('plugins/loyalty-points::loyalty-points.reports.transactions') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Top Customers by Current Balance --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">
                        <x-core::icon name="ti ti-trophy" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.reports.top_customers_by_balance') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($topCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">#</th>
                                        <th class="border-0">{{ trans('plugins/loyalty-points::loyalty-points.reports.customer') }}</th>
                                        <th class="border-0 text-end">{{ trans('plugins/loyalty-points::loyalty-points.reports.current_points') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $index => $balance)
                                        <tr>
                                            <td>
                                                @if($index === 0)
                                                    <span class="badge bg-yellow text-yellow-fg">{{ $index + 1 }}</span>
                                                @elseif($index === 1)
                                                    <span class="badge bg-azure text-azure-fg">{{ $index + 1 }}</span>
                                                @elseif($index === 2)
                                                    <span class="badge bg-orange text-orange-fg">{{ $index + 1 }}</span>
                                                @else
                                                    <span class="text-muted">{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $balance->customer->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $balance->customer->email }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-green">{{ number_format($balance->total_points) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <x-core::icon name="ti ti-inbox" class="icon-xxl text-muted mb-3" />
                            <p class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.no_data') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Customers by Lifetime Points --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">
                        <x-core::icon name="ti ti-medal" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.reports.top_customers_by_lifetime') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($topLifetimeCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">#</th>
                                        <th class="border-0">{{ trans('plugins/loyalty-points::loyalty-points.reports.customer') }}</th>
                                        <th class="border-0 text-end">{{ trans('plugins/loyalty-points::loyalty-points.reports.lifetime_points') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topLifetimeCustomers as $index => $balance)
                                        <tr>
                                            <td>
                                                @if($index === 0)
                                                    <span class="badge bg-yellow text-yellow-fg">{{ $index + 1 }}</span>
                                                @elseif($index === 1)
                                                    <span class="badge bg-azure text-azure-fg">{{ $index + 1 }}</span>
                                                @elseif($index === 2)
                                                    <span class="badge bg-orange text-orange-fg">{{ $index + 1 }}</span>
                                                @else
                                                    <span class="text-muted">{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $balance->customer->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $balance->customer->email }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-blue">{{ number_format($balance->lifetime_points) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <x-core::icon name="ti ti-inbox" class="icon-xxl text-muted mb-3" />
                            <p class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.no_data') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">
                        <x-core::icon name="ti ti-history" class="me-2" />
                        {{ trans('plugins/loyalty-points::loyalty-points.reports.recent_transactions') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">{{ trans('plugins/loyalty-points::loyalty-points.transaction.date') }}</th>
                                        <th class="border-0">{{ trans('plugins/loyalty-points::loyalty-points.reports.customer') }}</th>
                                        <th class="border-0">{{ trans('plugins/loyalty-points::loyalty-points.transaction.type') }}</th>
                                        <th class="border-0 text-end">{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</th>
                                        <th class="border-0">{{ trans('plugins/ecommerce::order.order') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <small>{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $transaction->customer->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $transaction->customer->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                {!! $transaction->type->toHtml() !!}
                                            </td>
                                            <td class="text-end">
                                                <strong class="@if($transaction->points > 0) text-green @else text-red @endif">
                                                    {{ $transaction->formatted_points }}
                                                </strong>
                                            </td>
                                            <td>
                                                @if($transaction->order_id && $transaction->order)
                                                    <a href="{{ route('orders.edit', $transaction->order->id) }}" class="text-decoration-none">
                                                        <x-core::icon name="ti ti-receipt" class="icon-sm me-1" />
                                                        {{ $transaction->order->code }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <x-core::icon name="ti ti-inbox" class="icon-xxl text-muted mb-3" />
                            <p class="text-muted">{{ trans('plugins/loyalty-points::loyalty-points.reports.no_transactions') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
