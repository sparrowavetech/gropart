<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\Contracts\SmsDriverInterface;
use Ashikul\IndiaSmsGateway\Drivers\Fast2SmsDriver;
use Ashikul\IndiaSmsGateway\Drivers\FirebaseSmsDriver;
use Ashikul\IndiaSmsGateway\Drivers\GenericHttpDriver;
use Ashikul\IndiaSmsGateway\Drivers\KaleyraDriver;
use Ashikul\IndiaSmsGateway\Drivers\Msg91Driver;
use Ashikul\IndiaSmsGateway\Drivers\SmsCountryDriver;
use Ashikul\IndiaSmsGateway\Drivers\TwoFactorDriver;
use InvalidArgumentException;

class GatewayRegistry
{
    public function __construct(private SettingsRepository $settings)
    {
    }

    public function all(): array
    {
        return [
            'msg91' => [
                'name' => 'MSG91',
                'description' => 'DLT-ready transactional SMS and OTP through MSG91.',
                'icon' => '01',
            ],
            'fast2sms' => [
                'name' => 'Fast2SMS',
                'description' => 'DLT, OTP and transactional messaging through Fast2SMS.',
                'icon' => '02',
            ],
            'firebasesms' => [
                'name' => 'FirebaseSMS',
                'description' => 'Configurable FirebaseSMS/local-provider API with username, password and DLT mapping.',
                'icon' => '03',
            ],
            'twofactor' => [
                'name' => '2Factor',
                'description' => 'OTP and transactional SMS through 2Factor.',
                'icon' => '04',
            ],
            'smscountry' => [
                'name' => 'SMSCountry',
                'description' => 'Indian transactional and bulk SMS through SMSCountry.',
                'icon' => '05',
            ],
            'kaleyra' => [
                'name' => 'Kaleyra',
                'description' => 'Enterprise messaging through Kaleyra APIs.',
                'icon' => '06',
            ],
            'generic' => [
                'name' => 'Local Indian Gateway',
                'description' => 'Connect local providers using configurable GET/POST fields, DLT Template ID and PEID.',
                'icon' => '07',
            ],
        ];
    }

    public function driver(string $key): SmsDriverInterface
    {
        return match ($key) {
            'msg91' => new Msg91Driver($this->settings),
            'fast2sms' => new Fast2SmsDriver($this->settings),
            'firebasesms' => new FirebaseSmsDriver($this->settings),
            'twofactor' => new TwoFactorDriver($this->settings),
            'smscountry' => new SmsCountryDriver($this->settings),
            'kaleyra' => new KaleyraDriver($this->settings),
            'generic' => new GenericHttpDriver($this->settings),
            default => throw new InvalidArgumentException(
                "Unknown SMS gateway: {$key}"
            ),
        };
    }

    public function enabled(string $key): bool
    {
        return $this->settings->bool('gateway_' . $key . '_enabled');
    }

    public function configured(string $key): bool
    {
        return match ($key) {
            'smscountry' => (bool) $this->settings->get(
                'gateway_smscountry_username'
            ) && (bool) $this->settings->secret(
                'gateway_smscountry_api_key'
            ),
            'kaleyra' => (bool) $this->settings->get(
                'gateway_kaleyra_account_id'
            ) && (bool) $this->settings->secret(
                'gateway_kaleyra_api_key'
            ),
            'generic', 'firebasesms' => (bool) $this->settings->get(
                'gateway_' . $key . '_endpoint'
            ) && (
                (bool) $this->settings->get(
                    'gateway_' . $key . '_username'
                )
                || (bool) $this->settings->secret(
                    'gateway_' . $key . '_password'
                )
                || (bool) $this->settings->secret(
                    'gateway_' . $key . '_api_key'
                )
            ),
            default => (bool) $this->settings->secret(
                'gateway_' . $key . '_api_key'
            ),
        };
    }
}
