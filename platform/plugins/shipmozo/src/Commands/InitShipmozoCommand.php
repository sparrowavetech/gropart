<?php

namespace SparroWave\Shipmozo\Commands;

use Botble\Setting\Facades\Setting;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:shipmozo:init', 'Configure ShipMozo API credentials')]
class InitShipmozoCommand extends Command
{
    use ConfirmableTrait;

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $publicKey = (string) $this->option('public-key');
        $privateKey = (string) $this->option('private-key');

        if ($publicKey === '' || $privateKey === '') {
            $this->components->error('Both --public-key and --private-key are required.');

            return self::INVALID;
        }

        $settings = [
            'shipping_shipmozo_public_key' => $publicKey,
            'shipping_shipmozo_private_key' => $privateKey,
            'shipping_shipmozo_status' => '1',
        ];

        if ($webhookSecret = $this->option('webhook-secret')) {
            if (strlen((string) $webhookSecret) < 16) {
                $this->components->error('The webhook secret must contain at least 16 characters.');

                return self::INVALID;
            }

            $settings['shipping_shipmozo_webhook_secret'] = (string) $webhookSecret;
            $settings['shipping_shipmozo_webhooks'] = '1';
        }

        Setting::set($settings)->save();

        $this->components->info('ShipMozo credentials configured successfully.');

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addOption('public-key', null, InputOption::VALUE_REQUIRED, 'ShipMozo public API key')
            ->addOption('private-key', null, InputOption::VALUE_REQUIRED, 'ShipMozo private API key')
            ->addOption('webhook-secret', null, InputOption::VALUE_OPTIONAL, 'Secret used to authenticate webhooks')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run in production');
    }
}
