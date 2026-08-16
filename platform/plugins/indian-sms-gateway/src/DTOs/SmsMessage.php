<?php

namespace Ashikul\IndiaSmsGateway\DTOs;

class SmsMessage
{
    public function __construct(
        public string $to,
        public string $message,
        public ?string $senderId = null,
        public string $type = 'transactional',
        public array $metadata = [],
    ) {
    }
}
