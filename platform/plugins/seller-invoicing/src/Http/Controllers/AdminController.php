<?php

namespace SparroWave\SellerInvoicing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;
use SparroWave\SellerInvoicing\Services\InvoiceZipService;
use SparroWave\SellerInvoicing\Tables\SellerInvoiceTable;
use Throwable;

class AdminController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.name'))
            ->add(trans('plugins/seller-invoicing::seller-invoicing.library'), route('seller-invoicing.index'));
    }

    public function index(SellerInvoiceTable $table, Request $request, InvoiceGeneratorService $generator)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.library'));

        // Automatically backfill completed historical marketplace orders only on initial page view (not on AJAX calls)
        if (! $request->ajax() && ! $request->wantsJson()) {
            try {
                $generator->backfillHistoricalOrders(10);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $table->renderTable();
    }

    public function show(Document $document)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.show') || auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        $this->breadcrumb()->add(
            trans('plugins/seller-invoicing::seller-invoicing.document_title', ['number' => $document->number]),
            route('seller-invoicing.show', $document->getKey())
        );

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.document_title', ['number' => $document->number]));

        return view('plugins/seller-invoicing::show', [
            'document' => $document->load(['order.store', 'snapshot']),
            'vendor' => false,
        ]);
    }

    public function showByOrder(Order $order, InvoiceGeneratorService $generator)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.show') || auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        $document = Document::query()->where('order_id', $order->getKey())->first();

        if (! $document) {
            try {
                $document = $generator->generateForOrder($order);
            } catch (Throwable $e) {
                report($e);
            }
        }

        if ($document) {
            return redirect()->route('seller-invoicing.show', $document->getKey());
        }

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.order_record') . ' — #' . $order->getKey());

        $snapshot = OrderSnapshot::query()->where('order_id', $order->getKey())->first();

        return view('plugins/seller-invoicing::order-not-ready', [
            'order' => $order,
            'snapshot' => $snapshot,
            'vendor' => false,
        ]);
    }

    public function generate(Order $order, InvoiceGeneratorService $generator): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.generate') || auth()->user()?->hasPermission('seller-invoicing.manage'), 403);

        try {
            $document = $generator->generateForOrder($order, true);

            if ($document) {
                return redirect()->route('seller-invoicing.show', $document->getKey())
                    ->with('success_msg', trans('plugins/seller-invoicing::seller-invoicing.generated_successfully'));
            }

            return back()->with('error_msg', trans('plugins/seller-invoicing::seller-invoicing.errors.no_revenue'));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error_msg', trans('plugins/seller-invoicing::seller-invoicing.errors.prepare_failed'));
        }
    }

    public function download(Document $document, GenerateSellerInvoiceService $service)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.show') || auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        return $service->document($document)->download();
    }

    public function print(Document $document, GenerateSellerInvoiceService $service)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.show') || auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        return $service->document($document)->stream();
    }

    public function bulkDownload(Request $request, InvoiceZipService $zipService)
    {
        abort_unless(auth()->user()?->hasPermission('seller-invoicing.show') || auth()->user()?->hasPermission('seller-invoicing.index') || auth()->user()?->hasPermission('seller-invoicing.view'), 403);

        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error_msg', trans('plugins/seller-invoicing::seller-invoicing.no_invoices_selected'));
        }

        try {
            $zipPath = $zipService->createZipFromDocumentIds($ids);
            $downloadName = sprintf('seller-invoices-%s.zip', date('Y-m-d'));

            return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error_msg', $e->getMessage());
        }
    }
}
