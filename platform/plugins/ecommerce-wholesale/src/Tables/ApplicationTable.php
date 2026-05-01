<?php

namespace Botble\EcommerceWholesale\Tables;

use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class ApplicationTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(WholesaleApplication::class)
            ->addColumns([
                IdColumn::make(),
                Column::make('name')
                    ->title(trans('core/base::tables.name'))
                    ->alignStart(),
                Column::make('email')
                    ->title(trans('core/base::tables.email'))
                    ->alignStart(),
                Column::make('company_name')
                    ->title(trans('plugins/ecommerce-wholesale::wholesale.application.company_name'))
                    ->alignStart(),
                EnumColumn::make('status')
                    ->title(trans('core/base::tables.status'))
                    ->width(120),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                Action::make('review')
                    ->route('wholesale.applications.edit')
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.review'))
                    ->icon('ti ti-eye')
                    ->permission('wholesale.applications.edit'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('wholesale.applications.destroy'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'name',
                        'email',
                        'company_name',
                        'status',
                        'created_at',
                    ])
                    ->latest();
            });
    }
}
