<?php

namespace SparroWave\AdvancedCod\Supports;

use Botble\Base\Supports\Action;
use Botble\Payment\Enums\PaymentStatusEnum;
use Closure;

class AdvancePaymentAction extends Action
{
    public function __construct(private readonly Action $inner)
    {
    }

    public function fire(string $action, array $args): void
    {
        $data = $args[0] ?? null;
        if (defined('PAYMENT_ACTION_PAYMENT_PROCESSED') && $action === PAYMENT_ACTION_PAYMENT_PROCESSED
            && is_array($data) && AdvancePayment::handle($data)) {
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
