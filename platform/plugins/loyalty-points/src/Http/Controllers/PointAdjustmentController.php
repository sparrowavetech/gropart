<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\LoyaltyPoints\Http\Requests\PointAdjustmentRequest;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class PointAdjustmentController extends BaseLoyaltyController
{
    public function __construct(protected LoyaltyPointService $loyaltyPointService)
    {
        parent::__construct();
    }

    public function store(PointAdjustmentRequest $request)
    {
        $memberId = $request->input('member_id');
        $member = CustomerPointBalance::query()->findOrFail($memberId);

        $points = (int) $request->input('points');
        $type = $request->input('adjustment_type');
        $note = $request->input('note');

        if ($type === 'deduct') {
            $points = -abs($points);
        }

        $this->loyaltyPointService->adjustPoints($member->customer_id, $points, $note);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/loyalty-points::loyalty-points.adjustment.success'));
    }
}
