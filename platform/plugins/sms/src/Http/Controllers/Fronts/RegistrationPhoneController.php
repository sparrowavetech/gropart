<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Rules\UniquePhoneRule;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationPhoneController extends BaseController
{
    public function check(Request $request): JsonResponse
    {
        abort_unless(EcommerceHelper::isCustomerRegistrationEnabled(), 404);

        $request->validate([
            'phone' => ['nullable', 'string', 'max:40'],
            'phone_display' => ['nullable', 'string', 'max:40'],
        ]);

        // Match RegisterRequest's HasPhoneFieldValidation preference for the full international number.
        $phone = (string) $request->input('phone', '');
        $display = (string) $request->input('phone_display', '');
        $selected = $display && $phone && str_starts_with($phone, '+') ? $phone : ($phone ?: $display);
        $normalized = preg_replace('/[^\d+]/', '', $selected);

        if (! $normalized || strlen($normalized) > 20) {
            return response()->json(['available' => false], 422);
        }

        $validator = Validator::make(
            ['phone' => $normalized],
            ['phone' => [UniquePhoneRule::make(Customer::class)]]
        );

        return response()->json(['available' => ! $validator->fails()]);
    }
}
