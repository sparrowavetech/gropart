<?php
namespace SparroWave\EnquiryProducts\Support;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Catalog
{
    public const SCOPE = 'sparrowave_enquiry_catalog';

    // Only storefront discovery queries. Never hide records from administration,
    // vendor management, customer order history, checkout, or background jobs.
    public function isStorefront(): bool
    {
        $request = request();
        $name = $request->route()?->getName() ?? '';
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD') && $name !== 'public.ajax.render-ui-block') {
            return false;
        }
        $action = $request->route()?->getActionName() ?? '';
        foreach (['ProductController', 'ProductCategoryController', 'BrandController', 'FilterController', 'FlashSaleController'] as $controller) {
            if (str_starts_with($action, 'Botble\\Ecommerce\\Http\\Controllers\\API\\' . $controller . '@')) {
                return true;
            }
        }
        if (! str_starts_with($name, 'public.')) {
            return false;
        }
        foreach (['cart', 'checkout', 'order', 'customer', 'account', 'download', 'wishlist', 'compare', 'tracking', 'invoice', 'payment'] as $part) {
            if (str_contains($name, $part)) {
                return false;
            }
        }
        return true;
    }

    public function constrain(Builder $query, bool $enquiry): Builder
    {
        $table = $query->getModel()->getTable();
        return $query->where(function (Builder $group) use ($table, $enquiry): void {
            if ($enquiry) {
                $group->where($table . '.is_enquiry', 1)->orWhereExists($this->categoryMatch($table));
            } else {
                $group->where(function (Builder $flags) use ($table): void {
                    $flags->whereNull($table . '.is_enquiry')->orWhere($table . '.is_enquiry', 0);
                })->whereNotExists($this->categoryMatch($table));
            }
        });
    }

    private function categoryMatch(string $table): \Closure
    {
        // Query builder intentionally avoids the public category scope here.
        return static function ($query) use ($table): void {
            $query->selectRaw('1')->from('ec_product_category_product as ep_pivot')
                ->join('ec_product_categories as ep_category', 'ep_category.id', '=', 'ep_pivot.category_id')
                ->whereColumn('ep_pivot.product_id', $table . '.id')->where('ep_category.is_enquiry', 1);
        };
    }

    public function products(): Builder
    {
        return $this->constrain(Product::withoutGlobalScope(self::SCOPE), true)
            ->wherePublished()->where('is_variation', 0);
    }

    public function categories()
    {
        // Include categories attached to enquiry products as well as explicitly flagged categories.
        $categories = ProductCategory::withoutGlobalScope(self::SCOPE)->wherePublished()
            ->where(function (Builder $query): void {
                $query->where('is_enquiry', 1)->orWhereHas('products', function (Builder $products): void {
                    $products->withoutGlobalScope(self::SCOPE)->wherePublished()->where('is_variation', 0);
                    $this->constrain($products, true);
                });
            })->orderBy('order')->orderBy('name')->get();
        $tree = ProductCategory::withoutGlobalScope(self::SCOPE)->wherePublished()
            ->orderBy('order')->orderBy('name')->get()->keyBy('id');
        $included = array_fill_keys($categories->modelKeys(), true);
        // All levels below enquiry categories are selectable, even without direct products.
        do {
            $previous = count($included);
            foreach ($tree as $node) {
                if (isset($included[$node->parent_id])) {
                    $included[$node->id] = true;
                }
            }
        } while (count($included) > $previous);
        // Include ancestors for navigating to enquiry products in nested categories.
        foreach (array_keys($included) as $id) {
            $parent = $tree[$id]->parent_id;
            $seen = [$id => true];
            while ($parent && isset($tree[$parent]) && ! isset($seen[$parent])) {
                $seen[$parent] = true;
                $included[$parent] = true;
                $parent = $tree[$parent]->parent_id;
            }
        }
        $result = new \Illuminate\Database\Eloquent\Collection();
        $visited = [];
        $children = $tree->groupBy('parent_id');
        $append = function ($node, array $path = []) use (&$append, &$visited, $children, $included, $result): void {
            if (isset($visited[$node->id]) || ! isset($included[$node->id])) {
                return;
            }
            $visited[$node->id] = true;
            $path[] = $node->name;
            $node->setAttribute('enquiry_label', implode(' / ', $path));
            $result->push($node);
            foreach ($children->get($node->id, collect()) as $child) {
                $append($child, $path);
            }
        };
        foreach ($tree as $node) {
            if (! $node->parent_id || ! isset($tree[$node->parent_id])) {
                $append($node);
            }
        }
        // Keep malformed legacy cycles finite and their categories accessible.
        foreach ($tree as $node) {
            $append($node);
        }
        return $result;
    }

    public function isEnquiry(Product $product): bool
    {
        $id = $product->getKey();
        if ($product->is_variation) {
            $id = DB::table('ec_product_variations')->where('product_id', $id)->value('configurable_product_id') ?: $id;
        }
        return $this->constrain(Product::withoutGlobalScope(self::SCOPE)->whereKey($id), true)->exists();
    }

    public function listing(?string $search = null, ?int $category = null): Builder
    {
        $query = $this->products()->with('slugable');
        if ($search !== null && trim($search) !== '') {
            // Escape LIKE metacharacters: a user's % or _ is a literal name character.
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($search));
            $query->whereRaw("ec_products.name LIKE ? ESCAPE '!'", ['%' . $term . '%']);
        }
        if ($category) {
            $ids = [$category];
            $tree = ProductCategory::withoutGlobalScope(self::SCOPE)->wherePublished()->get(['id', 'parent_id']);
            do {
                $previous = count($ids);
                foreach ($tree as $child) {
                    if (in_array($child->parent_id, $ids) && ! in_array($child->id, $ids)) { $ids[] = $child->id; }
                }
            } while (count($ids) > $previous);
            $query->whereHas('categories', fn (Builder $q) => $q->withoutGlobalScope(self::SCOPE)->whereKey($ids));
        }
        return $query->orderBy('ec_products.order')->orderByDesc('ec_products.id');
    }
}
