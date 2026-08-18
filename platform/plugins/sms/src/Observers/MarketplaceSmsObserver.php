<?php

namespace Botble\Sms\Observers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Sms\Enums\SmsEnum;
use Botble\Sms\Supports\SmsHandler;

class MarketplaceSmsObserver
{
    public function updated(object $model): void
    {
        if (! is_plugin_active('sms')) {
            return;
        }

        if ($model instanceof Product) {
            $this->handleProductApproved($model);

            return;
        }

        $class = get_class($model);

        if ($class === 'Botble\Marketplace\Models\Withdrawal') {
            $this->handleWithdrawalApproved($model);

            return;
        }

        if ($class === 'Botble\Marketplace\Models\Vendor') {
            $this->handleVendorApproved($model);
        }
    }

    private function handleProductApproved(Product $product): void
    {
        if (! $product->wasChanged('status') || (string) $product->status !== BaseStatusEnum::PUBLISHED) {
            return;
        }

        $store = $product->store ?? null;

        if (! $store || ! $store->phone) {
            return;
        }

        $this->sendToStore(SmsEnum::PRODUCT_APPROVED(), $store, [
            'product_name' => $product->name,
            'product_url' => $product->url,
        ]);
    }

    private function handleWithdrawalApproved(object $withdrawal): void
    {
        if (! method_exists($withdrawal, 'wasChanged') || ! $withdrawal->wasChanged('status') || (string) $withdrawal->status !== 'completed') {
            return;
        }

        $store = $withdrawal->customer->store ?? null;

        if (! $store || ! $store->phone) {
            return;
        }

        $this->sendToStore(SmsEnum::WITHDRAWAL_APPROVED(), $store, [
            'withdrawal_amount' => function_exists('format_price') ? format_price($withdrawal->amount) : (string) $withdrawal->amount,
        ]);
    }

    private function handleVendorApproved(object $vendor): void
    {
        if (! method_exists($vendor, 'wasChanged') || ! $vendor->wasChanged('confirmed_at')) {
            return;
        }

        $store = $vendor->store ?? null;

        if (! $store || ! $store->phone) {
            return;
        }

        $this->sendToStore(SmsEnum::VENDOR_ACCOUNT_APPROVED(), $store);
    }

    private function sendToStore(string $template, object $store, array $variables = []): void
    {
        $sms = new SmsHandler();
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        $sms->setVariableValues(array_merge([
            'store_name' => (string) $store->name,
            'store_phone' => (string) $store->phone,
            'store_link' => (string) ($store->url ?? ''),
        ], $variables));
        $sms->sendUsingTemplate($template, $store->phone);
    }
}
