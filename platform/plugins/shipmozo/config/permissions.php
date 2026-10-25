<?php

return [
    [
        'name' => 'ShipMozo Settings',
        'flag' => 'shipmozo.settings',
        'parent_flag' => 'shipping_methods.index',
    ],
    [
        'name' => 'ShipMozo Logs',
        'flag' => 'shipmozo.logs.index',
        'parent_flag' => 'shipmozo.settings',
    ],
    [
        'name' => 'View Log Detail',
        'flag' => 'shipmozo.logs.show',
        'parent_flag' => 'shipmozo.logs.index',
    ],
    [
        'name' => 'Delete Log File',
        'flag' => 'shipmozo.logs.destroy',
        'parent_flag' => 'shipmozo.logs.index',
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
    [
        'name' => 'ShipMozo Mapped Warehouses',
        'flag' => 'shipmozo.warehouses.index',
        'parent_flag' => 'marketplace.store.index',
    ],
    [
        'name' => 'Edit ShipMozo Warehouse',
        'flag' => 'shipmozo.warehouses.edit',
        'parent_flag' => 'shipmozo.warehouses.index',
    ],
    [
        'name' => 'Unlink ShipMozo Warehouse',
        'flag' => 'shipmozo.warehouses.destroy',
        'parent_flag' => 'shipmozo.warehouses.index',
    ],
];
