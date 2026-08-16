<?php

namespace Ashikul\IndiaSmsGateway\Contracts;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;

interface SmsDriverInterface
{
    public function key(): string;

    public function send(SmsMessage $message): SmsResult;

    public function balance(): ?string;
}
