<?php

namespace SparroWave\SellerInvoicing\Services\Gst;

use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use SparroWave\SellerInvoicing\Models\GstMonthlyReport;

class GstReportService
{
    public function __construct(
        protected Gstr1Engine $gstr1Engine,
        protected Gstr3bEngine $gstr3bEngine
    ) {
    }

    public function getReport(?int $storeId, string $taxPeriod, bool $forceRefresh = false): array
    {
        $periodCarbon = Carbon::createFromFormat('Y-m', $taxPeriod);
        $month = (int) $periodCarbon->format('n');
        $year = (int) $periodCarbon->format('Y');

        // Financial year e.g. 2026-27
        $fyStart = $month >= 4 ? $year : $year - 1;
        $financialYear = sprintf('%d-%02d', $fyStart, ($fyStart + 1) % 100);

        if ($storeId && ! $forceRefresh) {
            $cached = GstMonthlyReport::query()
                ->where('store_id', $storeId)
                ->where('tax_period', $taxPeriod)
                ->first();

            if ($cached && $cached->gstr1_payload && $cached->gstr3b_payload) {
                return [
                    'cached' => true,
                    'model' => $cached,
                    'tax_period' => $taxPeriod,
                    'financial_year' => $cached->financial_year,
                    'formatted_period' => $cached->formatted_period,
                    'gstr1' => $cached->gstr1_payload,
                    'gstr3b' => $cached->gstr3b_payload,
                    'order_count' => $cached->order_count,
                    'total_taxable_sales' => $cached->total_taxable_sales,
                    'total_output_tax' => $cached->total_output_tax,
                    'itc_gropart_fees' => $cached->itc_gropart_fees,
                    'net_gst_payable' => $cached->net_gst_payable,
                ];
            }
        }

        // Live compilation
        $gstr1 = $this->gstr1Engine->generate($storeId, $taxPeriod);
        $gstr3b = $this->gstr3bEngine->generate($gstr1, $storeId, $taxPeriod);

        $orderCount = (int) ($gstr1['order_count'] ?? 0);
        $totalTaxable = (float) ($gstr1['total_taxable_sales'] ?? 0.0);
        $totalOutputTax = (float) ($gstr1['total_output_tax'] ?? 0.0);
        $itcGropart = (float) ($gstr3b['itc_gropart_fees'] ?? 0.0);
        $netPayable = (float) ($gstr3b['net_gst_payable']['total'] ?? 0.0);

        $model = null;
        if ($storeId) {
            $store = Store::query()->find($storeId);
            $sellerId = $store?->customer_id ?: 0;

            $model = GstMonthlyReport::query()->updateOrCreate(
                [
                    'store_id' => $storeId,
                    'tax_period' => $taxPeriod,
                ],
                [
                    'seller_id' => $sellerId,
                    'financial_year' => $financialYear,
                    'month' => $month,
                    'year' => $year,
                    'order_count' => $orderCount,
                    'b2b_count' => (int) ($gstr1['b2b_count'] ?? 0),
                    'b2b_taxable' => (float) ($gstr1['b2b_taxable'] ?? 0.0),
                    'b2b_tax' => (float) ($gstr1['b2b_tax'] ?? 0.0),
                    'b2cs_count' => (int) ($gstr1['b2cs_count'] ?? 0),
                    'b2cs_taxable' => (float) ($gstr1['b2cs_taxable'] ?? 0.0),
                    'b2cs_tax' => (float) ($gstr1['b2cs_tax'] ?? 0.0),
                    'total_taxable_sales' => $totalTaxable,
                    'total_output_tax' => $totalOutputTax,
                    'itc_gropart_fees' => $itcGropart,
                    'net_gst_payable' => $netPayable,
                    'hsn_summary' => $gstr1['hsn'] ?? [],
                    'gstr1_payload' => $gstr1,
                    'gstr3b_payload' => $gstr3b,
                    'status' => 'ready',
                    'generated_at' => Carbon::now(),
                ]
            );
        }

        return [
            'cached' => false,
            'model' => $model,
            'tax_period' => $taxPeriod,
            'financial_year' => $financialYear,
            'formatted_period' => $periodCarbon->format('F Y'),
            'gstr1' => $gstr1,
            'gstr3b' => $gstr3b,
            'order_count' => $orderCount,
            'total_taxable_sales' => $totalTaxable,
            'total_output_tax' => $totalOutputTax,
            'itc_gropart_fees' => $itcGropart,
            'net_gst_payable' => $netPayable,
        ];
    }

    public function getAvailablePeriods(int $months = 18): array
    {
        $periods = [];
        $current = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $key = $current->format('Y-m');
            $label = $current->format('F Y');
            $periods[$key] = $label;
            $current->subMonth();
        }

        return $periods;
    }

    public function getAvailableFinancialYears(): array
    {
        $years = [];
        $currentYear = (int) Carbon::now()->format('Y');
        $currentMonth = (int) Carbon::now()->format('n');
        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;

        for ($i = 0; $i < 3; $i++) {
            $y = $startYear - $i;
            $code = sprintf('%d-%02d', $y, ($y + 1) % 100);
            $years[$code] = sprintf('FY %d-%d', $y, $y + 1);
        }

        return $years;
    }

    public function getStoresList(): Collection
    {
        return Store::query()
            ->select(['id', 'name', 'tax_id', 'gstin', 'state', 'customer_id'])
            ->orderBy('name', 'asc')
            ->get();
    }

    public function compileAllStoresForPeriod(string $taxPeriod): int
    {
        $stores = $this->getStoresList();
        $count = 0;

        foreach ($stores as $store) {
            $this->getReport($store->id, $taxPeriod, true);
            $count++;
        }

        return $count;
    }

    public function sendMonthlyGstNotifications(string $taxPeriod, ?int $targetStoreId = null): array
    {
        $query = Store::query()->whereNotNull('customer_id');
        if ($targetStoreId) {
            $query->where('id', $targetStoreId);
        }

        $stores = $query->get();
        $sellersNotified = 0;
        $totalPlatformTaxable = 0.0;
        $totalPlatformOutputTax = 0.0;
        $totalPlatformItc = 0.0;
        $activeStoresCount = 0;

        $siteTitle = (string) (theme_option('site_title') ?: get_ecommerce_setting('store_name', 'Marketplace'));

        foreach ($stores as $store) {
            $customer = $store->customer;
            $email = $store->email ?: ($customer?->email ?: '');

            $reportData = $this->getReport($store->id, $taxPeriod);
            $orderCount = (int) ($reportData['order_count'] ?? 0);
            $taxableSales = (float) ($reportData['total_taxable_sales'] ?? 0.0);
            $outputTax = (float) ($reportData['total_output_tax'] ?? 0.0);
            $itcFees = (float) ($reportData['itc_gropart_fees'] ?? 0.0);
            $netPayable = (float) ($reportData['net_gst_payable'] ?? 0.0);

            // Accumulate platform stats for active stores
            if ($orderCount > 0 || $itcFees > 0.0) {
                $activeStoresCount++;
                $totalPlatformTaxable += $taxableSales;
                $totalPlatformOutputTax += $outputTax;
                $totalPlatformItc += $itcFees;
            }

            if (! $email) {
                continue;
            }

            // Only notify seller if they had sales or claimable ITC
            if ($orderCount === 0 && $itcFees === 0.0) {
                continue;
            }

            $variables = [
                'seller_name' => $customer?->name ?: $store->name,
                'store_name' => $store->name,
                'seller_gstin' => $store->tax_id ?: ($store->gstin ?: 'N/A'),
                'tax_period' => $reportData['formatted_period'] ?? $taxPeriod,
                'total_taxable_sales' => number_format($taxableSales, 2),
                'total_output_tax' => number_format($outputTax, 2),
                'itc_gropart_fees' => number_format($itcFees, 2),
                'itc_marketplace_fees' => number_format($itcFees, 2),
                'net_gst_payable' => number_format($netPayable, 2),
                'report_url' => route('marketplace.vendor.seller-invoicing.gst-reports.index', ['period' => $taxPeriod]),
                'site_title' => $siteTitle,
            ];

            try {
                \Botble\Base\Facades\EmailHandler::setModule('seller-invoicing')
                    ->setVariableValues($variables)
                    ->sendUsingTemplate('monthly_gst_report', $email);
                $sellersNotified++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Send Admin Summary Notification
        $adminNotified = false;
        try {
            $adminEmails = function_exists('get_admin_email')
                ? get_admin_email()->toArray()
                : [get_ecommerce_setting('admin_email') ?: config('mail.from.address')];

            $periodCarbon = Carbon::createFromFormat('Y-m', $taxPeriod);
            $formattedPeriod = $periodCarbon->format('F Y');

            $adminVariables = [
                'admin_name' => 'Administrator',
                'tax_period' => $formattedPeriod,
                'stores_count' => (string) $activeStoresCount,
                'total_taxable_sales' => number_format($totalPlatformTaxable, 2),
                'total_output_tax' => number_format($totalPlatformOutputTax, 2),
                'total_marketplace_itc' => number_format($totalPlatformItc, 2),
                'report_url' => route('seller-invoicing.gst-reports.index', ['period' => $taxPeriod]),
                'site_title' => $siteTitle,
            ];

            foreach ($adminEmails as $adminEmail) {
                if ($adminEmail) {
                    \Botble\Base\Facades\EmailHandler::setModule('seller-invoicing')
                        ->setVariableValues($adminVariables)
                        ->sendUsingTemplate('admin_monthly_gst_summary', $adminEmail);
                    $adminNotified = true;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'sellers_notified' => $sellersNotified,
            'admin_notified' => $adminNotified,
            'active_stores' => $activeStoresCount,
            'total_sales' => $totalPlatformTaxable,
            'total_tax' => $totalPlatformOutputTax,
            'total_itc' => $totalPlatformItc,
        ];
    }
}

