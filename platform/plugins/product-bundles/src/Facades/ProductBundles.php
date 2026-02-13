<?php

namespace Botble\ProductBundles\Facades;

use Illuminate\Support\Facades\Facade;

class ProductBundles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'product-bundles';
    }
}
