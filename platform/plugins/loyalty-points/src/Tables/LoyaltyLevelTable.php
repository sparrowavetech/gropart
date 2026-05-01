<?php

namespace Botble\LoyaltyPoints\Tables;

use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;

class LoyaltyLevelTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(LoyaltyLevel::class)
            ->addActions([
                EditAction::make()->route('loyalty-points.levels.edit'),
                DeleteAction::make()->route('loyalty-points.levels.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('loyalty-points.levels.destroy'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'name',
                        'badge',
                        'min_points',
                        'max_points',
                        'earning_rate',
                        'created_at',
                        'status',
                    ]);
            });
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make('badge')
                ->title(trans('plugins/loyalty-points::loyalty-points.levels.badge'))
                ->width(70),
            NameColumn::make()->route('loyalty-points.levels.edit'),
            Column::make('min_points')->title(trans('plugins/loyalty-points::loyalty-points.levels.min_points')),
            FormattedColumn::make('max_points')
                ->title(trans('plugins/loyalty-points::loyalty-points.levels.max_points'))
                ->getValueUsing(function (FormattedColumn $column) {
                    $value = $column->getItem()->max_points;

                    return $value ?? '∞';
                }),
            Column::make('earning_rate')->title(trans('plugins/loyalty-points::loyalty-points.levels.earning_rate')),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('loyalty-points.levels.create'), 'loyalty-points.levels.create');
    }
}
