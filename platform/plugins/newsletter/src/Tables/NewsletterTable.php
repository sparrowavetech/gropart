<?php

namespace Botble\Newsletter\Tables;

use ArchiElite\NotificationPlus\Drivers\WhatsApp;
use Botble\Newsletter\Models\Newsletter;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\EmailBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EmailColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;

class NewsletterTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Newsletter::class)
            ->addColumns([
                IdColumn::make(),
                EmailColumn::make()->linkable(),
                LinkableColumn::make('whatsapp')
                    ->urlUsing(fn(LinkableColumn $column) => 'https://wa.me/' . $column->getItem()->whatsapp)
                    ->attributes([
                        'target' => '_blank',
                    ])
                    ->title(trans('plugins/newsletter::newsletter.whatsapp')),
                NameColumn::make(),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addActions([
                DeleteAction::make()->route('newsletter.destroy'),
            ])
            ->addBulkAction(DeleteBulkAction::make()->permission('newsletter.destroy'))
            ->addBulkChanges([
                NameBulkChange::make(),
                EmailBulkChange::make(),
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'email',
                        'name',
                        'created_at',
                        'status',
                        'whatsapp',
                    ]);
            });
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
