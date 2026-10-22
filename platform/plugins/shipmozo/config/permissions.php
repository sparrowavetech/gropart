<?php

return [
    [
        'name' => 'ShipMozo Settings',
        'flag' => 'shipmozo.settings',
        'parent_flag' => 'shipping_methods.index',
    ],
    [
        'name' => 'Create & Dispatch Shipment',
        'flag' => 'shipmozo.shipments.create',
        'parent_flag' => 'ecommerce.shipments.edit',
    ],
    [
        'name' => 'Update Shipping Rates',
        'flag' => 'shipmozo.rates.update',
        'parent_flag' => 'ecommerce.shipments.edit',
    ],
    [
        'name' => 'ShipMozo NDRs',
        'flag' => 'shipmozo.ndr.index',
        'parent_flag' => 'orders.index',
    ],
    [
        'name' => 'ShipMozo NDR Action (Re-attempt / RTO)',
        'flag' => 'shipmozo.ndr.action',
        'parent_flag' => 'shipmozo.ndr.index',
    ],
];
