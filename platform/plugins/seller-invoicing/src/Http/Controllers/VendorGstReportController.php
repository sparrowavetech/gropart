<?php

namespace SparroWave\SellerInvoicing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SparroWave\SellerInvoicing\Services\Gst\ExcelGstExport;
use SparroWave\SellerInvoicing\Services\Gst\GovtOfflineToolExport;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

class VendorGstReportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/seller-invoicing::seller-invoicing.gst_reports.menu_title'), route('marketplace.vendor.seller-invoicing.gst-reports.index'));
    }

    protected function getVendorStore(): ?Store
    {
        $customerId = auth('customer')->id();
        if (! $customerId) {
            return null;
        }

        return Store::query()->where('customer_id', $customerId)->first();
    }

    public function index(Request $request, GstReportService $reportService)
    {
        abort_unless(auth('customer')->check(), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $store = $this->getVendorStore();
        if (! $store) {
            return view('plugins/seller-invoicing::vendor.gst-reports.no-store');
        }

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.gst_reports.page_title'));

        $selectedPeriod = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $forceRefresh = (bool) $request->input('refresh', false);

        $reportData = $reportService->getReport($store->id, $selectedPeriod, $forceRefresh);
        $periods = $reportService->getAvailablePeriods();
        $financialYears = $reportService->getAvailableFinancialYears();

        return view('plugins/seller-invoicing::vendor.gst-reports.index', [
            'store' => $store,
            'report' => $reportData,
            'selectedPeriod' => $selectedPeriod,
            'periods' => $periods,
            'financialYears' => $financialYears,
        ]);
    }

    public function exportExcel(Request $request, GstReportService $reportService, ExcelGstExport $exporter)
    {
        abort_unless(auth('customer')->check(), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $store = $this->getVendorStore();
        abort_unless($store, 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $reportData = $reportService->getReport($store->id, $period);
        $filePath = $exporter->generate($reportData['gstr1'], $reportData['gstr3b']);

        $filename = 'GST_Report_' . str_replace('-', '_', $period) . '_' . ($store->tax_id ?: ('Store_' . $store->id)) . '.xlsx';

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    public function exportJson(Request $request, GstReportService $reportService, GovtOfflineToolExport $exporter)
    {
        abort_unless(auth('customer')->check(), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $store = $this->getVendorStore();
        abort_unless($store, 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $reportData = $reportService->getReport($store->id, $period);
        $json = $exporter->generateJson($reportData['gstr1']);

        $gstin = $store->tax_id ?: ($store->gstin ?: 'SELLER');
        $fp = $reportData['gstr1']['fp'] ?? date('mY');
        $filename = 'GSTR1_' . $gstin . '_' . $fp . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportCsvZip(Request $request, GstReportService $reportService, GovtOfflineToolExport $exporter)
    {
        abort_unless(auth('customer')->check(), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $store = $this->getVendorStore();
        abort_unless($store, 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $reportData = $reportService->getReport($store->id, $period);
        $zipPath = $exporter->generateCsvZip($reportData['gstr1']);

        if (! $zipPath || ! file_exists($zipPath)) {
            return back()->with('error_msg', 'Could not create CSV zip file.');
        }

        $gstin = $store->tax_id ?: ($store->gstin ?: 'SELLER');
        $fp = $reportData['gstr1']['fp'] ?? date('mY');
        $filename = 'GSTR1_Offline_CSVs_' . $gstin . '_' . $fp . '.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}

