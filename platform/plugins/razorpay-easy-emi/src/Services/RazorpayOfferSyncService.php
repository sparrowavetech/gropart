<?php

namespace SparroWave\RazorpayEasyEmi\Services;

use Botble\Base\Facades\BaseHelper;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer;

class RazorpayOfferSyncService
{
    public function getCredentials(): ?array
    {
        $customKey = setting('razorpay_easy_emi_custom_key');
        $customSecret = setting('razorpay_easy_emi_custom_secret');

        if (! empty($customKey) && ! empty($customSecret)) {
            return [
                'key' => trim($customKey),
                'secret' => trim($customSecret),
            ];
        }

        $key = get_payment_setting('key', 'razorpay');
        $secret = get_payment_setting('secret', 'razorpay');

        if (! empty($key) && ! empty($secret)) {
            return [
                'key' => trim($key),
                'secret' => trim($secret),
            ];
        }

        return null;
    }

    public function syncOffers(): array
    {
        $creds = $this->getCredentials();

        if (! $creds) {
            return [
                'success' => false,
                'count' => 0,
                'message' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.errors.missing_credentials'),
                'offers' => [],
            ];
        }

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->timeout(12)
                ->get('https://api.razorpay.com/v1/offers');

            if (! $response->successful()) {
                $errorMsg = $response->json('error.description') ?: $response->body();

                return [
                    'success' => false,
                    'count' => 0,
                    'message' => $errorMsg,
                    'offers' => [],
                ];
            }

            $data = $response->json();
            $items = $data['items'] ?? [];
            $now = Carbon::now();
            $syncedCount = 0;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (! $id) {
                    continue;
                }

                $name = $item['name'] ?? $id;
                $type = $item['type'] ?? 'instant';
                $description = $item['description'] ?? null;

                $rules = $item['rules']['items'] ?? [];
                $firstRule = $rules[0] ?? [];
                $minPaise = $firstRule['criteria']['includes']['order']['min_amount'] ?? ($item['min_amount'] ?? null);
                $minOrder = ($minPaise && (float) $minPaise > 0) ? ((float) $minPaise / 100) : null;

                $benefits = $firstRule['benefits']['items'] ?? [];
                $firstBenefit = $benefits[0] ?? [];
                $maxPaise = $firstBenefit['max_benefit_value'] ?? ($item['max_discount'] ?? null);
                $maxDiscount = ($maxPaise && (float) $maxPaise > 0) ? ((float) $maxPaise / 100) : null;

                RazorpayEmiCachedOffer::query()->updateOrCreate(
                    ['id' => $id],
                    [
                        'name' => $name,
                        'type' => $type,
                        'description' => $description,
                        'min_order_amount' => $minOrder,
                        'max_discount' => $maxDiscount,
                        'raw_payload' => $item,
                        'synced_at' => $now,
                    ]
                );
                $syncedCount++;
            }

            return [
                'success' => true,
                'count' => $syncedCount,
                'message' => trans('plugins/razorpay-easy-emi::razorpay-easy-emi.sync_success', ['count' => $syncedCount]),
                'offers' => RazorpayEmiCachedOffer::query()->orderBy('name')->get()->toArray(),
            ];
        } catch (Exception $e) {
            BaseHelper::logError($e);

            return [
                'success' => false,
                'count' => 0,
                'message' => $e->getMessage(),
                'offers' => [],
            ];
        }
    }

    public function getDiagnostics(): array
    {
        $isPluginActive = is_plugin_active('razorpay');
        $isPaymentActive = (string) get_payment_setting('status', 'razorpay') === '1';
        $creds = $this->getCredentials();
        $hasKey = ! empty($creds['key']);
        $hasSecret = ! empty($creds['secret']);
        $cachedCount = RazorpayEmiCachedOffer::query()->count();
        $latestSync = RazorpayEmiCachedOffer::query()->max('synced_at');
        $customKey = setting('razorpay_easy_emi_custom_key');

        return [
            'razorpay_plugin_active' => $isPluginActive,
            'razorpay_payment_active' => $isPaymentActive,
            'credentials_configured' => $hasKey && $hasSecret,
            'key_id' => $hasKey ? (substr($creds['key'], 0, 8) . '...' . substr($creds['key'], -4)) : null,
            'key_id_preview' => $hasKey ? (substr($creds['key'], 0, 8) . '...' . substr($creds['key'], -4)) : null,
            'key_source' => ! empty($customKey) ? 'custom' : 'razorpay',
            'cached_offers_count' => $cachedCount,
            'last_synced_at' => $latestSync ? Carbon::parse($latestSync) : null,
            'latest_sync' => $latestSync ? Carbon::parse($latestSync)->diffForHumans() : null,
        ];
    }
}
