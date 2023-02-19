<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Table;

class RecentOrdersTable extends Table
{
    protected string $table = \Botble\Ecommerce\Tables\Reports\RecentOrdersTable::class;

    protected string $route = 'ecommerce.report.recent-orders';

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.recent_orders');
    }
}
