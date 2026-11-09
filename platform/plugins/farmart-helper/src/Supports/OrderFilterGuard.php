<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Base\Supports\Filter;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class OrderFilterGuard extends Filter
{
    public function __construct(private readonly Filter $inner)
    {
    }

    public function fire(string $action, array $args)
    {
        if (defined('SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA') && $action === SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA) {
            $orders = $args[0] ?? null;
            if ($orders instanceof Collection) {
                return app(OrderNotificationGuard::class)->handle($orders);
            }
        }

        return $this->inner->fire($action, $args);
    }

    public function addListener(
        string|array|null $hook,
        string|array|Closure $callback,
        int $priority = 20,
        int $arguments = 1
    ): void {
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

