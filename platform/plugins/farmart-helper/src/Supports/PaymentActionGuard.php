<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Base\Supports\Action;
use Botble\Payment\Enums\PaymentStatusEnum;
use Closure;

class PaymentActionGuard extends Action
{
    public function __construct(private readonly Action $inner)
    {
    }

    public function fire(string $action, array $args): void
    {
        $data = $args[0] ?? null;
        if (
            defined('PAYMENT_ACTION_PAYMENT_PROCESSED')
            && $action === PAYMENT_ACTION_PAYMENT_PROCESSED
            && is_array($data)
            && ($data['payment_channel'] ?? null) == 'razorpay'
            && is_plugin_active('razorpay')
            && ($data['status'] ?? null) != PaymentStatusEnum::COMPLETED
        ) {
            // This action is downstream of the gateway's verification. Retain the
            // attempt using its existing deduplication logic, without placing an order.
            app(RazorpayAttemptRecorder::class)->record($data);

            return;
        }

        $this->inner->fire($action, $args);
    }

    public function addListener(string|array|null $hook, string|array|Closure $callback, int $priority = 20, int $arguments = 1): void
    {
        $this->inner->addListener($hook, $callback, $priority, $arguments);
    }

    public function removeListener(string $hook): static
    {
        $this->inner->removeListener($hook);

        return $this;
    }

    public function getListeners(): array
    {
        return $this->inner->getListeners();
    }
}
