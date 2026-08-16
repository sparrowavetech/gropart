<?php

namespace Ashikul\IndiaSmsGateway\Facades;

use Illuminate\Support\Facades\Facade;

class IndiaSms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'india-sms';
    }
}
