<?php

use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Illuminate\Support\Collection;

if (! function_exists('get_product_categories')) {
    /**
     * @deprecated
     */
    function get_product_categories(): Collection
    {
        return ProductCategoryHelper::getAllProductCategories();
    }
}

if (! function_exists('get_product_categories_withparent')) {
    /**
     * @deprecated
     */
    function get_product_categories_withparent(array $params = [], bool $onlyParent = false): Collection
    {
        return ProductCategoryHelper::getAllProductCategories($params, $onlyParent);
    }
}

if (! function_exists('get_product_categories_with_children')) {
    /**
     * @deprecated
     */
    function get_product_categories_with_children(): array
    {
        return ProductCategoryHelper::getAllProductCategoriesWithChildren();
    }
}
