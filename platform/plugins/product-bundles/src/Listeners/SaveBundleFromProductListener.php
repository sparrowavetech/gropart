<?php

namespace Botble\ProductBundles\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\ProductBundles\Models\Bundle;
use Botble\ProductBundles\Models\BundleGroup;
use Botble\ProductBundles\Models\BundleGroupItem;
use Botble\ProductBundles\Models\BundleItem;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaveBundleFromProductListener
{
    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        if ($event->screen !== PRODUCT_MODULE_SCREEN_NAME) {
            return;
        }

        $product = $event->data;

        if (! $product instanceof Product || $product->is_variation) {
            return;
        }

        $request = $event->request;

        if (! $request->has('pb_is_bundle')) {
            return;
        }

        $isBundle = (bool) $request->input('pb_is_bundle', false);

        $bundle = Bundle::query()
            ->where('ecommerce_product_id', $product->getKey())
            ->first();

        if (! $isBundle) {
            if ($bundle) {
                $bundle->is_active = false;
                $bundle->save();
            }

            return;
        }

        $type = (string) $request->input('type', 'fixed');
        if (! in_array($type, ['fixed', 'mix'], true)) {
            $type = 'fixed';
        }

        $pricingType = (string) $request->input('pricing_type', 'percent_off');
        if (! in_array($pricingType, ['fixed_total', 'percent_off', 'amount_off'], true)) {
            $pricingType = 'percent_off';
        }

        $bundle = $bundle ?: new Bundle();

        $bundle->name = (string) ($product->name ?? '');
        $bundle->description = (string) ($product->description ?? '');
        $bundle->image = (string) ($product->image ?? '');
        $bundle->type = $type;
        $bundle->pricing_type = $pricingType;
        $bundle->pricing_value = (float) $request->input('pricing_value', 0);
        $bundle->start_date = $product->start_date;
        $bundle->end_date = $product->end_date;
        $bundle->is_featured = (bool) $product->is_featured;
        $bundle->is_active = (string) $product->status === BaseStatusEnum::PUBLISHED;
        $bundle->ecommerce_product_id = (int) $product->getKey();

        if (! $bundle->created_by) {
            $bundle->created_by = (int) (Auth::id() ?: ($product->created_by_id ?? 0));
        }

        $slug = trim((string) $request->input('slug'));
        if ($slug === '') {
            $slugModel = SlugHelper::getSlug(null, null, Product::class, $product->getKey());
            $slug = $slugModel ? (string) $slugModel->key : '';
        }
        if ($slug !== '') {
            $bundle->slug = $slug;
        }

        $items = $request->input('fixed_items', []);
        $groups = $request->input('mix_groups', []);
        $attachedProductIds = array_values(array_filter(array_map('intval', (array) $request->input('attached_product_ids', []))));

        DB::transaction(function () use ($bundle, $type, $items, $groups, $attachedProductIds): void {
            $bundle->save();

            $bundle->items()->delete();
            $bundle->groups()->each(function (BundleGroup $group): void {
                $group->items()->delete();
            });
            $bundle->groups()->delete();

            if ($attachedProductIds) {
                $bundle->products()->sync($attachedProductIds);
            } else {
                $bundle->products()->detach();
            }

            if ($type === 'fixed') {
                foreach ((array) $items as $idx => $row) {
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

                return;
            }

            foreach ((array) $groups as $gIdx => $gRow) {
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
                foreach ((array) $gItems as $iIdx => $iRow) {
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
        });

        if (function_exists('pb_bundle_total_price')) {
            try {
                $bundle->refresh();
                $finalPrice = pb_bundle_total_price($bundle);

                $product->price = max(0.0, (float) $finalPrice);
                $product->sale_price = null;
                $product->sale_type = 0;
                $product->saveQuietly();
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
