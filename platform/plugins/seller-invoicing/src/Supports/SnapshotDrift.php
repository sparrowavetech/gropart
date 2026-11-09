<?php

namespace SparroWave\SellerInvoicing\Supports;

use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use InvalidArgumentException;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;

final class SnapshotDrift
{
    public function status(OrderSnapshot $snapshot): string
    {
        $revenue = Revenue::query()
            ->whereKey($snapshot->revenue_id)
            ->where('order_id', $snapshot->order_id)
            ->where('customer_id', $snapshot->seller_id)
            ->where(function ($q) {
                $q->where('type', RevenueTypeEnum::ADD_AMOUNT)
                    ->orWhereNull('type')
                    ->orWhere('type', '');
            })
            ->first();

        if (! $revenue) {
            return 'missing';
        }

        if ((string) $revenue->currency !== $snapshot->currency) {
            return 'changed';
        }

        foreach ([
            'seller_merchandise_base' => 'sub_amount',
            'existing_marketplace_fee' => 'fee',
            'existing_seller_net' => 'amount',
        ] as $snapshotField => $revenueField) {
            try {
                $stored = Money::toMinor((string) ($snapshot->amounts[$snapshotField] ?? ''), $snapshot->currency_digits);
                $current = Money::toMinor((string) $revenue->{$revenueField}, $snapshot->currency_digits);
            } catch (InvalidArgumentException) {
                return 'invalid';
            }

            if ($stored !== $current) {
                return 'changed';
            }
        }

        return 'unchanged';
    }
}
