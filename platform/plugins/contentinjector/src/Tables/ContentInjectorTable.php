<?php

namespace Botble\ContentInjector\Tables;

use Botble\ContentInjector\Models\ContentInjector;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class ContentInjectorTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContentInjector::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('contentinjector.create'))
            ->addActions([
                EditAction::make()->route('contentinjector.edit'),
                DeleteAction::make()->route('contentinjector.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('contentinjector.edit'),
                NameColumn::make("value")->label("value"),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('contentinjector.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query) {
                $query->select([
                    'id',
                    'name',
                    'value',
                    'created_at',
                    'status',
                ]);
            });
    }
}
