<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Table;

class TopSellingProductsTable extends Table
{
    protected string $table = \Botble\Ecommerce\Tables\Reports\TopSellingProductsTable::class;

    protected string $route = 'ecommerce.report.top-selling-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.top_selling_products');
    }
}
