<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Throwable;

class SmsCountryDriver extends AbstractDriver
{
    public function key(): string { return 'smscountry'; }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $endpoint = (string) ($this->setting('endpoint') ?: 'https://api.smscountry.com/SMSCwebservice_bulk.aspx');
            $payload = [
                'User' => (string) $this->setting('username'),
                'passwd' => (string) $this->secret('api_key'),
                'mobilenumber' => substr($message->to, 2),
                'message' => $message->message,
                'sid' => (string) $this->setting('sender_id'),
                'mtype' => preg_match('/[^\x00-\x7F]/u', $message->message) ? 'OL' : 'N',
                'DR' => 'Y',
            ];
            $response = $this->http()->get($endpoint, $payload);
            $body = trim($response->body());
            $accepted = $response->successful() && ! preg_match('/error|invalid|failed/i', $body);
            return new SmsResult($accepted, $accepted ? 'accepted' : 'failed',
                $accepted ? mb_substr($body, 0, 191) : null, null,
                $accepted ? null : ($body ?: 'SMSCountry rejected the request.'),
                ['raw' => mb_substr($body, 0, 10000)], $this->key());
        } catch (Throwable $e) { return SmsResult::failed($e->getMessage(), 'connection_error'); }
    }
}
