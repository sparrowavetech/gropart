<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Tables\PointTransactionsTable;

class PointTransactionController extends BaseLoyaltyController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.transaction.menu_name'), route('loyalty-points.transactions.index'));
    }

    public function index(PointTransactionsTable $table, int|string|null $id = null)
    {
        if ($id) {
            $customer = Customer::query()->findOrFail($id);
            $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.transaction.history_for', ['name' => $customer->name]));
            $table->forCustomer($id);
        } else {
            $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.transaction.all_transactions'));
        }

        return $table->renderTable();
    }
}
