<?php

namespace SparroWave\FarmartHelper\Supports;

final class CheckoutAmounts
{
    // Preserve the existing one-paise gateway-creation/discount tolerance.
    // Verified receipt checks remain exact in their owning payment plugins.
    public const TOLERANCE_PAISE = 1;

    public static function paise(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
