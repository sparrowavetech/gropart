<?php

namespace Botble\EcommerceWholesale\Tables;

use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class CustomerGroupTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomerGroup::class)
            ->addActions([
                EditAction::make()->route('wholesale.customer-groups.edit'),
                DeleteAction::make()->route('wholesale.customer-groups.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('discount_value', function (CustomerGroup $item) {
                if ($item->discount_type->getValue() === DiscountTypeEnum::PERCENTAGE) {
                    return $item->discount_value . '%';
                }

                return format_price($item->discount_value);
            })
            ->editColumn('customers_count', function (CustomerGroup $item) {
                return $item->customers_count;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'discount_type',
                'discount_value',
                'priority',
                'status',
                'created_at',
            ])
            ->withCount('customers');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('wholesale.customer-groups.edit'),
            EnumColumn::make('discount_type')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_type'))
                ->enumClass(DiscountTypeEnum::class),
            Column::make('discount_value')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_value'))
                ->alignEnd(),
            Column::make('priority')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.customer_group.priority'))
                ->alignEnd(),
            Column::make('customers_count')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.customer_group.customers_count'))
                ->orderable(false)
                ->searchable(false)
                ->alignEnd(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('wholesale.customer-groups.create'), 'wholesale.customer-groups.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('wholesale.customer-groups.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:255',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => CustomerGroupStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', CustomerGroupStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
