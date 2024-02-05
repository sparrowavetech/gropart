<?php

namespace Botble\Marketplace\Facades;

use Botble\Marketplace\Supports\SellerInvoiceHelper;
use Illuminate\Support\Facades\Facade;

class SellerInvoiceHelperFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SellerInvoiceHelper::class;
    }
}
