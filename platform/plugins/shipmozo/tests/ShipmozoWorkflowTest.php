<?php

namespace SparroWave\Shipmozo\Tests;

require_once __DIR__.'/shipmozo-test-bootstrap.php';

use Botble\Ecommerce\Models\Order;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SparroWave\Shipmozo\Shipmozo;

class ShipmozoWorkflowTest extends TestCase
{
    public function test_documented_endpoint_shapes_are_used(): void
    {
        Http::swap(new Factory);
        Http::fake([
            '*/cancel-order' => Http::response(['result' => '1']),
            '*/track-order*' => Http::response(['result' => '1', 'data' => ['awb_number' => 'AWB-1']]),
            '*/get-order-label/*' => Http::response([
                'result' => '1',
                'data' => [['label' => 'data:image/png;base64,'.base64_encode('PNG')]],
            ]),
            '*/create-warehouse' => Http::response(['result' => '1', 'data' => ['warehouse_id' => 'WAREHOUSE-1']]),
            '*/get-ndr-all' => Http::response(['result' => '1', 'data' => [['awb_number' => 'AWB-1']]]),
            '*/ndr-action' => Http::response(['result' => '1']),
        ]);

        $shipmozo = (new ReflectionClass(Shipmozo::class))->newInstanceWithoutConstructor();
        foreach (['publicKey' => 'public', 'privateKey' => 'private', 'logging' => false] as $property => $value) {
            (new ReflectionClass($shipmozo))->getProperty($property)->setValue($shipmozo, $value);
        }

        $shipmozo->cancelOrder('ORDER-1', 'AWB-1');
        self::assertSame('AWB-1', $shipmozo->trackOrder('AWB-1')['awb_number']);
        self::assertSame('PNG', $shipmozo->getOrderLabel('AWB-1')['contents']);
        $shipmozo->createWarehouse([
            'address_title' => 'Main',
            'name' => 'Owner',
            'phone' => '9876543210',
            'address' => 'Delhi',
            'pincode' => '110001',
        ]);
        self::assertSame('AWB-1', $shipmozo->getNdrAll()[0]['awb_number']);
        $shipmozo->ndrAction('AWB-1', 'reattempt');

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/cancel-order')
            && $request['order_id'] === 'ORDER-1'
            && $request['awb_number'] === 'AWB-1');
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/track-order?awb_number=AWB-1'));
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/create-warehouse')
            && $request['address_line_one'] === 'Delhi'
            && $request['pin_code'] === 110001);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/ndr-action')
            && $request['awb_number'] === 'AWB-1'
            && $request['action'] === 'reattempt');
    }

    public function test_selected_manual_courier_is_assigned_before_pickup_is_scheduled(): void
    {
        $shipmozo = $this->fakeShipmozo();
        $order = new Order;
        $order->shipping_option = 'shipmozo_42_manual';

        $result = $shipmozo->createShipment($order);

        self::assertSame('1', $result['result']);
        self::assertSame('AWB-42', $result['data']['awb_number']);
        self::assertSame([
            ['push', null],
            ['assign', ['ORDER-1', 42]],
            ['detail', 'ORDER-1'],
            ['pickup', 'ORDER-1'],
        ], $shipmozo->calls);
    }

    public function test_automatic_pickup_does_not_call_manual_schedule_endpoint(): void
    {
        $shipmozo = $this->fakeShipmozo('AWB-AUTO');
        $order = new Order;
        $order->shipping_option = 'shipmozo_7_auto';

        $result = $shipmozo->createShipment($order);

        self::assertSame('AWB-AUTO', $result['data']['awb_number']);
        self::assertSame([
            ['push', null],
            ['assign', ['ORDER-1', 7]],
            ['detail', 'ORDER-1'],
        ], $shipmozo->calls);
    }

    public function test_missing_selected_courier_uses_documented_auto_assign_endpoint(): void
    {
        $shipmozo = $this->fakeShipmozo();
        $order = new Order;
        $order->shipping_option = 'standard';

        $result = $shipmozo->createShipment($order);

        self::assertSame('AWB-AUTO-ASSIGN', $result['data']['awb_number']);
        self::assertSame([
            ['push', null],
            ['auto', 'ORDER-1'],
            ['detail', 'ORDER-1'],
        ], $shipmozo->calls);
    }

    public function test_existing_shipmozo_order_is_reused_when_assignment_is_retried(): void
    {
        $shipmozo = $this->fakeShipmozo('AWB-RETRY');
        $order = new Order;
        $order->shipping_option = 'shipmozo_11_auto';

        $result = $shipmozo->createShipment($order, 'SHIPMOZO-ORDER-1');

        self::assertSame('AWB-RETRY', $result['data']['awb_number']);
        self::assertSame([
            ['assign', ['SHIPMOZO-ORDER-1', 11]],
            ['detail', 'SHIPMOZO-ORDER-1'],
        ], $shipmozo->calls);
    }

    private function fakeShipmozo(?string $detailAwb = null): Shipmozo
    {
        return new class($detailAwb) extends Shipmozo
        {
            public array $calls = [];

            public function __construct(private readonly ?string $detailAwb) {}

            public function pushOrder(Order $order): array
            {
                $this->calls[] = ['push', null];

                return ['result' => '1', 'data' => ['order_id' => 'ORDER-1']];
            }

            public function assignCourier(string $orderId, int $courierId): array
            {
                $this->calls[] = ['assign', [$orderId, $courierId]];

                return ['result' => '1', 'data' => ['courier' => 'Test Courier']];
            }

            public function getOrderDetail(string $orderId): array
            {
                $this->calls[] = ['detail', $orderId];

                return [
                    'result' => '1',
                    'data' => [['zone' => ['awb_number' => $this->detailAwb]]],
                ];
            }

            public function schedulePickup(string $orderId): array
            {
                $this->calls[] = ['pickup', $orderId];

                return ['result' => '1', 'data' => ['awb_number' => 'AWB-42']];
            }

            public function autoAssignOrder(string $orderId): array
            {
                $this->calls[] = ['auto', $orderId];

                return ['result' => '1', 'data' => ['awb_number' => 'AWB-AUTO-ASSIGN']];
            }
        };
    }
}
