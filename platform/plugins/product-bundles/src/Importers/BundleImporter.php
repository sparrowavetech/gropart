<?php

namespace Botble\ProductBundles\Importers;

use Botble\DataSynchronize\Importer\ImportColumn;
use Botble\DataSynchronize\Importer\Importer;
use Botble\ProductBundles\Models\Bundle;
use Botble\ProductBundles\Models\BundleGroup;
use Botble\ProductBundles\Models\BundleGroupItem;
use Botble\ProductBundles\Models\BundleItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class BundleImporter extends Importer
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
            ImportColumn::make('id')
                ->rules(['nullable', 'integer', 'min:1']),
            ImportColumn::make('name')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('slug')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('image')
                ->rules(['nullable', 'string']),
            ImportColumn::make('type')
                ->rules(['required', Rule::in($types)]),
            ImportColumn::make('pricing_type')
                ->rules(['required', Rule::in($pricingTypes)]),
            ImportColumn::make('pricing_value')
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('is_featured')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('start_date')
                ->rules(['nullable', 'date']),
            ImportColumn::make('end_date')
                ->rules(['nullable', 'date', 'after_or_equal:start_date']),
            ImportColumn::make('attached_product_ids')
                ->rules(['nullable', 'string']),
            ImportColumn::make('fixed_items')
                ->rules(['nullable', 'string']),
            ImportColumn::make('mix_groups')
                ->rules(['nullable', 'string']),
        ];
    }

    public function getValidateUrl(): string
    {
        return route('tools.data-synchronize.import.bundles.validate');
    }

    public function getImportUrl(): string
    {
        return route('tools.data-synchronize.import.bundles.store');
    }

    public function getDownloadExampleUrl(): ?string
    {
        return route('tools.data-synchronize.import.bundles.download-example');
    }

    public function getExportUrl(): ?string
    {
        return Auth::user()->hasPermission('product-bundles.export')
            ? route('tools.data-synchronize.export.bundles.store')
            : null;
    }

    public function examples(): array
    {
        return [
            [
                'id' => '',
                'name' => 'Sample Fixed Bundle',
                'slug' => 'sample-fixed-bundle',
                'description' => 'Bundle with fixed items',
                'image' => 'https://via.placeholder.com/600x400',
                'type' => 'fixed',
                'pricing_type' => 'fixed_total',
                'pricing_value' => 99.99,
                'is_active' => 'Yes',
                'is_featured' => 'No',
                'start_date' => '',
                'end_date' => '',
                'attached_product_ids' => '1,2',
                'fixed_items' => '1:1|2:2',
                'mix_groups' => '',
            ],
            [
                'id' => '',
                'name' => 'Sample Mix Bundle',
                'slug' => 'sample-mix-bundle',
                'description' => 'Bundle with mix-and-match groups',
                'image' => 'https://via.placeholder.com/600x400',
                'type' => 'mix',
                'pricing_type' => 'percent_off',
                'pricing_value' => 10,
                'is_active' => 'Yes',
                'is_featured' => 'No',
                'start_date' => '',
                'end_date' => '',
                'attached_product_ids' => '3',
                'fixed_items' => '',
                'mix_groups' => 'Group A|1|2|3,4;Group B|1|1|5',
            ],
        ];
    }

    public function handle(array $data): int
    {
        $count = 0;
        $skipExisting = request()->boolean('skip_existing_records');

        foreach ($data as $row) {
            $bundle = $this->findBundle($row);

            if ($bundle && $skipExisting) {
                continue;
            }

            $payload = $this->normalizePayload($row);

            if (! $bundle) {
                $bundle = new Bundle();
                $bundle->created_by = Auth::id();
            }

            $bundle->fill($payload);
            $bundle->save();

            $this->syncBundleRelations($bundle, $row);

            try {
                app('product-bundles')->syncBundleProduct($bundle);
            } catch (Throwable $e) {
                // ignore
            }

            $count++;
        }

        return $count;
    }

    protected function normalizePayload(array $row): array
    {
        $payload = Arr::only($row, [
            'name',
            'slug',
            'description',
            'image',
            'type',
            'pricing_type',
            'pricing_value',
            'is_active',
            'is_featured',
            'start_date',
            'end_date',
        ]);

        $payload['is_active'] = (int) Arr::get($payload, 'is_active', 0);
        $payload['is_featured'] = (int) Arr::get($payload, 'is_featured', 0);
        $payload['pricing_value'] = (float) Arr::get($payload, 'pricing_value', 0);

        $payload['image'] = $this->normalizeImage(Arr::get($payload, 'image'));
        $payload['start_date'] = $this->parseDate(Arr::get($payload, 'start_date'));
        $payload['end_date'] = $this->parseDate(Arr::get($payload, 'end_date'));

        return $payload;
    }

    protected function normalizeImage(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $this->resolveMediaImage($value, 'bundles');
    }

    protected function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function syncBundleRelations(Bundle $bundle, array $row): void
    {
        $bundle->products()->sync(
            $this->parseIdList(Arr::get($row, 'attached_product_ids'))
        );

        $this->resetBundleItems($bundle);

        if ($bundle->type === 'fixed') {
            $fixedItems = $this->parseFixedItems(Arr::get($row, 'fixed_items'));

            foreach ($fixedItems as $index => $item) {
                $productId = (int) Arr::get($item, 'product_id', 0);
                $quantity = (int) Arr::get($item, 'quantity', 1);

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                BundleItem::query()->create([
                    'bundle_id' => $bundle->getKey(),
                    'product_id' => $productId,
                    'variation_id' => Arr::get($item, 'variation_id') ?: null,
                    'quantity' => $quantity,
                    'sort_order' => $index,
                ]);
            }

            return;
        }

        $groups = $this->parseMixGroups(Arr::get($row, 'mix_groups'));

        foreach ($groups as $gIndex => $groupData) {
            $name = trim((string) Arr::get($groupData, 'name'));

            if ($name === '') {
                continue;
            }

            $group = BundleGroup::query()->create([
                'bundle_id' => $bundle->getKey(),
                'name' => $name,
                'choose_min' => max(0, (int) Arr::get($groupData, 'choose_min', 1)),
                'choose_max' => max(1, (int) Arr::get($groupData, 'choose_max', 1)),
                'sort_order' => $gIndex,
            ]);

            $items = Arr::get($groupData, 'items', []);

            foreach ($items as $iIndex => $item) {
                $productId = (int) Arr::get($item, 'product_id', 0);

                if ($productId <= 0) {
                    continue;
                }

                BundleGroupItem::query()->create([
                    'group_id' => $group->getKey(),
                    'product_id' => $productId,
                    'variation_id' => Arr::get($item, 'variation_id') ?: null,
                    'sort_order' => $iIndex,
                ]);
            }
        }
    }

    protected function resetBundleItems(Bundle $bundle): void
    {
        $bundle->items()->delete();

        $bundle->groups()->each(function (BundleGroup $group) {
            $group->items()->delete();
        });

        $bundle->groups()->delete();
    }

    protected function parseIdList(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => (int) $item)
                ->filter(fn ($item) => $item > 0)
                ->values()
                ->all();
        }

        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        return collect(preg_split('/[|,;]+/', $value))
            ->map(fn ($item) => (int) trim((string) $item))
            ->filter(fn ($item) => $item > 0)
            ->values()
            ->all();
    }

    protected function parseFixedItems(mixed $value): array
    {
        $value = is_string($value) ? trim($value) : $value;

        $decoded = $this->decodeJson($value);
        if (is_array($decoded)) {
            $items = Arr::get($decoded, 'items', $decoded);

            return $this->normalizeFixedItems($items);
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $result = [];
        $parts = preg_split('/[|;]+/', $value);

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            $qty = 1;
            $productPart = $part;
            $variationId = null;

            if (str_contains($part, ':')) {
                [$productPart, $qtyValue] = explode(':', $part, 2);
                $qty = (int) trim($qtyValue);
            }

            if (str_contains($productPart, '@')) {
                [$productPart, $variationValue] = explode('@', $productPart, 2);
                $variationId = (int) trim($variationValue);
            }

            $productId = (int) trim($productPart);

            if ($productId > 0) {
                $result[] = [
                    'product_id' => $productId,
                    'variation_id' => $variationId ?: null,
                    'quantity' => max(1, $qty),
                ];
            }
        }

        return $result;
    }

    protected function normalizeFixedItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $result = [];

        foreach ($items as $item) {
            if (is_numeric($item)) {
                $result[] = [
                    'product_id' => (int) $item,
                    'variation_id' => null,
                    'quantity' => 1,
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $productId = (int) Arr::get($item, 'product_id', Arr::get($item, 'product', 0));
            $variationId = (int) Arr::get($item, 'variation_id', Arr::get($item, 'variation', 0));
            $qty = (int) Arr::get($item, 'quantity', Arr::get($item, 'qty', 1));

            if ($productId <= 0) {
                continue;
            }

            $result[] = [
                'product_id' => $productId,
                'variation_id' => $variationId ?: null,
                'quantity' => max(1, $qty),
            ];
        }

        return $result;
    }

    protected function parseMixGroups(mixed $value): array
    {
        $value = is_string($value) ? trim($value) : $value;

        $decoded = $this->decodeJson($value);
        if (is_array($decoded)) {
            $groups = Arr::get($decoded, 'groups', $decoded);

            return $this->normalizeMixGroups($groups);
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $result = [];
        $groups = preg_split('/[;]+/', $value);

        foreach ($groups as $group) {
            $group = trim((string) $group);
            if ($group === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $group));
            $name = $parts[0] ?? '';

            if ($name === '') {
                continue;
            }

            $min = (int) ($parts[1] ?? 1);
            $max = (int) ($parts[2] ?? $min);
            $items = $this->parseGroupItemsString($parts[3] ?? '');

            $result[] = [
                'name' => $name,
                'choose_min' => max(0, $min),
                'choose_max' => max(1, $max),
                'items' => $items,
            ];
        }

        return $result;
    }

    protected function normalizeMixGroups(mixed $groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $result = [];

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $name = trim((string) Arr::get($group, 'name'));

            if ($name === '') {
                continue;
            }

            $min = (int) Arr::get($group, 'choose_min', Arr::get($group, 'min', 1));
            $max = (int) Arr::get($group, 'choose_max', Arr::get($group, 'max', $min));
            $items = $this->normalizeGroupItems(Arr::get($group, 'items', []));

            $result[] = [
                'name' => $name,
                'choose_min' => max(0, $min),
                'choose_max' => max(1, $max),
                'items' => $items,
            ];
        }

        return $result;
    }

    protected function normalizeGroupItems(mixed $items): array
    {
        if (is_string($items)) {
            return $this->parseGroupItemsString($items);
        }

        if (! is_array($items)) {
            return [];
        }

        $result = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $result = array_merge($result, $this->parseGroupItemsString($item));
                continue;
            }

            if (is_numeric($item)) {
                $result[] = [
                    'product_id' => (int) $item,
                    'variation_id' => null,
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $productId = (int) Arr::get($item, 'product_id', Arr::get($item, 'product', 0));
            $variationId = (int) Arr::get($item, 'variation_id', Arr::get($item, 'variation', 0));

            if ($productId <= 0) {
                continue;
            }

            $result[] = [
                'product_id' => $productId,
                'variation_id' => $variationId ?: null,
            ];
        }

        return $result;
    }

    protected function parseGroupItemsString(string $items): array
    {
        $items = trim($items);

        if ($items === '') {
            return [];
        }

        $result = [];
        $parts = preg_split('/[,]+/', $items);

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, ':')) {
                [$part] = explode(':', $part, 2);
                $part = trim((string) $part);
            }

            $variationId = null;
            $productPart = $part;

            if (str_contains($productPart, '@')) {
                [$productPart, $variationValue] = explode('@', $productPart, 2);
                $variationId = (int) trim($variationValue);
            }

            $productId = (int) trim($productPart);

            if ($productId > 0) {
                $result[] = [
                    'product_id' => $productId,
                    'variation_id' => $variationId ?: null,
                ];
            }
        }

        return $result;
    }

    protected function decodeJson(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || (! Str::startsWith($value, '[') && ! Str::startsWith($value, '{'))) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    protected function findBundle(array $row): ?Bundle
    {
        $id = (int) Arr::get($row, 'id', 0);

        if ($id > 0) {
            return Bundle::query()->find($id);
        }

        $slug = trim((string) Arr::get($row, 'slug'));

        if ($slug !== '') {
            return Bundle::query()->where('slug', $slug)->first();
        }

        $name = trim((string) Arr::get($row, 'name'));

        if ($name === '') {
            return null;
        }

        return Bundle::query()->where('name', $name)->first();
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
