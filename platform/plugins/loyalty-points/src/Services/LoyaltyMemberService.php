<?php

namespace Botble\LoyaltyPoints\Services;

use Botble\Ecommerce\Models\Customer;

class LoyaltyMemberService
{
    public function __construct(protected LoyaltyCardService $loyaltyCardService)
    {
    }

    public function parseAndValidate(string $input): ?Customer
    {
        $input = trim($input);

        if (empty($input)) {
            return null;
        }

        // Try L-format first: L000000123
        if (preg_match('/^L(\d{1,9})$/i', $input, $matches)) {
            $customerId = (int) ltrim($matches[1], '0');

            if ($customerId > 0) {
                return Customer::query()->find($customerId);
            }

            return null;
        }

        // Try QR format: LOYALTY:123:hash
        $customerId = $this->loyaltyCardService->validateQrContent($input);

        if ($customerId) {
            return Customer::query()->find($customerId);
        }

        // Try phone number lookup
        if ($this->looksLikePhone($input)) {
            return $this->findCustomerByPhone($input);
        }

        return null;
    }

    protected function findCustomerByPhone(string $input): ?Customer
    {
        // Clean input - remove spaces, dashes, parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $input);

        // Try exact match first
        $customer = Customer::query()->where('phone', $cleaned)->first();

        if ($customer) {
            return $customer;
        }

        // Try with + prefix if not present
        if (! str_starts_with($cleaned, '+')) {
            $customer = Customer::query()->where('phone', '+' . $cleaned)->first();

            if ($customer) {
                return $customer;
            }
        }

        // Try without + prefix if present
        if (str_starts_with($cleaned, '+')) {
            $customer = Customer::query()->where('phone', ltrim($cleaned, '+'))->first();

            if ($customer) {
                return $customer;
            }
        }

        // Try matching last 9-10 digits (local number without country code)
        $digitsOnly = preg_replace('/\D/', '', $cleaned);

        if (strlen($digitsOnly) >= 9) {
            $lastDigits = substr($digitsOnly, -10);
            $customer = Customer::query()
                ->whereRaw('RIGHT(REPLACE(REPLACE(phone, "+", ""), "-", ""), 10) = ?', [$lastDigits])
                ->first();

            if ($customer) {
                return $customer;
            }
        }

        return null;
    }

    public function formatMemberId(Customer|int $customer): string
    {
        $id = $customer instanceof Customer ? $customer->id : $customer;

        return sprintf('L%09d', $id);
    }

    public function isValidFormat(string $input): bool
    {
        $input = trim($input);

        // Check L-format
        if (preg_match('/^L(\d{1,9})$/i', $input)) {
            return true;
        }

        // Check QR format
        if (preg_match('/^LOYALTY:\d+:[a-f0-9]{8}$/i', $input)) {
            return true;
        }

        // Check phone format
        if ($this->looksLikePhone($input)) {
            return true;
        }

        return false;
    }

    protected function looksLikePhone(string $input): bool
    {
        // Remove common phone formatting characters
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $input);

        // Check if it starts with + or digit and has 8-15 digits total
        if (preg_match('/^\+?\d{8,15}$/', $cleaned)) {
            return true;
        }

        return false;
    }
}
