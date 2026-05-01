<?php

namespace Botble\LoyaltyPoints\Tables;

use Botble\Base\Facades\Html;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\ViewAction;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class MembersTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomerPointBalance::class)
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('customer.name')
                    ->title(trans('plugins/ecommerce::customer.name'))
                    ->renderUsing(fn (FormattedColumn $column) => Html::link(
                        route('loyalty-points.members.show', $column->getItem()->id),
                        $column->getItem()->customer?->name ?? '—'
                    )),
                FormattedColumn::make('customer.email')
                    ->title(trans('core/base::forms.email'))
                    ->searchable()
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->customer?->email ?? '—'),
                FormattedColumn::make('level.name')
                    ->title(trans('plugins/loyalty-points::loyalty-points.members.level'))
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->level
                        ? $column->getItem()->level->toHtml()
                        : Html::tag('span', trans('plugins/loyalty-points::loyalty-points.levels.default_member'), ['class' => 'badge bg-secondary text-white'])),
                FormattedColumn::make('total_points')
                    ->title(trans('plugins/loyalty-points::loyalty-points.points.balance'))
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag(
                        'span',
                        number_format($column->getItem()->total_points),
                        ['class' => $column->getItem()->total_points > 0 ? 'text-success fw-bold' : 'text-muted']
                    ))
                    ->alignEnd(),
                FormattedColumn::make('lifetime_points')
                    ->title(trans('plugins/loyalty-points::loyalty-points.points.lifetime'))
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag(
                        'span',
                        number_format($column->getItem()->lifetime_points),
                        ['class' => 'fw-bold']
                    ))
                    ->alignEnd(),
                FormattedColumn::make('updated_at')
                    ->title(trans('core/base::tables.updated_at'))
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->updated_at?->diffForHumans()),
            ])
            ->addActions([
                ViewAction::make()
                    ->route('loyalty-points.members.show')
                    ->permission('loyalty-points.members.index'),
                Action::make('history')
                    ->label(trans('plugins/loyalty-points::loyalty-points.points.history'))
                    ->icon('ti ti-history')
                    ->url(fn (Action $action) => route('loyalty-points.transactions.customer', $action->getItem()->customer_id))
                    ->permission('loyalty-points.index'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->with(['customer:id,name,email', 'level:id,name'])
                    ->select([
                        'ec_customer_points_balances.id',
                        'ec_customer_points_balances.customer_id',
                        'ec_customer_points_balances.level_id',
                        'ec_customer_points_balances.total_points',
                        'ec_customer_points_balances.lifetime_points',
                        'ec_customer_points_balances.updated_at',
                    ])
                    ->latest('updated_at');
            });
    }

    public function getDefaultButtons(): array
    {
        return ['reload'];
    }
}
