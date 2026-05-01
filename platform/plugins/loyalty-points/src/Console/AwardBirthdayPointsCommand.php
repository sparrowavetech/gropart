<?php

namespace Botble\LoyaltyPoints\Console;

use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AwardBirthdayPointsCommand extends Command
{
    protected $signature = 'loyalty:award-birthday-points';

    protected $description = 'Award birthday points to customers whose birthday is today';

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

        $points = $this->loyaltyHelper->getPointsForBirthday();

        if ($points <= 0) {
            $this->info('Birthday points are disabled (set to 0).');

            return self::SUCCESS;
        }

        $today = now();
        $currentYear = $today->year;

        $customers = Customer::query()
            ->whereNotNull('dob')
            ->whereMonth('dob', $today->month)
            ->whereDay('dob', $today->day)
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No customers have birthdays today.');

            return self::SUCCESS;
        }

        $awarded = 0;

        foreach ($customers as $customer) {
            $alreadyAwarded = PointTransaction::query()
                ->where('customer_id', $customer->id)
                ->where('type', PointTransaction::TYPE_EARN)
                ->where('note', 'LIKE', '%birthday%')
                ->where(DB::raw('YEAR(created_at)'), $currentYear)
                ->exists();

            if ($alreadyAwarded) {
                continue;
            }

            $this->loyaltyPointService->awardBonusPoints(
                $customer->id,
                $points,
                trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_birthday', [
                    'year' => $currentYear,
                ])
            );

            $awarded++;
        }

        $this->components->info("Awarded birthday points to {$awarded} customer(s).");

        return self::SUCCESS;
    }
}
