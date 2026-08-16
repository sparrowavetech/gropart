<?php

namespace Ashikul\IndiaSmsGateway\Jobs;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\Services\SmsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(
        public string $to,
        public string $message,
        public ?string $senderId = null,
        public string $type = 'transactional',
        public array $metadata = [],
        public ?string $gateway = null,
    ) {
    }

    public function handle(SmsManager $manager): void
    {
        $result = $manager->send(new SmsMessage($this->to, $this->message, $this->senderId, $this->type, $this->metadata), $this->gateway);
        if (! $result->accepted && in_array($result->errorCode, ['connection_error', 'timeout', '500', '502', '503', '504'], true)) {
            throw new \RuntimeException($result->errorMessage ?: 'Temporary SMS gateway failure.');
        }
    }
}
