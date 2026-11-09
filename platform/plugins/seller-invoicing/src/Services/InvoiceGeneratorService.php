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
                ->where(function ($q) {
                    $q->where('type', RevenueTypeEnum::ADD_AMOUNT)
                        ->orWhereNull('type')
                        ->orWhere('type', '');
                })
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
            $fy = $this->resolveFinancialYear($fulfillmentDate);
            if ($existingDocument && ! empty($existingDocument->number) && str_contains($existingDocument->number, "/{$fy}/") && ! preg_match('/(?:000$|SIN000)/', $existingDocument->number) && strlen($existingDocument->number) <= 16) {
                $number = $existingDocument->number;
            } else {
                $number = $this->formatInvoiceNumber($existingDocument, $revenue, null, $fulfillmentDate);
            }
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
            ->where(function ($q) {
                $q->where('type', RevenueTypeEnum::ADD_AMOUNT)
                    ->orWhereNull('type')
                    ->orWhere('type', '');
            })
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

    public function backfillAllHistoricalOrders(): int
    {
        $existingOrderIds = Document::query()->pluck('order_id')->filter()->all();

        $revenueOrderQuery = Revenue::query()
            ->where(function ($q) {
                $q->where('type', RevenueTypeEnum::ADD_AMOUNT)
                    ->orWhereNull('type')
                    ->orWhere('type', '');
            })
            ->whereNotNull('order_id');

        if (! empty($existingOrderIds)) {
            $revenueOrderQuery->whereNotIn('order_id', $existingOrderIds);
        }

        $orderIds = $revenueOrderQuery
            ->distinct()
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

    public function resolveFinancialYear(Carbon $date): string
    {
        $y = (int) $date->format('y');
        $m = (int) $date->format('n');
        if ($m >= 4) {
            return sprintf('%02d-%02d', $y, ($y + 1) % 100);
        }

        return sprintf('%02d-%02d', ($y - 1) % 100, $y);
    }

    public function getInvoicePrefix(): string
    {
        $prefix = (string) (setting('seller_invoicing_prefix') ?: setting('seller_invoice_code_prefix', 'GP'));
        $clean = rtrim($prefix, '/-');
        if ($clean === 'GPINV' || $clean === 'GPINV-') {
            $clean = 'GP';
        }

        return $clean ?: 'GP';
    }

    public function getInvoiceSuffix(): string
    {
        $suffix = (string) setting('seller_invoice_code_suffix');
        if (! $suffix) {
            $suffix = (string) setting('seller_invoicing_suffix', 'SIN');
        }
        $clean = trim($suffix, '/-');
        if (empty($clean) || $clean === 'SINV') {
            $clean = 'SIN';
        }

        return $clean;
    }

    public function formatInvoiceNumber(?Document $document = null, ?Revenue $revenue = null, ?int $sequence = null, ?Carbon $dateOverride = null): string
    {
        $prefix = $this->getInvoicePrefix();
        $suffix = $this->getInvoiceSuffix();

        // Determine date in dmY format (e.g. 09112026)
        if ($dateOverride) {
            $dateObj = $dateOverride;
        } elseif ($document?->issued_at) {
            $dateObj = $document->issued_at instanceof Carbon ? $document->issued_at : Carbon::parse($document->issued_at);
        } elseif ($revenue?->created_at) {
            $dateObj = $revenue->created_at instanceof Carbon ? $revenue->created_at : Carbon::parse($revenue->created_at);
        } elseif ($document?->created_at) {
            $dateObj = $document->created_at instanceof Carbon ? $document->created_at : Carbon::parse($document->created_at);
        } else {
            $dateObj = now();
        }
        $dateStr = $dateObj->format('dmY');

        if ($sequence !== null && $sequence > 0) {
            $seqStr = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            return sprintf('%s%s%s%s', $prefix, $dateStr, $suffix, $seqStr);
        }

        // If existing document already has a valid sequential number, preserve its sequence
        if ($document && ! empty($document->number)) {
            if (preg_match('/(?:' . preg_quote($suffix, '/') . '|SIN)(\d+)$/i', $document->number, $matches)) {
                $seqInt = (int) $matches[1];
                if ($seqInt > 0) {
                    $seqStr = str_pad((string) $seqInt, 3, '0', STR_PAD_LEFT);
                    return sprintf('%s%s%s%s', $prefix, $dateStr, $suffix, $seqStr);
                }
            } elseif (preg_match('/(\d{1,6})$/', $document->number, $matches)) {
                $seqInt = (int) $matches[1];
                if ($seqInt > 0) {
                    $seqStr = str_pad((string) $seqInt, 3, '0', STR_PAD_LEFT);
                    return sprintf('%s%s%s%s', $prefix, $dateStr, $suffix, $seqStr);
                }
            }
        }

        // Compute next sequence from existing documents
        $existingDocNumbers = Document::query()
            ->whereNotNull('number')
            ->where('number', '!=', '')
            ->where('number', 'not like', 'TMP-%')
            ->pluck('number');

        $maxSeq = 0;
        foreach ($existingDocNumbers as $docNum) {
            if (preg_match('/(?:' . preg_quote($suffix, '/') . '|SIN)(\d+)$/i', $docNum, $m)) {
                $val = (int) $m[1];
                if ($val > $maxSeq) {
                    $maxSeq = $val;
                }
            } elseif (preg_match('/(\d{1,6})$/', $docNum, $m)) {
                $val = (int) $m[1];
                if ($val > $maxSeq) {
                    $maxSeq = $val;
                }
            }
        }

        $nextSeq = max(1, $maxSeq + 1);
        $seqStr = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        return sprintf('%s%s%s%s', $prefix, $dateStr, $suffix, $seqStr);
    }

    public function renumberAllDocumentsChronologically(?string $prefix = null): int
    {
        $prefix = $prefix ?: $this->getInvoicePrefix();
        $suffix = $this->getInvoiceSuffix();
        $documents = Document::query()->with(['order'])->get();

        // Sort all platform documents chronologically by fulfillment/issued_at asc, then order_id asc
        $sortedDocs = $documents->all();
        usort($sortedDocs, function ($a, $b) {
            $dateA = $a->issued_at ?: ($a->order ? $this->resolveOrderFulfillmentDate($a->order) : ($a->created_at ?: now()));
            $dateB = $b->issued_at ?: ($b->order ? $this->resolveOrderFulfillmentDate($b->order) : ($b->created_at ?: now()));
            $timeA = $dateA instanceof Carbon ? $dateA->timestamp : Carbon::parse($dateA)->timestamp;
            $timeB = $dateB instanceof Carbon ? $dateB->timestamp : Carbon::parse($dateB)->timestamp;

            if ($timeA === $timeB) {
                return (int) $a->order_id <=> (int) $b->order_id;
            }

            return $timeA <=> $timeB;
        });

        // Prepare sequential targets: {prefix}{date}{suffix}{seq} (e.g. GP09112026SIN001, GP09112026SIN818)
        $plan = [];
        $seq = 1;
        foreach ($sortedDocs as $doc) {
            $date = $doc->issued_at ?: ($doc->order ? $this->resolveOrderFulfillmentDate($doc->order) : ($doc->created_at ?: now()));
            $dateObj = $date instanceof Carbon ? $date : Carbon::parse($date);
            $dateStr = $dateObj->format('dmY');

            $seqStr = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            $targetNumber = sprintf('%s%s%s%s', $prefix, $dateStr, $suffix, $seqStr);
            $safeNumber = str_replace(['/', '\\'], '-', $targetNumber);
            $targetPdfPath = sprintf('marketplace/seller-invoices/seller-invoice-%s.pdf', $safeNumber);

            $changed = ($doc->number !== $targetNumber || $doc->pdf_path !== $targetPdfPath);
            $plan[] = [
                'doc' => $doc,
                'date' => $dateObj,
                'target_number' => $targetNumber,
                'target_pdf' => $targetPdfPath,
                'changed' => $changed,
            ];
            $seq++;
        }

        $itemsToUpdate = array_filter($plan, fn ($item) => $item['changed']);
        if (empty($itemsToUpdate)) {
            return 0;
        }

        $updatedCount = 0;

        // Two-Phase atomic update to avoid unique constraint collisions (si_documents_kind_number_unique)
        DB::transaction(function () use ($itemsToUpdate, &$updatedCount) {
            $docIds = array_map(fn ($item) => $item['doc']->id, $itemsToUpdate);

            // PHASE 1: Assign temporary non-colliding numbers to clear the unique constraint namespace
            DB::table('si_documents')
                ->whereIn('id', $docIds)
                ->update([
                    'number' => DB::raw("CONCAT('TMP-', id, '-', UNIX_TIMESTAMP())"),
                    'sha256' => null,
                ]);

            // PHASE 2: Assign final sequential numbers and clean up obsolete PDF files
            foreach ($itemsToUpdate as $item) {
                $doc = $item['doc'];
                if ($doc->pdf_path && $doc->pdf_path !== $item['target_pdf'] && File::exists(storage_path('app/' . $doc->pdf_path))) {
                    File::delete(storage_path('app/' . $doc->pdf_path));
                }

                DB::table('si_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'number' => $item['target_number'],
                        'issued_at' => $item['date'],
                        'pdf_path' => $item['target_pdf'],
                        'sha256' => null,
                        'updated_at' => now(),
                    ]);

                $doc->number = $item['target_number'];
                $doc->issued_at = $item['date'];
                $doc->pdf_path = $item['target_pdf'];
                $doc->sha256 = null;
                $updatedCount++;
            }
        });

        return $updatedCount;
    }

    public function generateNextInvoiceNumber(?Revenue $revenue = null): string
    {
        return $this->formatInvoiceNumber(null, $revenue);
    }

    public function getTotalInvoiceCount(): int
    {
        return Document::query()->count();
    }

    public function regenerateSingleDocument(Document $document): bool
    {
        $order = $document->order;
        if (! $order) {
            return false;
        }

        $revenue = Revenue::query()
            ->where('order_id', $order->getKey())
            ->where(function ($q) {
                $q->where('type', RevenueTypeEnum::ADD_AMOUNT)
                    ->orWhereNull('type')
                    ->orWhere('type', '');
            })
            ->first();

        if (! $revenue) {
            return false;
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

        $prefix = $this->getInvoicePrefix();
        $suffix = $this->getInvoiceSuffix();
        $number = $document->number;
        if (empty($number) || preg_match('/(?:000$|SIN000)/', $number) || str_starts_with($number, 'TMP-') || ! str_contains($number, $suffix)) {
            $number = $this->formatInvoiceNumber($document, $revenue, null, $fulfillmentDate);
        }

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

        return true;
    }

    public function regenerateBatch(int $offset = 0, int $limit = 25): array
    {
        if ($offset === 0) {
            $this->backfillAllHistoricalOrders();
            $this->renumberAllDocumentsChronologically();
        }

        $total = $this->getTotalInvoiceCount();
        $documents = Document::query()
            ->with(['order.store', 'order'])
            ->orderBy('id', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $processedInBatch = 0;
        foreach ($documents as $document) {
            if ($this->regenerateSingleDocument($document)) {
                $processedInBatch++;
            }
        }

        $processedTotal = min($offset + $documents->count(), $total);
        $finished = $processedTotal >= $total || $documents->isEmpty();

        return [
            'total' => $total,
            'processed' => $processedTotal,
            'next_offset' => $offset + $limit,
            'finished' => $finished,
            'percentage' => $total > 0 ? (int) round(($processedTotal / $total) * 100) : 100,
        ];
    }

    public function regenerateAllInvoices(): int
    {
        $this->backfillAllHistoricalOrders();
        $this->renumberAllDocumentsChronologically();

        $documents = Document::query()->with(['order.store', 'order'])->get();
        $count = 0;

        foreach ($documents as $document) {
            if ($this->regenerateSingleDocument($document)) {
                $count++;
            }
        }

        return $count;
    }
}
