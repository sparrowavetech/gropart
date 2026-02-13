<?php

namespace Botble\ProductBundles\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\DataSynchronize\Table\HeaderActions\ExportHeaderAction;
use Botble\DataSynchronize\Table\HeaderActions\ImportHeaderAction;
use Botble\ProductBundles\Models\Bundle;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\IsFeaturedBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\NumberBulkChange;
use Botble\Table\BulkChanges\SelectBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\YesNoColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BundleTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Bundle::class)
            ->addActions([
                EditAction::make()
                    ->url(function (Action $action) {
                        $bundle = $action->getItem();

                        if ($bundle instanceof Bundle && $bundle->ecommerce_product_id) {
                            return route('products.edit', $bundle->ecommerce_product_id);
                        }

                        return route('product-bundles.edit', $bundle instanceof Bundle ? $bundle->getKey() : null);
                    })
                    ->permission('product-bundles.edit'),
                DeleteAction::make()
                    ->route('product-bundles.destroy')
                    ->permission('product-bundles.destroy'),
            ])
            ->addHeaderActions([
                ExportHeaderAction::make()
                    ->route('tools.data-synchronize.export.bundles.index')
                    ->permission('product-bundles.export'),
                ImportHeaderAction::make()
                    ->route('tools.data-synchronize.import.bundles.index')
                    ->permission('product-bundles.import'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('product-bundles.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make(),
                Column::make('name')
                    ->title(trans('plugins/product-bundles::bundles.list.columns.name'))
                    ->alignStart(),
                Column::make('type')
                    ->title(trans('plugins/product-bundles::bundles.list.columns.type')),
                Column::make('pricing_type')
                    ->title(trans('plugins/product-bundles::bundles.list.columns.pricing'))
                    ->alignStart(),
                YesNoColumn::make('is_active')
                    ->title(trans('plugins/product-bundles::bundles.list.columns.active')),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query) {
                return $query->select([
                    'id',
                    'name',
                    'slug',
                    'description',
                    'image',
                    'type',
                    'pricing_type',
                    'pricing_value',
                    'is_active',
                    'is_featured',
                    'ecommerce_product_id',
                    'created_at',
                ]);
            });
    }

    public function ajax(): JsonResponse
    {
        $typeLabels = $this->getTypeLabels();
        $pricingLabels = $this->getPricingTypeLabels();

        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Bundle $bundle) {
                $label = BaseHelper::clean($bundle->name);

                if ($this->hasPermission('product-bundles.edit')) {
                    $editUrl = $bundle->ecommerce_product_id
                        ? route('products.edit', $bundle->ecommerce_product_id)
                        : route('product-bundles.edit', $bundle->getKey());

                    $label = Html::link($editUrl, $label)->toHtml();
                }

                $description = trim((string) $bundle->description);

                if ($description !== '') {
                    $label .= Html::tag(
                        'div',
                        BaseHelper::clean(Str::limit($description, 110)),
                        ['class' => 'text-muted small']
                    )->toHtml();
                }

                return $label;
            })
            ->editColumn('type', function (Bundle $bundle) use ($typeLabels) {
                $type = (string) $bundle->type;
                $label = $typeLabels[$type] ?? strtoupper($type);

                $class = $type === 'fixed'
                    ? 'badge bg-primary text-white rounded-pill'
                    : 'badge bg-warning text-dark rounded-pill';

                return Html::tag('span', BaseHelper::clean($label), [
                    'class' => $class,
                ])->toHtml();
            })
            ->editColumn('pricing_type', function (Bundle $bundle) use ($pricingLabels) {
                $type = (string) $bundle->pricing_type;
                $label = $pricingLabels[$type] ?? $type;
                $value = $this->formatPricingValue($bundle);

                $labelHtml = Html::tag('div', BaseHelper::clean($label), [
                    'class' => 'text-muted small',
                ])->toHtml();
                $valueHtml = Html::tag('div', BaseHelper::clean($value), [
                    'class' => 'fw-semibold',
                ])->toHtml();

                return Html::tag('div', $labelHtml . $valueHtml, [
                    'class' => 'd-flex flex-column gap-1',
                ])->toHtml();
            })
            ->filter(function ($query) {
                $keyword = request()->input('search.value');

                if (! $keyword) {
                    return $query;
                }

                $keyword = trim((string) $keyword);
                $like = '%' . $keyword . '%';
                $id = null;

                if (preg_match('/^#?\\d+$/', $keyword)) {
                    $id = (int) ltrim($keyword, '#');
                }

                return $query->where(function ($sub) use ($like, $id) {
                    $sub->where('product_bundles.name', 'LIKE', $like)
                        ->orWhere('product_bundles.slug', 'LIKE', $like);

                    if ($id) {
                        $sub->orWhere('product_bundles.id', $id);
                    }
                });
            });

        return $this->toJson($data);
    }

    public function buttons(): array
    {
        return $this->addCreateButton(
            route('products.create', ['bundle' => 1]),
            'product-bundles.create'
        );
    }

    public function getBulkChanges(): array
    {
        $types = $this->getTypeLabels();
        $pricingTypes = $this->getPricingTypeLabels();

        return [
            NameBulkChange::make(),
            SelectBulkChange::make()
                ->name('type')
                ->title(trans('plugins/product-bundles::bundles.form.fields.type'))
                ->choices($types)
                ->validate('required|in:' . implode(',', array_keys($types))),
            SelectBulkChange::make()
                ->name('pricing_type')
                ->title(trans('plugins/product-bundles::bundles.form.fields.pricing_rule'))
                ->choices($pricingTypes)
                ->validate('required|in:' . implode(',', array_keys($pricingTypes))),
            NumberBulkChange::make()
                ->name('pricing_value')
                ->title(trans('plugins/product-bundles::bundles.form.fields.pricing_value')),
            SelectBulkChange::make()
                ->name('is_active')
                ->title(trans('plugins/product-bundles::bundles.form.fields.active'))
                ->choices([
                    0 => trans('core/base::base.no'),
                    1 => trans('core/base::base.yes'),
                ])
                ->validate('required|in:0,1'),
            IsFeaturedBulkChange::make(),
            CreatedAtBulkChange::make(),
        ];
    }

    protected function formatPricingValue(Bundle $bundle): string
    {
        $value = (float) $bundle->pricing_value;

        if ($bundle->pricing_type === 'percent_off') {
            $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

            return $formatted . '%';
        }

        if (function_exists('pb_format_price')) {
            return pb_format_price($value);
        }

        return (string) $value;
    }

    protected function getTypeLabels(): array
    {
        $types = trans('plugins/product-bundles::bundles.form.types');

        return is_array($types) ? $types : [];
    }

    protected function getPricingTypeLabels(): array
    {
        $pricingTypes = trans('plugins/product-bundles::bundles.form.pricing_types');

        return is_array($pricingTypes) ? $pricingTypes : [];
    }
}
