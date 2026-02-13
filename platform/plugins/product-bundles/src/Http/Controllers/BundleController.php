<?php

namespace Botble\ProductBundles\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Events\DeletedContentEvent;
use Botble\ProductBundles\Http\Requests\BundleRequest;
use Botble\ProductBundles\Models\Bundle;
use Botble\ProductBundles\Models\BundleGroup;
use Botble\ProductBundles\Models\BundleGroupItem;
use Botble\ProductBundles\Models\BundleItem;
use Botble\ProductBundles\Tables\BundleTable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BundleController extends BaseController
{
    public function index(BundleTable $dataTable)
    {
        $this->pageTitle(trans('plugins/product-bundles::bundles.list.title'));

        return $dataTable->renderTable();
    }

    public function create()
    {
        return redirect()->route('products.create', ['bundle' => 1]);
    }

    public function store(BundleRequest $request)
    {
        return $this->persist($request);
    }

    public function edit(Bundle $bundle)
    {
        if ($bundle->ecommerce_product_id) {
            return redirect()->route('products.edit', $bundle->ecommerce_product_id);
        }

        $bundle->load(['items.product', 'groups.items.product', 'products']);

        return view('plugins/product-bundles::admin.form', compact('bundle'));
    }

    public function update(Bundle $bundle, BundleRequest $request)
    {
        return $this->persist($request, $bundle);
    }

    public function destroy(Bundle $bundle): DeleteResourceAction
    {
        return DeleteResourceAction::make($bundle)
            ->deleteUsing(function (DeleteResourceAction $action) use ($bundle) {
                $bundle->items()->delete();
                $bundle->groups()->each(function (BundleGroup $group) {
                    $group->items()->delete();
                });
                $bundle->groups()->delete();
                $bundle->products()->detach();
                $bundle->delete();

                DeletedContentEvent::dispatch($bundle::class, $action->getRequest(), $bundle);

                $action->getHttpResponse()->withDeletedSuccessMessage();
            });

    }

    /**
     * Select2 AJAX endpoint to search products by name or #ID.
     */
    public function ajaxProducts(Request $request)
    {
        $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
            ? 'Botble\\Ecommerce\\Models\\Product'
            : (class_exists('App\\Models\\Product') ? 'App\\Models\\Product' : null);

        if (! $productClass) {
            return response()->json(['results' => [], 'pagination' => ['more' => false]]);
        }

        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        // Whether to include variations (if the Product model hides them behind a global scope).
        // 0: only base products, 1: include variations.
        $includeVariations = (bool) (int) $request->input('include_variations', 0);

        $query = $productClass::query();

        // Try to remove any global scopes so variations can be searched.
        try {
            if (method_exists($query, 'withoutGlobalScopes')) {
                $query->withoutGlobalScopes();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // If the products table has an is_variation column, optionally exclude variations.
        try {
            $table = (new $productClass())->getTable();
            if (class_exists('Illuminate\\Support\\Facades\\Schema') && \Illuminate\Support\Facades\Schema::hasColumn($table, 'is_variation')) {
                if (! $includeVariations) {
                    $query->where('is_variation', 0);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        if ($q !== '') {
            $numeric = preg_match('/^#?\d+$/', $q) ? (int) ltrim($q, '#') : null;

            $query->where(function ($sub) use ($q, $numeric) {
                $sub->where('name', 'LIKE', '%' . $q . '%');

                if ($numeric) {
                    $sub->orWhere('id', $numeric);
                }
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $results = $products->getCollection()->map(function ($p) {
            $name = (string) ($p->name ?? '');
            $name = $name !== '' ? $name : ('#' . $p->id);

            $isVariation = false;
            if (isset($p->is_variation)) {
                $isVariation = (bool) $p->is_variation;
            } elseif (method_exists($p, 'isVariation')) {
                try {
                    $isVariation = (bool) $p->isVariation();
                } catch (\Throwable $e) {
                    $isVariation = false;
                }
            }

            $prefix = $isVariation ? '[' . trans('plugins/product-bundles::bundles.form.labels.variation') . '] ' : '';

            return [
                'id' => (int) $p->id,
                'text' => $prefix . '#' . $p->id . ' - ' . Str::limit($name, 80),
            ];
        })->values()->all();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $products->hasMorePages(),
            ],
        ]);
    }

    public function ajaxPricePreview(Request $request)
    {
        $type = (string) $request->input('type', 'fixed');
        if (! in_array($type, ['fixed', 'mix'], true)) {
            $type = 'fixed';
        }

        $pricingType = (string) $request->input('pricing_type', 'percent_off');
        if (! in_array($pricingType, ['fixed_total', 'percent_off', 'amount_off'], true)) {
            $pricingType = 'percent_off';
        }

        $pricingValue = (float) $request->input('pricing_value', 0);

        $items = (array) $request->input('fixed_items', []);
        $groups = (array) $request->input('mix_groups', []);

        $ids = [];
        if ($type === 'fixed') {
            foreach ($items as $row) {
                $pid = (int) Arr::get($row, 'product_id', 0);
                if ($pid > 0) {
                    $ids[] = $pid;
                }
            }
        } else {
            foreach ($groups as $gRow) {
                foreach ((array) Arr::get($gRow, 'items', []) as $iRow) {
                    $pid = (int) Arr::get($iRow, 'product_id', 0);
                    if ($pid > 0) {
                        $ids[] = $pid;
                    }
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        $hasItems = ! empty($ids);

        $sumBase = 0.0;
        $priceMap = [];

        $productClass = function_exists('pb_product_model_class')
            ? pb_product_model_class()
            : (class_exists('Botble\\Ecommerce\\Models\\Product')
                ? 'Botble\\Ecommerce\\Models\\Product'
                : (class_exists('App\\Models\\Product') ? 'App\\Models\\Product' : null));

        if ($productClass && $hasItems) {
            $query = $productClass::query();
            try {
                if (method_exists($query, 'withoutGlobalScopes')) {
                    $query->withoutGlobalScopes();
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $products = $query->whereIn('id', $ids)->get();
            foreach ($products as $p) {
                $priceMap[(int) $p->id] = function_exists('pb_guess_product_price')
                    ? pb_guess_product_price($p)
                    : (float) ($p->price ?? 0);
            }
        }

        if ($hasItems) {
            if ($type === 'fixed') {
                foreach ($items as $row) {
                    $pid = (int) Arr::get($row, 'product_id', 0);
                    if ($pid <= 0) {
                        continue;
                    }

                    $qty = max(1, (int) Arr::get($row, 'quantity', 1));
                    $sumBase += (float) ($priceMap[$pid] ?? 0) * $qty;
                }
            } else {
                foreach ($groups as $gRow) {
                    $min = max(0, (int) Arr::get($gRow, 'choose_min', 0));
                    if ($min <= 0) {
                        continue;
                    }

                    $prices = [];
                    foreach ((array) Arr::get($gRow, 'items', []) as $iRow) {
                        $pid = (int) Arr::get($iRow, 'product_id', 0);
                        if ($pid <= 0) {
                            continue;
                        }
                        $prices[] = (float) ($priceMap[$pid] ?? 0);
                    }

                    sort($prices);
                    $sumBase += array_sum(array_slice($prices, 0, $min));
                }
            }
        }

        $final = $sumBase;
        if ($pricingType === 'fixed_total') {
            $final = max(0.0, $pricingValue);
        } elseif ($pricingType === 'percent_off') {
            $pct = max(0.0, min(100.0, $pricingValue));
            $final = max(0.0, $sumBase * (1 - $pct / 100));
        } elseif ($pricingType === 'amount_off') {
            $final = max(0.0, $sumBase - max(0.0, $pricingValue));
        }

        $discount = max(0.0, $sumBase - $final);

        $format = function (float $amount): string {
            if (function_exists('pb_format_price')) {
                return pb_format_price($amount);
            }

            return number_format($amount, 0, '.', ',');
        };

        return response()->json([
            'has_items' => $hasItems,
            'base_total' => $sumBase,
            'discount_total' => $discount,
            'final_total' => $final,
            'base_total_formatted' => $format($sumBase),
            'discount_total_formatted' => $format($discount),
            'final_total_formatted' => $format($final),
            'note' => $type === 'mix' && $hasItems
                ? trans('plugins/product-bundles::bundles.form.price_summary.estimate_note')
                : '',
            'empty' => trans('plugins/product-bundles::bundles.form.price_summary.empty'),
        ]);
    }

    protected function persist(BundleRequest $request, ?Bundle $bundle = null)
    {
        $data = $request->validated();

        $productIds = array_values(array_filter(array_map('intval', (array) ($data['attached_product_ids'] ?? []))));
        unset($data['attached_product_ids']);

        $items = $request->input('fixed_items', []);
        $groups = $request->input('mix_groups', []);

        DB::transaction(function () use (&$bundle, $data, $productIds, $items, $groups) {
            if (! $bundle) {
                $bundle = Bundle::query()->create($data);
            } else {
                $bundle->fill($data)->save();
                $bundle->items()->delete();
                $bundle->groups()->each(function (BundleGroup $group) {
                    $group->items()->delete();
                });
                $bundle->groups()->delete();
            }

            $bundle->products()->sync($productIds);

            if ($bundle->type === 'fixed') {
                foreach ($items as $idx => $row) {
                    $pid = (int) Arr::get($row, 'product_id', 0);
                    $qty = (int) Arr::get($row, 'quantity', 1);

                    if ($pid <= 0 || $qty <= 0) {
                        continue;
                    }

                    BundleItem::query()->create([
                        'bundle_id' => $bundle->id,
                        'product_id' => $pid,
                        'variation_id' => Arr::get($row, 'variation_id') ?: null,
                        'quantity' => $qty,
                        'sort_order' => $idx,
                    ]);
                }
            } else {
                foreach ($groups as $gIdx => $gRow) {
                    $gName = trim((string) Arr::get($gRow, 'name'));
                    if ($gName === '') {
                        continue;
                    }

                    $group = BundleGroup::query()->create([
                        'bundle_id' => $bundle->id,
                        'name' => $gName,
                        'choose_min' => max(0, (int) Arr::get($gRow, 'choose_min', 1)),
                        'choose_max' => max(1, (int) Arr::get($gRow, 'choose_max', 1)),
                        'sort_order' => $gIdx,
                    ]);

                    $gItems = Arr::get($gRow, 'items', []);
                    foreach ($gItems as $iIdx => $iRow) {
                        $pid = (int) Arr::get($iRow, 'product_id', 0);
                        if ($pid <= 0) {
                            continue;
                        }

                        BundleGroupItem::query()->create([
                            'group_id' => $group->id,
                            'product_id' => $pid,
                            'variation_id' => Arr::get($iRow, 'variation_id') ?: null,
                            'sort_order' => $iIdx,
                        ]);
                    }
                }
            }
        });

        // Create or update the hidden purchasable product representing this bundle.
        try {
            app('product-bundles')->syncBundleProduct($bundle);
        } catch (\Throwable $e) {
            // ignore
        }

        return redirect()->route('product-bundles.edit', $bundle)->with('success_msg', trans('plugins/product-bundles::bundles.saved'));
    }
}
