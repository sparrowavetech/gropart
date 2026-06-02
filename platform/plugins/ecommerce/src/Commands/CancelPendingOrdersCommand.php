<?php

namespace Botble\Ecommerce\Commands;

use Botble\Ecommerce\Enums\OrderCancellationReasonEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class CancelPendingOrdersCommand extends Command
{
    protected $signature = 'cms:ecommerce:cancel-pending-orders
                            {--threshold= : Override the configured threshold in minutes}';

    protected $description = 'Cancel pending unfinished orders paid by online gateways that exceed the configured age threshold';

    public function handle(): int
    {
        if (! get_ecommerce_setting('auto_cancel_pending_orders_enabled', false)) {
            $this->components->warn('Auto-cancel for pending orders is disabled. Enable it in Ecommerce Settings.');

            return self::SUCCESS;
        }

        $threshold = (int) ($this->option('threshold') ?: get_ecommerce_setting('auto_cancel_pending_orders_threshold_minutes', 30));
        $threshold = max(1, $threshold);
        $cutoff = Carbon::now()->subMinutes($threshold);

        $cancelled = 0;
        $failed = 0;

        // Orders with payment_id IS NULL are abandoned online-payment attempts.
        // COD and Bank Transfer create a payment row immediately and set is_finished=true,
        // so they are never matched here.
        Order::query()
            ->where('status', OrderStatusEnum::PENDING)
            ->where('is_finished', false)
            ->whereNull('payment_id')
            ->where('created_at', '<=', $cutoff)
            ->chunkById(100, function ($orders) use (&$cancelled, &$failed): void {
                foreach ($orders as $order) {
                    // Re-check inside the loop to avoid racing a payment webhook
                    // that completes the order between query and cancel.
                    $order->refresh();
                    if (
                        $order->status->getValue() !== OrderStatusEnum::PENDING
                        || $order->is_finished
                        || $order->payment_id
                    ) {
                        continue;
                    }

                    try {
                        OrderHelper::cancelOrder(
                            $order,
                            OrderCancellationReasonEnum::PAYMENT_ISSUES_OR_DECLINED_TRANSACTION
                        );
                        $cancelled++;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->components->error("Failed to cancel order #{$order->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->components->info("Cancelled {$cancelled} pending order(s), {$failed} failed.");

        return self::SUCCESS;
    }
}
