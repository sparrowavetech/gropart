<?php

namespace Botble\Sms\Listeners;

use Botble\Sms\Events\SendSmsEvent;
use Exception;
use Log;

class SendSmsListener
{
  
    public function __construct()
    {
      
    }

    /**
     * Handle the event.
     *
     * @param SendSmsEvent $event
     * @return void
     * @throws Exception
     */
    public function handle(SendSmsEvent $event)
    {
       
        try {
           
           
        } catch (Exception $exception) {
            if ($event->debug) {
                throw $exception;
            }
            Log::error($exception->getMessage());
        }
    }
}
