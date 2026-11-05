<?php

namespace SparroWave\SellerInvoicing\Supports;

use InvalidArgumentException;

final class RevenueReconciler
{
    /** @return array{status: string, difference_minor: int|null, reason: string|null} */
    public function assess(array $amounts, int $digits): array
    {
        foreach (['seller_merchandise_base', 'existing_marketplace_fee', 'existing_seller_net'] as $key) {
            if (! array_key_exists($key, $amounts)) {
                return ['status' => 'needs_review', 'difference_minor' => null, 'reason' => 'missing_amount'];
            }
        }

        try {
            $base = Money::toMinor((string) $amounts['seller_merchandise_base'], $digits);
            $fee = Money::toMinor((string) $amounts['existing_marketplace_fee'], $digits);
            $net = Money::toMinor((string) $amounts['existing_seller_net'], $digits);
        } catch (InvalidArgumentException $exception) {
            return ['status' => 'needs_review', 'difference_minor' => null, 'reason' => 'invalid_precision'];
        }

        if ($base < 0 || $fee < 0 || $net < 0) {
            return ['status' => 'needs_review', 'difference_minor' => null, 'reason' => 'negative_amount'];
        }

        // Avoid an integer overflow when handling an untrusted historical record.
        if ($fee > PHP_INT_MAX - $net) {
            return ['status' => 'needs_review', 'difference_minor' => null, 'reason' => 'amount_out_of_range'];
        }

        $difference = $base - ($fee + $net);

        return [
            'status' => $difference === 0 ? 'balanced' : 'needs_review',
            'difference_minor' => $difference,
            'reason' => $difference === 0 ? null : 'revenue_mismatch',
        ];
    }
}
