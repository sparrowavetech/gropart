<?php

namespace SparroWave\IndianGst\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class TaxSlabProductsTable extends TableAbstract
{
    protected ?int $taxId = null;
    protected ?Tax $taxModel = null;
    protected int $defaultSortColumn = 1;
    protected string $defaultSortDirection = 'desc';
    protected bool $bStateSave = false;

    public function setTaxId(int $taxId): self
    {
        $this->taxId = $taxId;
        $this->taxModel = Tax::query()->find($taxId);

        return $this;
    }

    public function setup(): void
    {
        $this
            ->model(Product::class)
            ->addActions([
                EditAction::make()->route('products.edit'),
            ])
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make(),
                Column::make('name')
                    ->title(trans('plugins/ecommerce::products.form.name'))
                    ->alignStart(),
                Column::make('sku')
                    ->title(trans('plugins/ecommerce::products.sku'))
                    ->alignStart(),
                Column::make('hsn_code')
                    ->title(__('HSN / SAC'))
                    ->alignCenter(),
                Column::make('price')
                    ->title(__('MRP (Incl. Tax)'))
                    ->alignEnd(),
                Column::make('base_price')
                    ->title(__('Base Price'))
                    ->orderable(false)
                    ->searchable(false)
                    ->alignEnd(),
                Column::make('tax_amount')
                    ->title(__('GST Amount'))
                    ->orderable(false)
                    ->searchable(false)
                    ->alignEnd(),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $taxId = (int) ($this->taxId ?: request()->route('id'));

        $query = $this->getModel()
            ->query()
            ->select([
                'ec_products.id',
                'ec_products.name',
                'ec_products.images',
                'ec_products.image',
                'ec_products.sku',
                'ec_products.hsn_code',
                'ec_products.price',
                'ec_products.status',
                'ec_products.created_at',
            ])
            ->where('ec_products.is_variation', 0);

        if ($taxId) {
            $query->whereHas('taxes', function ($q) use ($taxId) {
                $q->where('ec_taxes.id', $taxId);
            });
        }

        return $this->applyScopes($query);
    }

    public function ajax(): JsonResponse
    {
        $taxId = (int) ($this->taxId ?: request()->route('id'));
        $tax = $this->taxModel ?: Tax::query()->find($taxId);
        $taxPct = (float) ($tax?->percentage ?? 18);

        $data = $this->table
            ->eloquent($this->query())
            ->orderColumn('base_price', 'ec_products.price $1')
            ->orderColumn('tax_amount', 'ec_products.price $1')
            ->editColumn('name', function (Product $item) {
                return Html::link(
                    route('products.edit', $item->getKey()),
                    BaseHelper::clean($item->name),
                    ['class' => 'fw-medium text-primary', 'target' => '_blank']
                );
            })
            ->editColumn('hsn_code', function (Product $item) {
                return $item->hsn_code ? '<span class="badge bg-azure-lt">' . e($item->hsn_code) . '</span>' : '<span class="text-muted">&mdash;</span>';
            })
            ->editColumn('price', function (Product $item) {
                return format_price($item->price);
            })
            ->addColumn('base_price', function (Product $item) use ($taxPct) {
                $calc = IndianGstHelper::calculateReverseTax((float) $item->price, $taxPct);

                return format_price($calc['base_price']);
            })
            ->addColumn('tax_amount', function (Product $item) use ($taxPct) {
                $calc = IndianGstHelper::calculateReverseTax((float) $item->price, $taxPct);

                return '<span class="text-success fw-bold">' . format_price($calc['tax_amount']) . ' (' . $taxPct . '%)</span>';
            })
            ->filter(function ($query) {
                return $query->searchByKeyword(request()->input('search.value'));
            });

        return $this->toJson($data);
    }
}
