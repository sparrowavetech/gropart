<?php

namespace SparroWave\RazorpayEasyEmi\Models;

use Botble\Base\Models\BaseModel;

class RazorpayEmiCachedOffer extends BaseModel
{
    protected $table = 'razorpay_emi_cached_offers';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'type',
        'description',
        'min_order_amount',
        'max_discount',
        'raw_payload',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'min_order_amount' => 'float',
        'max_discount' => 'float',
        'raw_payload' => 'array',
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function getOfferIdAttribute(): string
    {
        return (string) $this->id;
    }

    public function getMinOrderAmountAttribute($value): ?float
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }

        $rules = $this->raw_payload['rules']['items'] ?? [];
        $firstRule = $rules[0] ?? [];
        $minPaise = $firstRule['criteria']['includes']['order']['min_amount'] ?? ($this->raw_payload['min_amount'] ?? null);

        if ($minPaise && (float) $minPaise > 0) {
            return (float) $minPaise / 100;
        }

        return null;
    }

    public function getMaxDiscountAttribute($value): ?float
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }

        $rules = $this->raw_payload['rules']['items'] ?? [];
        $firstRule = $rules[0] ?? [];
        $benefits = $firstRule['benefits']['items'] ?? [];
        $firstBenefit = $benefits[0] ?? [];
        $maxPaise = $firstBenefit['max_benefit_value'] ?? ($this->raw_payload['max_discount'] ?? null);

        if ($maxPaise && (float) $maxPaise > 0) {
            return (float) $maxPaise / 100;
        }

        return null;
    }

    public function getBenefitsTypesAttribute(): array
    {
        return $this->raw_payload['benefits_types'] ?? [];
    }

    public function getPaymentMethodsAttribute(): array
    {
        $methods = [];
        $rules = $this->raw_payload['rules']['items'] ?? [];
        foreach ($rules as $rule) {
            $m = $rule['criteria']['includes']['paymentInstrument']['methods'] ?? [];
            if (is_array($m)) {
                $methods = array_merge($methods, $m);
            }
        }

        return array_values(array_unique($methods));
    }

    public function isNoCostEmi(): bool
    {
        return in_array('no_cost_emi', $this->benefits_types, true)
            || stripos($this->name, 'no cost') !== false
            || stripos($this->description ?? '', 'no cost') !== false;
    }

    public function isLowCostEmi(): bool
    {
        return in_array('low_cost_emi', $this->benefits_types, true)
            || stripos($this->name, 'low cost') !== false
            || stripos($this->description ?? '', 'low cost') !== false;
    }

    public function isEmiOffer(): bool
    {
        return $this->isEmiApplicable();
    }

    public function isNonEmiOffer(): bool
    {
        return ! $this->isEmiOffer();
    }

    public function isEmiApplicable(): bool
    {
        if ($this->isNoCostEmi() || $this->isLowCostEmi()) {
            return true;
        }

        // Strictly exclude non-EMI offers (UPI, gift cards, wallets, or prepayment discounts)
        $nameLower = strtolower($this->name . ' ' . ($this->description ?? ''));
        if (str_contains($nameLower, 'upi') || str_contains($nameLower, 'gift card') || str_contains($nameLower, '100% prepayment')) {
            return false;
        }

        $methods = $this->payment_methods;
        $hasEmiMethod = in_array('emi', $methods, true) || in_array('cardless_emi', $methods, true);
        $hasEmiKeyword = str_contains($nameLower, 'emi');

        return $hasEmiMethod || $hasEmiKeyword;
    }

    public function getBankIssuerAttribute(): string
    {
        $rules = $this->raw_payload['rules']['items'] ?? [];
        foreach ($rules as $rule) {
            $issuers = $rule['criteria']['includes']['paymentInstrument']['card']['issuers'] ?? [];
            if (! empty($issuers) && is_array($issuers)) {
                return (string) $issuers[0];
            }
        }

        $knownBanks = ['HDFC', 'ICICI', 'SBI', 'Axis', 'Kotak', 'Yes Bank', 'IDFC FIRST', 'Bank of Baroda', 'IndusInd'];
        foreach ($knownBanks as $bank) {
            if (stripos($this->name, $bank) !== false) {
                return $bank;
            }
        }

        return 'Bank Offer';
    }

    public function getCleanTitleAttribute(): string
    {
        $title = preg_replace('/\s*\((offer_[a-zA-Z0-9_-]+)\)\s*$/', '', $this->name);
        return trim($title ?: $this->name);
    }

    public function getDiscountSummaryAttribute(): string
    {
        if ($this->isNoCostEmi()) {
            return '0% Interest (Zero Subvention Cost)';
        }

        if ($this->isLowCostEmi()) {
            return 'Subsidized Low Interest';
        }

        return $this->description ?: 'Bank Cashback on EMI';
    }

    public function getOfferCategoryAttribute(): string
    {
        if ($this->isNoCostEmi()) {
            return 'no_cost';
        }

        if ($this->isLowCostEmi()) {
            return 'low_cost';
        }

        if ($this->isEmiApplicable()) {
            return 'emi_cashback';
        }

        return 'other';
    }
}
