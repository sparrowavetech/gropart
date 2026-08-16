<?php

use Botble\Ecommerce\Models\Order;
use SparroWave\Shipmozo\Shipmozo;

if (! class_exists(Order::class)) {
    eval('namespace Botble\Ecommerce\Models; class Order { public mixed $shipping_option = null; }');
}

if (! class_exists(Shipmozo::class)) {
    require_once dirname(__DIR__).'/src/Shipmozo.php';
}
