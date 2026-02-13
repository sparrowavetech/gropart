<?php

namespace Botble\ProductBundles\Exporters;

use Botble\DataSynchronize\Exporter\ExportColumn;
use Botble\DataSynchronize\Exporter\ExportCounter;
use Botble\DataSynchronize\Exporter\Exporter;
use Botble\Media\Facades\RvMedia;
use Botble\ProductBundles\Models\Bundle;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BundleExporter extends Exporter
{
    public function getLabel(): string
    {
        return trans('plugins/product-bundles::bundles.menu');
    }

    public function columns(): array
    {
        $types = $this->getTypeOptions();
        $pricingTypes = $this->getPricingTypeOptions();

        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('slug'),
            ExportColumn::make('description'),
            ExportColumn::make('image'),
            ExportColumn::make('type')->dropdown($types),
            ExportColumn::make('pricing_type')->dropdown($pricingTypes),
            ExportColumn::make('pricing_value'),
            ExportColumn::make('is_active')->boolean(),
            ExportColumn::make('is_featured')->boolean(),
            ExportColumn::make('start_date')->dateTime(),
            ExportColumn::make('end_date')->dateTime(),
            ExportColumn::make('attached_product_ids'),
            ExportColumn::make('fixed_items'),
            ExportColumn::make('mix_groups'),
        ];
    }

    public function counters(): array
    {
        return [
            ExportCounter::make()
                ->label(trans('plugins/product-bundles::bundles.list.title'))
                ->value(Bundle::query()->count()),
        ];
    }

    public function hasDataToExport(): bool
    {
        return Bundle::query()->exists();
    }

    public function collection(): Collection
    {
        return Bundle::query()
            ->with(['items', 'groups.items', 'products'])
            ->orderBy('id')
            ->get()
            ->transform(function (Bundle $bundle) {
                return [
                    'id' => $bundle->id,
                    'name' => $bundle->name,
                    'slug' => $bundle->slug,
                    'description' => $bundle->description,
                    'image' => $bundle->image ? RvMedia::getImageUrl($bundle->image) : '',
                    'type' => $bundle->type,
                    'pricing_type' => $bundle->pricing_type,
                    'pricing_value' => $bundle->pricing_value,
                    'is_active' => (int) $bundle->is_active,
                    'is_featured' => (int) $bundle->is_featured,
                    'start_date' => $bundle->start_date,
                    'end_date' => $bundle->end_date,
                    'attached_product_ids' => $bundle->products->pluck('id')->implode(','),
                    'fixed_items' => $bundle->type === 'fixed' ? $this->formatFixedItems($bundle) : '',
                    'mix_groups' => $bundle->type === 'mix' ? $this->formatMixGroups($bundle) : '',
                ];
            });
    }

    protected function formatFixedItems(Bundle $bundle): string
    {
        return $bundle->items
            ->map(function ($item) {
                $product = (string) $item->product_id;

                if ($item->variation_id) {
                    $product .= '@' . $item->variation_id;
                }

                return $product . ':' . max(1, (int) $item->quantity);
            })
            ->implode('|');
    }

    protected function formatMixGroups(Bundle $bundle): string
    {
        return $bundle->groups
            ->map(function ($group) {
                $name = Str::of((string) $group->name)
                    ->replace(['|', ';'], ' ')
                    ->trim()
                    ->toString();

                $items = $group->items
                    ->map(function ($item) {
                        $product = (string) $item->product_id;

                        if ($item->variation_id) {
                            $product .= '@' . $item->variation_id;
                        }

                        return $product;
                    })
                    ->implode(',');

                return implode('|', [
                    $name,
                    max(0, (int) $group->choose_min),
                    max(1, (int) $group->choose_max),
                    $items,
                ]);
            })
            ->implode(';');
    }

    protected function getTypeOptions(): array
    {
        $types = trans('plugins/product-bundles::bundles.form.types');

        return is_array($types) ? array_keys($types) : ['fixed', 'mix'];
    }

    protected function getPricingTypeOptions(): array
    {
        $pricingTypes = trans('plugins/product-bundles::bundles.form.pricing_types');

        return is_array($pricingTypes) ? array_keys($pricingTypes) : ['fixed_total', 'percent_off', 'amount_off'];
    }
}
