<?php

namespace SparroWave\SellerInvoicing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SparroWave\SellerInvoicing\Services\Gst\ExcelGstExport;
use SparroWave\SellerInvoicing\Services\Gst\GovtOfflineToolExport;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

class AdminGstReportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.name'))
            ->add(trans('plugins/seller-invoicing::seller-invoicing.library'), route('seller-invoicing.index'));
    }

    public function index(Request $request, GstReportService $reportService)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.gst-reports') || auth()->user()?->hasPermission('seller-invoicing.index'), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.gst_reports.page_title'));

        $selectedPeriod = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $storeIdInput = $request->input('store_id');
        $selectedStoreId = ($storeIdInput !== null && $storeIdInput !== '') ? (int) $storeIdInput : null;
        $forceRefresh = (bool) $request->input('refresh', false);

        $reportData = $reportService->getReport($selectedStoreId, $selectedPeriod, $forceRefresh);
        $stores = $reportService->getStoresList();
        $periods = $reportService->getAvailablePeriods();
        $financialYears = $reportService->getAvailableFinancialYears();

        return view('plugins/seller-invoicing::admin.gst-reports.index', [
            'report' => $reportData,
            'selectedPeriod' => $selectedPeriod,
            'selectedStoreId' => $selectedStoreId,
            'stores' => $stores,
            'periods' => $periods,
            'financialYears' => $financialYears,
        ]);
    }

    public function exportExcel(Request $request, GstReportService $reportService, ExcelGstExport $exporter)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.gst-reports') || auth()->user()?->hasPermission('seller-invoicing.index'), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $storeId = $request->input('store_id') ? (int) $request->input('store_id') : null;

        $reportData = $reportService->getReport($storeId, $period);
        $filePath = $exporter->generate($reportData['gstr1'], $reportData['gstr3b']);

        $storeSuffix = $storeId ? ('_Store_' . $storeId) : '_All_Sellers';
        $filename = 'GST_Report_' . str_replace('-', '_', $period) . $storeSuffix . '.xlsx';

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    public function exportJson(Request $request, GstReportService $reportService, GovtOfflineToolExport $exporter)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.gst-reports') || auth()->user()?->hasPermission('seller-invoicing.index'), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $storeId = $request->input('store_id') ? (int) $request->input('store_id') : null;

        $reportData = $reportService->getReport($storeId, $period);
        $json = $exporter->generateJson($reportData['gstr1']);

        $defaultName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) (theme_option('site_title') ?: 'MARKETPLACE'))) ?: 'MARKETPLACE';
        $gstin = $reportData['gstr1']['gstin'] ?: $defaultName;
        $fp = $reportData['gstr1']['fp'] ?? date('mY');
        $filename = 'GSTR1_' . $gstin . '_' . $fp . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportCsvZip(Request $request, GstReportService $reportService, GovtOfflineToolExport $exporter)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.gst-reports') || auth()->user()?->hasPermission('seller-invoicing.index'), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $storeId = $request->input('store_id') ? (int) $request->input('store_id') : null;

        $reportData = $reportService->getReport($storeId, $period);
        $zipPath = $exporter->generateCsvZip($reportData['gstr1']);

        if (! $zipPath || ! file_exists($zipPath)) {
            return back()->with('error_msg', 'Could not create CSV zip file.');
        }

        $defaultName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) (theme_option('site_title') ?: 'MARKETPLACE'))) ?: 'MARKETPLACE';
        $gstin = $reportData['gstr1']['gstin'] ?: $defaultName;
        $fp = $reportData['gstr1']['fp'] ?? date('mY');
        $filename = 'GSTR1_Offline_CSVs_' . $gstin . '_' . $fp . '.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function sendNotifications(Request $request, GstReportService $reportService)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.gst-reports') || auth()->user()?->hasPermission('seller-invoicing.index'), 403);
        abort_unless(TaxEngine::isGstReportEnabled(), 404);

        $period = (string) $request->input('period', Carbon::now()->format('Y-m'));
        $storeId = $request->input('store_id') ? (int) $request->input('store_id') : null;

        $result = $reportService->sendMonthlyGstNotifications($period, $storeId);

        $msg = sprintf(
            'GST report email notifications sent: %d sellers notified, Administrator notified: %s (Active Stores: %d).',
            $result['sellers_notified'],
            $result['admin_notified'] ? 'Yes' : 'No',
            $result['active_stores']
        );

        return back()->with('success_msg', $msg);
    }
}

