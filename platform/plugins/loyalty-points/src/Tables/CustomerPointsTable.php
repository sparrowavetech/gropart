<?php

namespace Botble\LoyaltyPoints\Tables;

use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;

class CustomerPointsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomerPointBalance::class)
            ->addActions([
                Action::make('adjust')
                    ->label(trans('plugins/loyalty-points::loyalty-points.adjustment.adjust_points'))
                    ->icon('ti ti-edit')
                    ->route('loyalty-points.adjust-points.show')
                    ->permission('loyalty-points.edit'),
                Action::make('history')
                    ->label(trans('plugins/loyalty-points::loyalty-points.points.history'))
                    ->icon('ti ti-history')
                    ->route('loyalty-points.transactions.index')
                    ->permission('loyalty-points.index'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->with('customer:id,name,email')
                    ->select([
                        'ec_customer_points_balances.id',
                        'ec_customer_points_balances.customer_id',
                        'ec_customer_points_balances.total_points',
                        'ec_customer_points_balances.lifetime_points',
                        'ec_customer_points_balances.updated_at',
                    ]);
            });
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make('customer.name')
                ->title(trans('plugins/ecommerce::customer.name'))
                ->route('customers.edit')
                ->permission('customers.edit'),
            FormattedColumn::make('customer.email')
                ->title(trans('plugins/ecommerce::customer.email'))
                ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->customer?->email),
            Column::make('total_points')
                ->title(trans('plugins/loyalty-points::loyalty-points.points.balance'))
                ->alignCenter()
                ->width(150),
            Column::make('lifetime_points')
                ->title(trans('plugins/loyalty-points::loyalty-points.points.lifetime'))
                ->alignCenter()
                ->width(150),
            FormattedColumn::make('updated_at')
                ->title(trans('core/base::tables.updated_at'))
                ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->updated_at?->format('Y-m-d H:i:s'))
                ->width(150),
        ];
    }
}
