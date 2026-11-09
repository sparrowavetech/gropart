<?php

namespace SparroWave\SellerInvoicing\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

class CompileMonthlyGstCommand extends Command
{
    protected $signature = 'seller-invoicing:compile-monthly-gst {--period= : Tax period in YYYY-MM format (defaults to previous month)} {--notify : Send automated email notifications to sellers and administrator}';

    protected $description = 'Pre-compile monthly GSTR-1 and GSTR-3B report snapshots for all marketplace stores and optionally send email reminders';

    public function handle(GstReportService $reportService): int
    {
        if (! TaxEngine::isGstReportEnabled()) {
            $this->warn('Seller GST reporting is disabled (requires Indian billing mode & marketplace).');

            return self::SUCCESS;
        }

        $period = (string) ($this->option('period') ?: Carbon::now()->subMonth()->format('Y-m'));
        $this->info("Compiling GST reports for period: {$period}");

        $count = $reportService->compileAllStoresForPeriod($period);
        $this->info("Successfully compiled GST reports for {$count} stores for period {$period}.");

        if ($this->option('notify')) {
            $this->info("Sending automated GST report email notifications...");
            $notifyResult = $reportService->sendMonthlyGstNotifications($period);
            $this->info(sprintf(
                "Email notifications completed: %d sellers notified, Admin notified: %s (Active stores: %d)",
                $notifyResult['sellers_notified'],
                $notifyResult['admin_notified'] ? 'YES' : 'NO',
                $notifyResult['active_stores']
            ));
        }

        return self::SUCCESS;
    }
}

