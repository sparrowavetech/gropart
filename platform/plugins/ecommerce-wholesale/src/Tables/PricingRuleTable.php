<?php

namespace Botble\EcommerceWholesale\Tables;

use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class PricingRuleTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(GroupPricingRule::class)
            ->addActions([
                EditAction::make()->route('wholesale.pricing-rules.edit'),
                DeleteAction::make()->route('wholesale.pricing-rules.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('target', function (GroupPricingRule $item) {
                return match ($item->scope->getValue()) {
                    PricingRuleScopeEnum::PRODUCT => $item->product?->name ?? '-',
                    PricingRuleScopeEnum::CATEGORY => $item->category?->name ?? '-',
                    default => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.all_products'),
                };
            })
            ->editColumn('customer_group_id', function (GroupPricingRule $item) {
                return $item->customerGroup?->name ?? trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.all_groups');
            })
            ->editColumn('quantity_range', function (GroupPricingRule $item) {
                return $item->quantity_range;
            })
            ->editColumn('discount_value', function (GroupPricingRule $item) {
                if ($item->discount_type->getValue() === PricingDiscountTypeEnum::PERCENTAGE) {
                    return $item->discount_value . '%';
                }

                return format_price($item->discount_value);
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
                'scope',
                'product_id',
                'category_id',
                'customer_group_id',
                'min_quantity',
                'max_quantity',
                'discount_type',
                'discount_value',
                'status',
                'created_at',
            ])
            ->with(['product', 'category', 'customerGroup']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            EnumColumn::make('scope')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope'))
                ->enumClass(PricingRuleScopeEnum::class),
            Column::make('target')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.target'))
                ->orderable(false)
                ->searchable(false),
            Column::make('customer_group_id')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.customer_group'))
                ->orderable(false),
            Column::make('quantity_range')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.quantity_range'))
                ->orderable(false)
                ->searchable(false),
            EnumColumn::make('discount_type')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_type'))
                ->enumClass(PricingDiscountTypeEnum::class),
            Column::make('discount_value')
                ->title(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value'))
                ->alignEnd(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('wholesale.pricing-rules.create'), 'wholesale.pricing-rules.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('wholesale.pricing-rules.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'scope' => [
                'title' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope'),
                'type' => 'select',
                'choices' => PricingRuleScopeEnum::labels(),
                'validate' => 'required|in:' . implode(',', PricingRuleScopeEnum::values()),
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
