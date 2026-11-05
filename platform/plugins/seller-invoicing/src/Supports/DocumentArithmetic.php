<?php

namespace SparroWave\SellerInvoicing\Supports;

use DomainException;

/**
 * Validates amounts that have already been rounded and allocated in currency minor units.
 * It does not choose tax rates, classify services, or issue a tax document.
 */
final class DocumentArithmetic
{
    /**
     * @param array<int, array{taxable_minor: int, taxes_minor: array<string, int>, total_minor: int}> $lines
     * @param array{taxable_minor: int, taxes_minor: array<string, int>, total_minor: int} $summary
     */
    public function assertBalanced(array $lines, array $summary): void
    {
        if ($lines === []) {
            throw new DomainException('A document must have at least one line.');
        }

        $taxable = 0;
        $total = 0;
        $taxes = [];

        foreach ($lines as $line) {
            $this->assertAmountShape($line);
            $lineTotal = $line['taxable_minor'];
            foreach ($line['taxes_minor'] as $code => $tax) {
                $lineTotal = $this->add($lineTotal, $tax);
                $taxes[$code] = $this->add($taxes[$code] ?? 0, $tax);
            }

            if ($lineTotal !== $line['total_minor']) {
                throw new DomainException('A document line does not equal its taxable amount plus taxes.');
            }

            $taxable = $this->add($taxable, $line['taxable_minor']);
            $total = $this->add($total, $line['total_minor']);
        }

        $this->assertAmountShape($summary);
        if ($taxable !== $summary['taxable_minor'] || $total !== $summary['total_minor']) {
            throw new DomainException('Document column totals do not equal the sum of lines.');
        }

        if ($taxes !== $summary['taxes_minor']) {
            // Key order is presentation-only; amounts and tax-code set must be identical.
            ksort($taxes);
            $summaryTaxes = $summary['taxes_minor'];
            ksort($summaryTaxes);
            if ($taxes !== $summaryTaxes) {
                throw new DomainException('Document tax columns do not equal the sum of lines.');
            }
        }

        $grand = $summary['taxable_minor'];
        foreach ($summary['taxes_minor'] as $tax) {
            $grand = $this->add($grand, $tax);
        }
        if ($grand !== $summary['total_minor']) {
            throw new DomainException('Document grand total does not equal taxable amount plus taxes.');
        }
    }

    private function assertAmountShape(array $amount): void
    {
        if (! isset($amount['taxable_minor'], $amount['total_minor'], $amount['taxes_minor'])
            || ! is_int($amount['taxable_minor'])
            || ! is_int($amount['total_minor'])
            || ! is_array($amount['taxes_minor'])) {
            throw new DomainException('Invalid document amount shape.');
        }

        foreach ($amount['taxes_minor'] as $code => $tax) {
            if (! is_string($code) || $code === '' || ! is_int($tax)) {
                throw new DomainException('Invalid document tax amount.');
            }
        }
    }

    private function add(int $left, int $right): int
    {
        if (($right > 0 && $left > PHP_INT_MAX - $right)
            || ($right < 0 && $left < PHP_INT_MIN - $right)) {
            throw new DomainException('Document amount exceeds supported range.');
        }

        return $left + $right;
    }
}
