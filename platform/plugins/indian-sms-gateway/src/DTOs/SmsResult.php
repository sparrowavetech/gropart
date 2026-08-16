<?php

namespace Ashikul\IndiaSmsGateway\DTOs;

class SmsResult
{
    public function __construct(
        public bool $accepted,
        public string $status = 'failed',
        public ?string $messageId = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public array $response = [],
        public ?string $gateway = null,
    ) {
    }

    public static function failed(string $message, ?string $code = null, array $response = []): self
    {
        return new self(false, 'failed', null, $code, $message, $response);
    }
}
