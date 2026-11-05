<?php

namespace SparroWave\SellerInvoicing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\Request;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;
use SparroWave\SellerInvoicing\Services\InvoiceZipService;
use SparroWave\SellerInvoicing\Tables\VendorSellerInvoiceTable;
use Throwable;

class VendorController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/seller-invoicing::seller-invoicing.library'), route('marketplace.vendor.seller-invoicing.index'));
    }

    public function index(VendorSellerInvoiceTable $table, Request $request, InvoiceGeneratorService $generator)
    {
        $customerId = auth('customer')->id();
        abort_unless($customerId, 403);

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.library'));

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
        $customerId = auth('customer')->id();
        abort_unless((int) $document->seller_id === (int) $customerId, 404);

        $this->breadcrumb()->add(
            trans('plugins/seller-invoicing::seller-invoicing.document_title', ['number' => $document->number]),
            route('marketplace.vendor.seller-invoicing.show', $document->getKey())
        );

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.document_title', ['number' => $document->number]));

        return view('plugins/seller-invoicing::show', [
            'document' => $document->load(['order.store', 'snapshot']),
            'vendor' => true,
        ]);
    }

    public function showByOrder(Order $order, InvoiceGeneratorService $generator)
    {
        $customerId = auth('customer')->id();
        abort_unless((int) $order->store?->customer_id === (int) $customerId, 404);

        $document = Document::query()
            ->where('seller_id', $customerId)
            ->where('order_id', $order->getKey())
            ->first();

        if (! $document) {
            $document = $generator->generateForOrder($order);
        }

        if ($document) {
            return redirect()->route('marketplace.vendor.seller-invoicing.show', $document->getKey());
        }

        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.order_record') . ' — #' . $order->getKey());

        return view('plugins/seller-invoicing::order-not-ready', [
            'order' => $order,
            'snapshot' => null,
            'vendor' => true,
        ]);
    }

    public function download(Document $document, GenerateSellerInvoiceService $service)
    {
        $customerId = auth('customer')->id();
        abort_unless((int) $document->seller_id === (int) $customerId, 404);

        return $service->document($document)->download();
    }

    public function print(Document $document, GenerateSellerInvoiceService $service)
    {
        $customerId = auth('customer')->id();
        abort_unless((int) $document->seller_id === (int) $customerId, 404);

        return $service->document($document)->stream();
    }

    public function bulkDownload(Request $request, InvoiceZipService $zipService)
    {
        $customerId = auth('customer')->id();
        abort_unless($customerId, 403);

        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error_msg', trans('plugins/seller-invoicing::seller-invoicing.no_invoices_selected'));
        }

        try {
            $zipPath = $zipService->createZipFromDocumentIds($ids, (int) $customerId);
            $downloadName = sprintf('seller-invoices-%s.zip', date('Y-m-d'));

            return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error_msg', $e->getMessage());
        }
    }

    public function filterInput(Request $request, VendorSellerInvoiceTable $table)
    {
        $customerId = auth('customer')->id();
        abort_unless($customerId, 403);

        $key = $request->input('key');
        $value = $request->input('value');
        $filters = $table->getFilters();
        $column = $filters[$key] ?? null;

        if (! $column) {
            return response()->json(['html' => '', 'data' => []]);
        }

        $res = $table->getValueInput(null, $value, $column['type'], $column['choices'] ?? []);

        return response()->json($res);
    }
}
