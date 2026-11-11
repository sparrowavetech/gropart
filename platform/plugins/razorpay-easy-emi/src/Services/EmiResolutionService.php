<?php

namespace SparroWave\RazorpayEasyEmi\Services;

use Botble\Ecommerce\Models\Product;
use SparroWave\RazorpayEasyEmi\Enums\EmiSchemeEnum;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig;

class EmiResolutionService
{
    public function isEmiGloballyEnabled(): bool
    {
        if (! is_plugin_active('ecommerce') || ! is_plugin_active('razorpay')) {
            return false;
        }

        $paymentStatus = get_payment_setting('status', 'razorpay');
        if ((string) $paymentStatus !== '1') {
            return false;
        }

        return (bool) setting('razorpay_easy_emi_enabled', true);
    }

    public function getKeyId(): ?string
    {
        $custom = setting('razorpay_easy_emi_custom_key');
        if (! empty($custom)) {
            return trim($custom);
        }

        $key = get_payment_setting('key', 'razorpay');

        return ! empty($key) ? trim($key) : null;
    }

    public function resolveForProduct(?Product $product): array
    {
        if (! $product || ! $this->isEmiGloballyEnabled()) {
            return ['eligible' => false];
        }

        $key = $this->getKeyId();
        if (! $key) {
            return ['eligible' => false];
        }

        // Must be physical product and not enquiry-only
        if ($product->is_variation) {
            $parent = $product->original_product;
            if ($parent && (bool) ($parent->is_enquiry ?? false)) {
                return ['eligible' => false];
            }
        } elseif ((bool) ($product->is_enquiry ?? false)) {
            return ['eligible' => false];
        }

        // Fetch product EMI config
        $productId = $product->is_variation ? ($product->original_product->id ?? $product->id) : $product->id;
        $config = RazorpayEmiProductConfig::query()->where('product_id', $productId)->first();

        // Status check
        $status = $config?->status ?? 'inherit';
        if ($status === 'disabled') {
            return ['eligible' => false];
        }

        // Global default scheme
        $globalDefaultScheme = setting('razorpay_easy_emi_default_scheme', 'standard');
        if ($status === 'inherit' && $globalDefaultScheme === 'disabled') {
            return ['eligible' => false];
        }

        // Minimum order amount threshold
        $globalMin = (float) setting('razorpay_easy_emi_min_amount', 2500);
        $minThreshold = ($config && $config->min_amount !== null && $config->min_amount > 0)
            ? (float) $config->min_amount
            : $globalMin;

        // Resolve price
        $price = $this->getProductActivePrice($product);
        if ($price < $minThreshold) {
            return ['eligible' => false];
        }

        $scheme = $config?->emi_type ? $config->emi_type->getValue() : $globalDefaultScheme;
        $offerMode = $config?->offer_mode ?? 'auto';
        $selectedOfferIds = $config ? $config->offer_ids : [];

        // Fetch active offers
        $allActiveOffers = RazorpayEmiCachedOffer::query()
            ->where('is_active', true)
            ->get();

        $activeEmiOffers = $allActiveOffers->filter(fn ($o) => $o->isEmiOffer());
        $activeOtherOffers = $allActiveOffers->filter(fn ($o) => $o->isNonEmiOffer());

        if ($offerMode === 'custom' && ! empty($selectedOfferIds)) {
            $applicableEmiOffers = $activeEmiOffers->whereIn('id', $selectedOfferIds);
            $applicableOtherOffers = $activeOtherOffers->whereIn('id', $selectedOfferIds);
        } else {
            $applicableEmiOffers = $activeEmiOffers;
            $applicableOtherOffers = $activeOtherOffers;
        }

        // Calculate starting EMI
        $startingEmi = $this->calculateStartingEmi($price, $scheme);

        // Calculate max savings
        $maxSavings = $this->calculateMaxSavings($price, $scheme, $applicableEmiOffers->merge($applicableOtherOffers));

        // Generate full bank EMI plans table
        $bankPlans = $this->getBankEmiPlans($price, $scheme);

        // Custom badge
        $badge = $config?->custom_badge;
        if (empty($badge)) {
            $badge = match ($scheme) {
                'no_cost' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.no_cost_emi'),
                'low_cost' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.low_cost_emi'),
                default => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.standard_emi'),
            };
        }

        return [
            'eligible' => true,
            'product_id' => $productId,
            'key' => $key,
            'amount' => (int) round($price * 100), // in paise
            'price' => $price,
            'currency' => 'INR',
            'scheme' => $scheme,
            'emi_type' => $scheme,
            'offer_mode' => $offerMode,
            'offer_ids' => $selectedOfferIds,
            'offer_id' => ! empty($selectedOfferIds) ? (string) $selectedOfferIds[0] : null,
            'custom_badge' => $config?->custom_badge,
            'badge' => $badge,
            'min_amount' => $minThreshold,
            'starting_emi' => $startingEmi,
            'max_savings' => $maxSavings,
            'bank_plans' => $bankPlans,
            'emi_offers' => $applicableEmiOffers->values()->all(),
            'other_offers' => $applicableOtherOffers->values()->all(),
        ];
    }

    public function calculateMonthlyEmi(float $principal, int $months, float $annualRate): float
    {
        if ($annualRate <= 0) {
            return round($principal / $months);
        }

        $r = ($annualRate / 12) / 100;
        $emi = ($principal * $r * pow(1 + $r, $months)) / (pow(1 + $r, $months) - 1);

        return round($emi);
    }

    public function calculateStartingEmi(float $price, string $scheme): float
    {
        // 24 months standard/low-cost gives the lowest starting EMI (e.g. ₹106/mo or ₹124/mo)
        if ($scheme === 'no_cost') {
            // For 3-month or 6-month zero interest
            return round($price / 6);
        }

        return $this->calculateMonthlyEmi($price, 24, 15.0);
    }

    public function calculateMaxSavings(float $price, string $scheme, $offers): float
    {
        $savings = 0.0;

        // Subvention interest saving for No Cost EMI (standard 6-month interest ~15%)
        if ($scheme === 'no_cost') {
            $stdTotal = $this->calculateMonthlyEmi($price, 6, 15.0) * 6;
            $savings = max($savings, round($stdTotal - $price));
        }

        foreach ($offers as $offer) {
            if ($offer->min_order_amount && $price < $offer->min_order_amount) {
                continue;
            }

            $rules = $offer->raw_payload['rules']['items'] ?? [];
            $benefits = $rules[0]['benefits']['items'] ?? [];
            $benefit = $benefits[0] ?? [];
            $unit = $benefit['unit'] ?? 'PERCENTAGE';
            $val = isset($benefit['value']) ? (float) $benefit['value'] : 0.0;
            $maxCap = isset($benefit['max_benefit_value']) && (float) $benefit['max_benefit_value'] > 0
                ? ((float) $benefit['max_benefit_value'] / 100)
                : 0.0;

            if ($unit === 'PERCENTAGE' && $val > 0) {
                // val is basis points (e.g. 500 = 5%, 350 = 3.5%, 100 = 1%)
                $calc = ($price * ($val / 10000));
                if ($maxCap > 0) {
                    $calc = min($calc, $maxCap);
                }
                $savings = max($savings, round($calc));
            } elseif ($unit === 'FIXED' && $val > 0) {
                // val is in paise (e.g. 5000 = ₹50)
                $calc = $val / 100;
                $savings = max($savings, round($calc));
            } elseif ($offer->max_discount && $offer->max_discount > 0 && $offer->max_discount <= ($price * 0.5)) {
                $savings = max($savings, (float) $offer->max_discount);
            }
        }

        return $savings > 0 ? $savings : 500.0;
    }

    public function getBankEmiPlans(float $price, string $scheme): array
    {
        $banks = [
            [
                'code' => 'hdfc',
                'name' => 'HDFC Bank',
                'type' => 'Credit Card',
                'annual_rate' => 15.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3, 6] : [],
            ],
            [
                'code' => 'icici',
                'name' => 'ICICI Bank',
                'type' => 'Credit Card',
                'annual_rate' => 15.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3, 6] : [],
            ],
            [
                'code' => 'sbi',
                'name' => 'State Bank of India',
                'type' => 'Credit Card',
                'annual_rate' => 14.5,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3] : [],
            ],
            [
                'code' => 'axis',
                'name' => 'Axis Bank',
                'type' => 'Credit Card',
                'annual_rate' => 15.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3, 6] : [],
            ],
            [
                'code' => 'kotak',
                'name' => 'Kotak Mahindra Bank',
                'type' => 'Credit Card',
                'annual_rate' => 15.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3] : [],
            ],
            [
                'code' => 'idfc',
                'name' => 'IDFC FIRST Bank',
                'type' => 'Credit Card',
                'annual_rate' => 14.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3, 6] : [],
            ],
            [
                'code' => 'yesbank',
                'name' => 'Yes Bank',
                'type' => 'Credit Card',
                'annual_rate' => 15.0,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3] : [],
            ],
            [
                'code' => 'bob',
                'name' => 'Bank of Baroda',
                'type' => 'Credit Card',
                'annual_rate' => 14.5,
                'no_cost_tenures' => in_array($scheme, ['no_cost', 'low_cost']) ? [3] : [],
            ],
        ];

        $tenures = [3, 6, 9, 12, 18, 24];

        foreach ($banks as &$bank) {
            $plans = [];
            foreach ($tenures as $months) {
                $isNoCost = in_array($months, $bank['no_cost_tenures'], true);
                $annualRate = $isNoCost ? 0.0 : $bank['annual_rate'];

                $stdMonthlyEmi = $this->calculateMonthlyEmi($price, $months, $bank['annual_rate']);
                $stdTotal = round($stdMonthlyEmi * $months);
                $stdInterest = max(0, $stdTotal - $price);

                if ($isNoCost) {
                    $monthlyEmi = round($price / $months);
                    $total = $price;
                    $savings = $stdInterest;
                } else {
                    $monthlyEmi = $stdMonthlyEmi;
                    $total = $stdTotal;
                    $savings = 0;
                }

                $plans[] = [
                    'months' => $months,
                    'monthly_emi' => $monthlyEmi,
                    'annual_rate' => $annualRate,
                    'std_rate' => $bank['annual_rate'],
                    'total' => $total,
                    'savings' => $savings,
                    'is_no_cost' => $isNoCost,
                ];
            }
            $bank['plans'] = $plans;
        }

        return $banks;
    }

    public function getProductActivePrice(Product $product): float
    {
        if (isset($product->front_sale_price_with_taxes) && $product->front_sale_price_with_taxes > 0) {
            return (float) $product->front_sale_price_with_taxes;
        }

        if (isset($product->front_sale_price) && $product->front_sale_price > 0) {
            return (float) $product->front_sale_price;
        }

        if (isset($product->price_with_taxes) && $product->price_with_taxes > 0) {
            return (float) $product->price_with_taxes;
        }

        return (float) ($product->sale_price ?: $product->price);
    }
}
