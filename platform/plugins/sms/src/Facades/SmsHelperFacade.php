<?php

namespace Botble\Ecommerce\Facades;

use Botble\Sms\Supports\SmsHandler;
use Illuminate\Support\Facades\Facade;

class SmsHelperFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SmsHandler::class;
    }
}
