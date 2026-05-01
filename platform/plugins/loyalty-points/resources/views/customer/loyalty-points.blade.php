<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('plugins/loyalty-points::loyalty-points.page_titles.loyalty_points') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <h5>{{ trans('plugins/loyalty-points::loyalty-points.points.current_balance') }}</h5>
                                <h2 class="mb-0">{{ number_format($balance->total_points) }}</h2>
                                <small>{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-success">
                                <h5>{{ trans('plugins/loyalty-points::loyalty-points.points.lifetime') }}</h5>
                                <h2 class="mb-0">{{ number_format($balance->lifetime_points) }}</h2>
                                <small>{{ trans('plugins/loyalty-points::loyalty-points.points.earned_total') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning">
                                <h5>{{ trans('plugins/loyalty-points::loyalty-points.levels.menu_name') }}</h5>
                                <h2 class="mb-0 d-flex align-items-center gap-2">
                                    @if($balance->level && $balance->level->badge)
                                        <img src="{{ RvMedia::getImageUrl($balance->level->badge) }}" alt="{{ $balance->level->name }}" style="width: 32px; height: 32px; object-fit: contain;">
                                    @endif
                                    {{ $balance->level ? $balance->level->name : '—' }}
                                </h2>
                                @if($balance->level && $balance->level->earning_rate > 1)
                                    <small>{{ trans('plugins/loyalty-points::loyalty-points.levels.earning_rate') }}: x{{ $balance->level->earning_rate }}</small>
                                @endif
                                @if($balance->level && $balance->level->benefits)
                                    <hr class="my-2">
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach(explode(PHP_EOL, $balance->level->benefits) as $benefit)
                                            <li><i class="ti ti-check text-success"></i> {{ $benefit }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(isset($nextLevel) && $nextLevel)
                        <div class="alert alert-secondary mb-4">
                            <h5>{{ trans('plugins/loyalty-points::loyalty-points.levels.next_level', ['name' => $nextLevel->name]) }}</h5>
                            <div class="progress mb-2" style="height: 20px;">
                                @php
                                    $current = $balance->lifetime_points;
                                    $target = $nextLevel->min_points;
                                    $percent = $target > 0 ? min(100, ($current / $target) * 100) : 100;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($percent, 1) }}%
                                </div>
                            </div>
                            <small>{{ trans('plugins/loyalty-points::loyalty-points.levels.points_needed', ['points' => number_format($target - $current)]) }}</small>
                        </div>
                    @endif

                    @if(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->isEnabled())
                        <div class="alert alert-light mb-4">
                            <p class="mb-1">
                                <i class="ti ti-info-circle"></i>
                                {{ trans('plugins/loyalty-points::loyalty-points.customer.earning_info', [
                                    'points' => number_format(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getEarningRate()),
                                    'currency' => format_price(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getEarningCurrency())
                                ]) }}
                            </p>
                            <p class="mb-0">
                                <i class="ti ti-gift"></i>
                                {{ trans('plugins/loyalty-points::loyalty-points.customer.redemption_info', [
                                    'points' => number_format(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getRedemptionRate()),
                                    'currency' => format_price(app(\Botble\LoyaltyPoints\Helpers\LoyaltyHelper::class)->getRedemptionCurrency())
                                ]) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('plugins/loyalty-points::loyalty-points.points.history') }}</h4>
                </div>
                <div class="card-body">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ trans('plugins/loyalty-points::loyalty-points.transaction.date') }}</th>
                                        <th>{{ trans('plugins/loyalty-points::loyalty-points.transaction.type') }}</th>
                                        <th>{{ trans('plugins/loyalty-points::loyalty-points.points.points') }}</th>
                                        <th>{{ trans('plugins/ecommerce::order.order') }}</th>
                                        <th>{{ trans('plugins/loyalty-points::loyalty-points.transaction.note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                {!! $transaction->type->toHtml() !!}
                                            </td>
                                            <td class="{{ $transaction->points > 0 ? 'text-success' : 'text-danger' }}">
                                                <strong>{{ $transaction->formatted_points }}</strong>
                                            </td>
                                            <td>
                                                @if($transaction->order)
                                                    <a href="{{ route('customer.orders.view', $transaction->order->id) }}">
                                                        {{ $transaction->order->code }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $transaction->note ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {!! $transactions->links() !!}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ trans('plugins/loyalty-points::loyalty-points.customer.no_transactions') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
