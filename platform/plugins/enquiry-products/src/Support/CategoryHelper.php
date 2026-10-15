<?php
namespace SparroWave\EnquiryProducts\Support;

use Botble\Ecommerce\Supports\ProductCategoryHelper;
use Illuminate\Support\Collection;

class CategoryHelper extends ProductCategoryHelper
{
    public function getProductCategoriesWithUrl(array $categoryIds = [], array $condition = [], ?int $limit = null): Collection
    {
        if (app(Catalog::class)->isStorefront()) {
            // A separate cache key prevents admin-built category caches leaking into storefront widgets.
            $condition[] = ['ec_product_categories.is_enquiry', '!=', 1];
        }
        return parent::getProductCategoriesWithUrl($categoryIds, $condition, $limit);
    }
}
