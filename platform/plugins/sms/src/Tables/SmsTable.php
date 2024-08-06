<?php

namespace Botble\Sms\Tables;

use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\SelectBulkChange;

use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\Column;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Botble\Sms\Models\Sms;
use Botble\Sms\Enums\SmsEnum;
use Illuminate\Database\Eloquent\Builder;

class SmsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Sms::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('sms.create'))
            ->addActions([
                EditAction::make()->route('sms.edit'),
                DeleteAction::make()->route('sms.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                EnumColumn::make('name'),
                Column::make('template_id')
                    ->label(trans('plugins/sms::sms.template_id')),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addBulkChanges([
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'name',
                'template_id',
                'created_at',
                'status',
            ]));
    }
}
