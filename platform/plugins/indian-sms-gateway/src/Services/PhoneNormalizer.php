<?php

namespace Ashikul\IndiaSmsGateway\Services;

use InvalidArgumentException;

class PhoneNormalizer
{
    public function normalize(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: '';

        if (str_starts_with($phone, '0091')) {
            $phone = substr($phone, 2);
        }

        if (preg_match('/^[6-9]\d{9}$/', $phone)) {
            $phone = '91' . $phone;
        }

        if (! preg_match((string) config('india-sms.phone_regex', '/^91[6-9]\d{9}$/'), $phone)) {
            throw new InvalidArgumentException('Please enter a valid Indian mobile number.');
        }

        return $phone;
    }

    public function variants(string $phone): array
    {
        $canonical = $this->normalize($phone);

        return array_values(array_unique([
            $canonical,
            '+' . $canonical,
            substr($canonical, 2),
            '0' . substr($canonical, 2),
        ]));
    }
}
