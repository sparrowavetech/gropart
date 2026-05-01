<?php

namespace Botble\LoyaltyPoints\Console;

use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Console\Command;

class ExpirePointsCommand extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire old loyalty points based on expiry date';

    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            $this->info('Loyalty program is disabled.');

            return self::SUCCESS;
        }

        $expiryMonths = $this->loyaltyHelper->getPointsExpiryMonths();

        if ($expiryMonths <= 0) {
            $this->info('Points expiry is disabled (set to 0).');

            return self::SUCCESS;
        }

        $expiredCount = $this->loyaltyPointService->expireOldPoints();

        $this->components->info("Expired points for {$expiredCount} transaction(s).");

        return self::SUCCESS;
    }
}
