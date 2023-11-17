<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Widget\AbstractWidget;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductCategoriesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Product Categories'),
            'description' => __('List of product categories'),
            'categories' => [],
        ]);
    }

    protected function data(): array|Collection
    {
        $categoryIds = $this->getConfig('categories');

        if (empty($categoryIds) || ! is_plugin_active('ecommerce')) {
            return [
                'categories' => [],
            ];
        }

        $categories = ProductCategory::query()
            ->toBase()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->whereIn('ec_product_categories.id', $categoryIds)
            ->select([
                'ec_product_categories.id',
                'ec_product_categories.name',
                DB::raw('CONCAT(slugs.prefix, "/", slugs.key) as url'),
            ])
            ->leftJoin('slugs', function (JoinClause $join) {
                $join
                    ->on('slugs.reference_id', 'ec_product_categories.id')
                    ->where('slugs.reference_type', ProductCategory::class);
            })
            ->orderByDesc('ec_product_categories.created_at')
            ->orderBy('ec_product_categories.order')
            ->get();

        return [
            'categories' => $categories,
        ];
    }
}
