<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Listeners\AwardPointsForCompletedOrder;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class PointsEarningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'platform/plugins/loyalty-points/database/migrations']);
    }

    public function test_event_triggers_listener()
    {
        Event::fake();

        event(new OrderCompletedEvent(new Order()));

        Event::assertListening(
            OrderCompletedEvent::class,
            AwardPointsForCompletedOrder::class
        );
    }

    public function test_listener_calls_service()
    {
        $service = Mockery::mock(LoyaltyPointService::class);
        $service->shouldReceive('awardPointsForOrder')->once();

        $listener = new AwardPointsForCompletedOrder($service);

        $order = new Order();
        $order->id = 1;
        $order->user_id = 123;
        $order->status = 'completed';

        $event = new OrderCompletedEvent($order);

        $listener->handle($event);
    }
}
