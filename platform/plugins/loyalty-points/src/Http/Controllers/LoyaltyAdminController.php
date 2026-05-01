<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LoyaltyPoints\Services\LoyaltyMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyAdminController extends BaseController
{
    public function __construct(protected LoyaltyMemberService $loyaltyMemberService)
    {
    }

    public function validateMemberId(Request $request): JsonResponse
    {
        $memberId = $request->input('member_id');

        if (empty($memberId)) {
            return response()->json([
                'valid' => false,
                'message' => trans('plugins/loyalty-points::loyalty-points.member_id.empty'),
            ]);
        }

        if (! $this->loyaltyMemberService->isValidFormat($memberId)) {
            return response()->json([
                'valid' => false,
                'message' => trans('plugins/loyalty-points::loyalty-points.member_id.invalid_format'),
            ]);
        }

        $customer = $this->loyaltyMemberService->parseAndValidate($memberId);

        if (! $customer) {
            return response()->json([
                'valid' => false,
                'message' => trans('plugins/loyalty-points::loyalty-points.member_id.not_found'),
            ]);
        }

        return response()->json([
            'valid' => true,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'message' => trans('plugins/loyalty-points::loyalty-points.member_id.valid', [
                'name' => $customer->name,
            ]),
        ]);
    }
}
