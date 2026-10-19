<?php

namespace SparroWave\AdvancedCod\Supports;

use InvalidArgumentException;

/** Currency arithmetic shared by checkout and verified payment allocation. */
class CodAmounts
{
    public static function split(float $total, float $percentage): array
    {
        if (! is_finite($total) || $total < 0 || ! is_finite($percentage) || $percentage <= 0 || $percentage >= 100) {
            throw new InvalidArgumentException('Invalid COD total or advance percentage.');
        }

        $totalPaise = (int) round($total * 100);
        $cashPaise = min((int) floor($totalPaise / 100) * 100, (int) round($totalPaise * (100 - $percentage) / 10000) * 100);
        $advancePaise = $totalPaise - $cashPaise;

        return ['total' => $totalPaise / 100, 'advance' => $advancePaise / 100, 'remaining' => ($totalPaise - $advancePaise) / 100];
    }

    /** Allocate a checkout-wide advance without duplicating it across vendor orders. */
    public static function allocate(array $totals, float $percentage): array
    {
        // Each vendor shipment collects whole cash; online payment is the exact residual.
        return array_map(fn ($total) => self::split((float)$total, $percentage), $totals);
    }
}
