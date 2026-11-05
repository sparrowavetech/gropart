<?php

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Botble\\Ecommerce\\')) {
        $file = __DIR__ . '/../platform/plugins/ecommerce/src/' . str_replace('\\', '/', substr($class, 17)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'Botble\\Marketplace\\')) {
        $file = __DIR__ . '/../platform/plugins/marketplace/src/' . str_replace('\\', '/', substr($class, 19)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'SparroWave\\SellerInvoicing\\')) {
        $file = __DIR__ . '/../platform/plugins/seller-invoicing/src/' . str_replace('\\', '/', substr($class, 27)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use Illuminate\Support\Carbon;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\Shipment;
use Botble\Marketplace\Models\Revenue;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config([
    'database.connections.mysql.host' => '',
    'database.connections.mysql.unix_socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);
\Illuminate\Support\Facades\DB::purge('mysql');

function testResolveDate(Order $order, ?Revenue $revenue = null): Carbon {
    $completedHistory = OrderHistory::query()
        ->where('order_id', $order->getKey())
        ->where(function ($q) {
            $q->where('action', 'mark_order_as_completed')
                ->orWhere(function ($sq) {
                    $sq->where('action', 'update_shipping_status')
                        ->where('description', 'like', '%Delivered%');
                });
        })
        ->latest('id')
        ->first();

    if ($completedHistory && $completedHistory->created_at) {
        return Carbon::parse($completedHistory->created_at);
    }

    $shipment = Shipment::query()
        ->where('order_id', $order->getKey())
        ->whereIn('status', ['delivered', 'completed'])
        ->latest('id')
        ->first();

    if ($shipment && ($shipment->date_shipped ?: $shipment->updated_at)) {
        return Carbon::parse($shipment->date_shipped ?: $shipment->updated_at);
    }

    if ($revenue && $revenue->created_at) {
        return Carbon::parse($revenue->created_at);
    }

    if ($order->status == 'completed' && $order->updated_at) {
        return Carbon::parse($order->updated_at);
    }

    return Carbon::parse($order->created_at ?: now());
}

$docs = SparroWave\SellerInvoicing\Models\Document::query()->take(10)->get();
foreach ($docs as $doc) {
    $order = Order::find($doc->order_id);
    if (! $order) continue;
    $revenue = Revenue::where('order_id', $doc->order_id)->first();
    $fulfillmentDate = testResolveDate($order, $revenue);
    echo "Doc {$doc->id} | Order #{$order->id} | Order Date: {$order->created_at->format('Y-m-d')} | Fulfilled Date: {$fulfillmentDate->format('Y-m-d H:i')} | Old Doc issued_at: {$doc->issued_at}\n";
}
