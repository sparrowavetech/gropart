<?php

namespace SparroWave\SellerInvoicing\Services;

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\Shipment;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;
use SparroWave\SellerInvoicing\Supports\SnapshotBuilder;
use SparroWave\SellerInvoicing\Supports\TaxEngine;
use Throwable;

class InvoiceGeneratorService
{
    public function __construct(
        protected SnapshotBuilder $snapshotBuilder,
        protected TaxEngine $taxEngine
    ) {
    }

    public function generateForOrder(Order $order, bool $force = false): ?Document
    {
        return DB::transaction(function () use ($order, $force): ?Document {
            $revenue = Revenue::query()
                ->where('order_id', $order->getKey())
                ->where('type', RevenueTypeEnum::ADD_AMOUNT)
                ->first();

            if (! $revenue) {
                return null;
            }

            try {
                $snapshot = $this->snapshotBuilder->prepare($order, 'invoice_generation');
            } catch (Throwable $e) {
                report($e);

                return null;
            }

            $existingDocument = Document::query()
                ->where('snapshot_id', $snapshot->getKey())
                ->where('kind', 'invoice')
                ->first();

            if ($existingDocument && ! $force) {
                return $existingDocument;
            }

            $store = $order->store;
            if (! $store || (int) $store->customer_id !== (int) $revenue->customer_id) {
                $revenueStore = \Botble\Marketplace\Models\Store::query()
                    ->where('customer_id', $revenue->customer_id)
                    ->first();
                if ($revenueStore) {
                    $store = $revenueStore;
                }
            }

            $fulfillmentDate = $this->resolveOrderFulfillmentDate($order, $revenue);
            $taxResult = $this->taxEngine->resolve($order, $revenue, $store);
            $number = $existingDocument?->number ?: $this->formatInvoiceNumber($existingDocument, $revenue, null, $fulfillmentDate);
            $currency = (string) ($revenue->currency ?: 'INR');

            $safeNumber = str_replace(['/', '\\'], '-', $number);

            $documentData = [
                'snapshot_id' => $snapshot->getKey(),
                'seller_id' => $revenue->customer_id,
                'order_id' => $order->getKey(),
                'kind' => 'invoice',
                'status' => 'issued',
                'number' => $number,
                'currency' => $currency,
                'lines' => $taxResult['lines'],
                'taxes' => $taxResult['taxes'],
                'subtotal' => $taxResult['subtotal'],
                'tax_amount' => $taxResult['tax_amount'],
                'grand_total' => $taxResult['grand_total'],
                'issued_at' => $fulfillmentDate,
                'pdf_path' => sprintf('marketplace/seller-invoices/seller-invoice-%s.pdf', $safeNumber),
            ];

            if ($existingDocument) {
                $existingDocument->update($documentData);
                $document = $existingDocument;
            } else {
                $document = Document::query()->create($documentData);
            }

            $snapshot->update([
                'status' => 'issued',
            ]);

            return $document;
        });
    }

    public function backfillHistoricalOrders(int $limit = 10): int
    {
        $existingOrderIds = Document::query()->pluck('order_id')->filter()->all();

        $revenueOrderQuery = Revenue::query()
            ->where('type', RevenueTypeEnum::ADD_AMOUNT)
            ->whereNotNull('order_id');

        if (! empty($existingOrderIds)) {
            $revenueOrderQuery->whereNotIn('order_id', $existingOrderIds);
        }

        $orderIds = $revenueOrderQuery
            ->distinct()
            ->latest('id')
            ->limit($limit)
            ->pluck('order_id')
            ->all();

        if (empty($orderIds)) {
            return 0;
        }

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->whereIn('status', [OrderStatusEnum::COMPLETED, 'completed'])
            ->with(['store'])
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            try {
                $doc = $this->generateForOrder($order);
                if ($doc) {
                    $count++;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $count;
    }

    public function resolveOrderFulfillmentDate(Order $order, ?Revenue $revenue = null): Carbon
    {
        $history = OrderHistory::query()
            ->where('order_id', $order->getKey())
            ->where(function ($q) {
                $q->where('action', 'mark_order_as_completed')
                    ->orWhere(function ($sq) {
                        $sq->where('action', 'update_shipping_status')
                            ->where('description', 'like', '%Delivered%');
                    });
            })
            ->latest('id')
            ->first();

        if ($history && $history->created_at) {
            return Carbon::parse($history->created_at);
        }

        $shipment = Shipment::query()
            ->where('order_id', $order->getKey())
            ->whereIn('status', ['delivered', 'completed'])
            ->latest('id')
            ->first();

        if ($shipment && ($shipment->date_shipped ?: $shipment->updated_at)) {
            return Carbon::parse($shipment->date_shipped ?: $shipment->updated_at);
        }

        if ($revenue && $revenue->created_at) {
            return Carbon::parse($revenue->created_at);
        }

        if ($order->status == 'completed' && $order->updated_at) {
            return Carbon::parse($order->updated_at);
        }

        return Carbon::parse($order->created_at ?: now());
    }

    public function formatInvoiceNumber(?Document $document = null, ?Revenue $revenue = null, ?int $sequence = null, ?Carbon $dateOverride = null): string
    {
        $prefix = (string) (setting('seller_invoicing_prefix') ?: setting('seller_invoice_code_prefix', 'GPINV-'));
        $suffix = (string) setting('seller_invoicing_suffix');
        if (! $suffix) {
            $legacySuffix = (string) setting('seller_invoice_code_suffix');
            $suffix = ($legacySuffix && $legacySuffix !== 'SINV') ? $legacySuffix : 'SIN';
        }

        if (! $prefix) {
            $prefix = 'GPINV-';
        }

        // Determine date in dmY format (e.g. 07082026)
        if ($dateOverride) {
            $date = $dateOverride->format('dmY');
        } elseif ($document?->issued_at) {
            $date = $document->issued_at->format('dmY');
        } elseif ($revenue?->created_at) {
            $date = $revenue->created_at->format('dmY');
        } elseif ($document?->created_at) {
            $date = $document->created_at->format('dmY');
        } else {
            $date = now()->format('dmY');
        }

        // Determine sequence number
        $num = $sequence;
        if ($num === null && $document) {
            if (preg_match('/(?:SINV|SIN)(\d+)$/i', $document->number, $matches)) {
                $num = (int) $matches[1];
            } elseif (preg_match('/(\d+)$/', $document->number, $matches)) {
                $num = (int) $matches[1];
            } else {
                $num = $document->getKey();
            }
        }

        if ($num === null) {
            $maxId = (int) Document::query()->max('id');
            $num = $maxId + 1;
        }

        $paddedNum = str_pad((string) $num, 3, '0', STR_PAD_LEFT);

        return sprintf('%s%s%s%s', $prefix, $date, $suffix, $paddedNum);
    }

    public function generateNextInvoiceNumber(?Revenue $revenue = null): string
    {
        return $this->formatInvoiceNumber(null, $revenue);
    }

    public function regenerateAllInvoices(): int
    {
        $documents = Document::query()->with(['order.store', 'order'])->get();
        $count = 0;

        foreach ($documents as $document) {
            $order = $document->order;
            if (! $order) {
                continue;
            }

            $revenue = Revenue::query()
                ->where('order_id', $order->getKey())
                ->where('type', RevenueTypeEnum::ADD_AMOUNT)
                ->first();

            if (! $revenue) {
                continue;
            }

            $store = $order->store;
            if (! $store || (int) $store->customer_id !== (int) $revenue->customer_id) {
                $revenueStore = \Botble\Marketplace\Models\Store::query()
                    ->where('customer_id', $revenue->customer_id)
                    ->first();
                if ($revenueStore) {
                    $store = $revenueStore;
                }
            }

            $fulfillmentDate = $this->resolveOrderFulfillmentDate($order, $revenue);
            $taxResult = $this->taxEngine->resolve($order, $revenue, $store);
            $number = $this->formatInvoiceNumber($document, $revenue, null, $fulfillmentDate);

            $safeNumber = str_replace(['/', '\\'], '-', $number);
            $pdfRelativePath = sprintf('marketplace/seller-invoices/seller-invoice-%s.pdf', $safeNumber);
            $fullPdfPath = storage_path('app/' . $pdfRelativePath);

            // Clean up old cached PDF
            if (File::exists($fullPdfPath)) {
                File::delete($fullPdfPath);
            }
            if ($document->pdf_path && File::exists(storage_path('app/' . $document->pdf_path))) {
                File::delete(storage_path('app/' . $document->pdf_path));
            }

            $document->update([
                'number' => $number,
                'issued_at' => $fulfillmentDate,
                'lines' => $taxResult['lines'],
                'taxes' => $taxResult['taxes'],
                'subtotal' => $taxResult['subtotal'],
                'tax_amount' => $taxResult['tax_amount'],
                'grand_total' => $taxResult['grand_total'],
                'pdf_path' => $pdfRelativePath,
                'sha256' => null,
            ]);

            if ($document->snapshot) {
                $document->snapshot->update([
                    'status' => 'issued',
                ]);
            }

            $count++;
        }

        return $count;
    }
}
