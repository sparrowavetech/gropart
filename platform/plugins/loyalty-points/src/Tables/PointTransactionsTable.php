<?php

namespace Botble\LoyaltyPoints\Tables;

use Botble\Base\Facades\Html;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\ViewAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class PointTransactionsTable extends TableAbstract
{
    protected int|string|null $customerId = null;

    public function forCustomer(int|string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function setup(): void
    {
        $this
            ->model(PointTransaction::class)
            ->addColumns($this->getTableColumns())
            ->addActions($this->getTableActions())
            ->queryUsing(function (Builder $query) {
                $baseQuery = $query
                    ->with(['customer:id,name,email', 'order:id,code', 'customer.pointBalance:id,customer_id'])
                    ->select([
                        'ec_customer_points_transactions.id',
                        'ec_customer_points_transactions.customer_id',
                        'ec_customer_points_transactions.order_id',
                        'ec_customer_points_transactions.type',
                        'ec_customer_points_transactions.points',
                        'ec_customer_points_transactions.note',
                        'ec_customer_points_transactions.created_at',
                    ])->latest();

                if ($this->customerId) {
                    $baseQuery->where('customer_id', $this->customerId);
                }

                return $baseQuery;
            });
    }

    protected function getTableActions(): array
    {
        $actions = [];

        if (! $this->customerId) {
            $actions[] = ViewAction::make()
                ->label(trans('plugins/loyalty-points::loyalty-points.members.details'))
                ->url(fn (ViewAction $action) => route(
                    'loyalty-points.members.show',
                    $action->getItem()->customer?->pointBalance?->id ?? 0
                ))
                ->permission('loyalty-points.members.index');
        }

        return $actions;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    protected function getTableColumns(): array
    {
        $columns = [
            IdColumn::make(),
        ];

        if (! $this->getCustomerId()) {
            $columns[] = FormattedColumn::make('customer.name')
                ->title(trans('plugins/ecommerce::customer.name'))
                ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->customer?->name ?? '—');
        }

        return array_merge($columns, [
            EnumColumn::make('type')
                ->title(trans('plugins/loyalty-points::loyalty-points.transaction.type'))
                ->width(120),
            FormattedColumn::make('points')
                ->title(trans('plugins/loyalty-points::loyalty-points.points.points'))
                ->renderUsing(function (FormattedColumn $column) {
                    $points = $column->getItem()->points;
                    $class = $points > 0 ? 'text-success' : 'text-danger';
                    $prefix = $points > 0 ? '+' : '';

                    return Html::tag('span', $prefix . number_format($points), ['class' => $class . ' fw-bold']);
                })
                ->alignCenter()
                ->width(120),
            FormattedColumn::make('order_id')
                ->title(trans('plugins/ecommerce::order.order'))
                ->renderUsing(function (FormattedColumn $column) {
                    $order = $column->getItem()->order;
                    if (! $order) {
                        return '—';
                    }

                    return Html::link(route('orders.edit', $order->id), $order->code, ['target' => '_blank']);
                })
                ->width(150),
            Column::make('note')
                ->title(trans('plugins/loyalty-points::loyalty-points.transaction.note'))
                ->searchable(),
            FormattedColumn::make('created_at')
                ->title(trans('core/base::tables.created_at'))
                ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->created_at?->format('Y-m-d H:i:s'))
                ->width(170),
        ]);
    }

    public function getDefaultButtons(): array
    {
        return ['reload'];
    }
}
