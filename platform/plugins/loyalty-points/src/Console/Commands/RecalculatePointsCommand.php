<?php

namespace Botble\LoyaltyPoints\Console\Commands;

use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Console\Command;

class RecalculatePointsCommand extends Command
{
    protected $signature = 'loyalty:recalculate {--order_id= : The ID of the order to recalculate}';

    protected $description = 'Recalculate loyalty points for orders';

    public function handle(LoyaltyPointService $service): int
    {
        $orderId = $this->option('order_id');

        if ($orderId) {
            $order = Order::query()->find($orderId);
            if (! $order) {
                $this->error('Order not found.');

                return self::FAILURE;
            }

            $this->info("Recalculating points for order #{$order->code}...");
            $service->recalculatePointsForOrder($order);
            $this->info('Done.');

            return self::SUCCESS;
        }

        $this->components->error('Please provide --order_id to recalculate points for a specific order.');

        return self::FAILURE;
    }
}
