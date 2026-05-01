<?php

namespace Botble\LoyaltyPoints\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Services\LoyaltyCardService;
use Illuminate\Contracts\View\View;

class LoyaltyMemberController extends BaseController
{
    public function __construct(protected LoyaltyCardService $loyaltyCardService)
    {
    }

    public function show(string $token): View
    {
        $customerId = $this->loyaltyCardService->validateMemberToken($token);

        if (! $customerId) {
            abort(404, trans('plugins/loyalty-points::loyalty-points.errors.member_not_found'));
        }

        $customer = Customer::query()->find($customerId);

        if (! $customer) {
            abort(404, trans('plugins/loyalty-points::loyalty-points.errors.member_not_found'));
        }

        $balance = CustomerPointBalance::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['total_points' => 0, 'lifetime_points' => 0]
        );

        $balance->load('level');

        $memberId = 'L' . str_pad($customer->id, 9, '0', STR_PAD_LEFT);

        $pageTitle = trans('plugins/loyalty-points::loyalty-points.member_card.page_title', ['name' => $customer->name]);

        return view('plugins/loyalty-points::themes.member-card', compact('customer', 'balance', 'memberId', 'pageTitle'));
    }
}
