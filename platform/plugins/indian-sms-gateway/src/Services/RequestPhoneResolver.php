<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Http\Request;

class RequestPhoneResolver
{
    public function resolve(Request $request): ?string
    {
        $keys = [
            'phone',
            'mobile',
            'mobile_number',
            'customer_phone',
            'address.phone',
            'shipping_address.phone',
            'billing_address.phone',
            'shippingAddress.phone',
            'billingAddress.phone',
        ];

        foreach ($keys as $key) {
            $value = $request->input($key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        foreach ($request->all() as $key => $value) {
            if (is_string($value) && str_contains(strtolower((string) $key), 'phone') && trim($value) !== '') {
                return trim($value);
            }

            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if (is_string($nestedValue) && str_contains(strtolower((string) $nestedKey), 'phone') && trim($nestedValue) !== '') {
                        return trim($nestedValue);
                    }
                }
            }
        }

        return null;
    }
}
