<?php

namespace Botble\Marketplace\Tables;

use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Models\Discount;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiscountTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Discount::class)
            ->addActions([
                DeleteAction::make()->route('marketplace.vendor.discounts.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('detail', function ($item) {
                $isCoupon = $item->type === DiscountTypeEnum::COUPON;

                return view('plugins/ecommerce::discounts.detail', compact('item', 'isCoupon'))->render();
            })
            ->editColumn('total_used', function ($item) {
                if ($item->type === 'promotion') {
                    return '&mdash;';
                }

                if ($item->quantity === null) {
                    return $item->total_used;
                }

                return $item->total_used . '/' . $item->quantity;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select(['*'])
            ->where('store_id', auth('customer')->user()->store->id);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('detail')
                ->name('code')
                ->title(trans('plugins/ecommerce::discount.detail'))
                ->alignLeft(),
            Column::make('total_used')
                ->title(trans('plugins/ecommerce::discount.used'))
                ->width(100),
            DateColumn::make('start_date')
                ->title(trans('plugins/ecommerce::discount.start_date')),
            DateColumn::make('end_date')
                ->title(trans('plugins/ecommerce::discount.end_date')),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.vendor.discounts.create'));
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->beforeDispatch(function (Discount $discount, array $ids) {
                foreach ($ids as $id) {
                    $discount = Discount::query()->findOrFail($id);

                    if ($discount->store_id !== auth('customer')->user()->store->id) {
                        abort(403);
                    }
                }
            }),
        ];
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::discounts.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
