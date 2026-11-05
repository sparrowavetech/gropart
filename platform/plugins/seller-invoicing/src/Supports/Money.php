<?php

namespace SparroWave\SellerInvoicing\Supports;

use InvalidArgumentException;

final class Money
{
    public static function toMinor(string|int $amount, int $digits): int
    {
        if ($digits < 0 || $digits > 4) {
            throw new InvalidArgumentException('Unsupported currency precision.');
        }

        $value = trim((string) $amount);
        if (! preg_match('/^(-?)(\d+)(?:\.(\d+))?$/D', $value, $parts)) {
            throw new InvalidArgumentException('Invalid decimal amount.');
        }

        $fraction = $parts[3] ?? '';
        if (strlen($fraction) > $digits && trim(substr($fraction, $digits), '0') !== '') {
            throw new InvalidArgumentException('Amount exceeds currency precision.');
        }

        $whole = ltrim($parts[2], '0') ?: '0';
        $minorText = ltrim($whole . str_pad(substr($fraction, 0, $digits), $digits, '0'), '0') ?: '0';
        $limit = (string) PHP_INT_MAX;
        if (strlen($minorText) > strlen($limit) || (strlen($minorText) === strlen($limit) && strcmp($minorText, $limit) > 0)) {
            throw new InvalidArgumentException('Amount exceeds supported range.');
        }

        $minor = (int) $minorText;

        return $parts[1] === '-' ? -$minor : $minor;
    }

    public static function fromMinor(int $minor, int $digits): string
    {
        if ($digits < 0 || $digits > 4 || $minor === PHP_INT_MIN) {
            throw new InvalidArgumentException('Unsupported amount or currency precision.');
        }

        $sign = $minor < 0 ? '-' : '';
        $absolute = (string) abs($minor);
        if ($digits === 0) {
            return $sign . $absolute;
        }

        $padded = str_pad($absolute, $digits + 1, '0', STR_PAD_LEFT);

        return $sign . substr($padded, 0, -$digits) . '.' . substr($padded, -$digits);
    }
}
