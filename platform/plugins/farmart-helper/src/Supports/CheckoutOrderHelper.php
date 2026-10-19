<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Supports\OrderHelper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CheckoutOrderHelper extends OrderHelper
{
    public function processOrder(string|array|null $orderIds, ?string $chargeId = null): bool|Collection|array|Model
    {
        $tokens = Order::whereIn('id', (array) $orderIds)->orderBy('token')->pluck('token')->unique()->all();
        $run = function () use ($orderIds, $chargeId) {
        foreach (Order::whereIn('id', (array) $orderIds)->where('is_finished', true)->pluck('id') as $id) {
            \Illuminate\Support\Facades\Log::info('checkout_integrity.duplicate_finalization_skipped', ['order_id'=>$id]);
        }
        $policy = app(CheckoutPaymentPolicy::class);
        $allowed = Order::query()->whereIn('id', (array) $orderIds)->get()
            ->reject(fn (Order $order) => $policy->blocksFinalization($order, $chargeId))
            ->modelKeys();

        return $allowed ? parent::processOrder($allowed, $chargeId) : false;
        };
        foreach (array_reverse($tokens) as $token) {
            $next = $run;
            $run = fn () => app(PaymentSnapshot::class)->synchronized($token, $next);
        }
        return $run();
    }

    public function captureFootprints(Order $order): void
    {
        if ($order->referral()->exists()) return;
        parent::captureFootprints($order);
        if ($order->referral()->exists()) {
            \Illuminate\Support\Facades\Log::info('checkout_integrity.source_saved', ['order_id'=>$order->id]);
        }
    }

    public function isCheckoutTokenSpent(?string $token): bool
    {
        if (!$token) return false;
        $orders = Order::where('token', $token)->get();
        // A pending payment locks prices, but has not consumed the customer's cart.
        return $orders->isNotEmpty() && $orders->every(fn (Order $order) => parent::isOrderLocked($order));
    }

    public function isOrderLocked(Order $order): bool
    {
        return (bool) apply_filters('ecommerce_order_checkout_locked', (bool) app(PaymentSnapshot::class)->read($order) || parent::isOrderLocked($order), $order);
    }

    public function processOrderInCheckout($sessionData, $request, $cartItems, $order, array $generalData): array
    {
        if ($order && $this->isOrderLocked($order)) return [$sessionData, $order->fresh()];
        $created = $sessionData['created_order'] ?? null;
        if (is_string($created) && $created !== '') {
            $created = Carbon::parse($created);
        }
        $rebuilt = $order && $created && ! empty($sessionData['created_order_id'])
            && (! $created instanceof CarbonInterface || ! $created->eq(Cart::instance('cart')->getLastUpdatedAt()));

        [$sessionData, $updated] = parent::processOrderInCheckout($sessionData, $request, $cartItems, $order, $generalData);

        // Parent refreshes the cart amount but retains these fields on existing orders.
        // A caller-provided total is authoritative and must not receive shipping twice.
        if ($rebuilt && ! array_key_exists('amount', $generalData)) {
            $updated->amount += (float) $updated->shipping_amount + (float) $updated->shipping_tax_amount;
        }

        return [$sessionData, $updated];
    }
}
