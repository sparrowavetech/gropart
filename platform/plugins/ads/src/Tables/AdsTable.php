<?php

namespace Botble\Ads\Tables;

use Botble\Ads\Models\Ads;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AdsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Ads::class)
            ->addActions([
                EditAction::make()->route('ads.edit'),
                DeleteAction::make()->route('ads.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('key', function ($item) {
                return generate_shortcode('ads', ['key' => $item->key]);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'image',
                'key',
                'name',
                'clicked',
                'expired_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make(),
            NameColumn::make()->route('ads.edit'),
            Column::make('key')
                ->title(trans('plugins/ads::ads.shortcode'))
                ->alignStart(),
            Column::make('clicked')
                ->title(trans('plugins/ads::ads.clicked'))
                ->alignStart(),
            DateColumn::make('expired_at'),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('ads.create'), 'ads.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('ads.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'expired_at' => [
                'title' => trans('plugins/ads::ads.expired_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
