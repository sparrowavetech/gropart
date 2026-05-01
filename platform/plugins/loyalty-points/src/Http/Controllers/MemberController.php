<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Supports\Breadcrumb;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Tables\MembersTable;
use Botble\LoyaltyPoints\Tables\PointTransactionsTable;

class MemberController extends BaseLoyaltyController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.members.menu_name'), route('loyalty-points.members.index'));
    }

    public function index(MembersTable $table)
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.members.menu_name'));

        return $table->renderTable();
    }

    public function show(int|string $id, PointTransactionsTable $transactionsTable)
    {
        $member = CustomerPointBalance::query()
            ->with(['customer:id,name,email,phone', 'level'])
            ->findOrFail($id);

        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.members.show', ['name' => $member->customer?->name]));

        $transactionsTable->forCustomer($member->customer_id);

        if (request()->has('draw')) {
            return $transactionsTable->render('core/table::base-table');
        }

        return view('plugins/loyalty-points::members.show', compact('member', 'transactionsTable'));
    }
}
