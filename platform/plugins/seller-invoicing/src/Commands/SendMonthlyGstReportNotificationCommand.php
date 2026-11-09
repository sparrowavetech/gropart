<?php

namespace SparroWave\SellerInvoicing\Commands;

use Botble\Base\Facades\EmailHandler;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

class SendMonthlyGstReportNotificationCommand extends Command
{
    protected $signature = 'seller-invoicing:notify-monthly-gst {--period= : Tax period in YYYY-MM format} {--store= : Specific store ID}';

    protected $description = 'Send automated monthly GST report readiness email notifications to marketplace sellers';

    public function handle(GstReportService $reportService): int
    {
        if (! TaxEngine::isGstReportEnabled()) {
            $this->warn('Seller GST reporting is disabled (requires Indian billing mode & marketplace).');

            return self::SUCCESS;
        }

        $period = (string) ($this->option('period') ?: Carbon::now()->subMonth()->format('Y-m'));
        $storeIdOption = $this->option('store') ? (int) $this->option('store') : null;

        $this->info("Sending monthly GST report notifications for period: {$period}...");

        $result = $reportService->sendMonthlyGstNotifications($period, $storeIdOption);

        $this->info(sprintf(
            "Completed: %d sellers notified, Admin notified: %s (Active Stores: %d, Total Sales: ₹%s, Total Output GST: ₹%s, Total Marketplace ITC: ₹%s)",
            $result['sellers_notified'],
            $result['admin_notified'] ? 'YES' : 'NO',
            $result['active_stores'],
            number_format($result['total_sales'], 2),
            number_format($result['total_tax'], 2),
            number_format($result['total_itc'], 2)
        ));

        return self::SUCCESS;
    }
}

