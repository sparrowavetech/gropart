<?php

namespace SparroWave\SellerInvoicing\Services;

use Illuminate\Support\Facades\File;
use SparroWave\SellerInvoicing\Models\Document;
use Throwable;
use ZipArchive;
use RuntimeException;

class InvoiceZipService
{
    public function createZipFromDocumentIds(array $documentIds, ?int $vendorCustomerId = null): string
    {
        $cleanIds = array_values(array_filter(array_map('intval', $documentIds)));

        if (empty($cleanIds)) {
            throw new RuntimeException(trans('plugins/seller-invoicing::seller-invoicing.no_invoices_selected'));
        }

        $query = Document::query()
            ->whereIn('id', $cleanIds)
            ->with(['order.store', 'seller', 'snapshot']);

        if ($vendorCustomerId !== null) {
            $query->where('seller_id', $vendorCustomerId);
        }

        $documents = $query->get();

        if ($documents->isEmpty()) {
            throw new RuntimeException(trans('plugins/seller-invoicing::seller-invoicing.no_documents'));
        }

        $tempDir = storage_path('app/temp/seller-invoices-zip');
        File::ensureDirectoryExists($tempDir);

        $zipFileName = sprintf('seller-invoices-%s-%s.zip', date('Ymd-His'), bin2hex(random_bytes(4)));
        $zipFilePath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        $status = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($status !== true) {
            throw new RuntimeException('Failed to create ZIP archive (code: ' . $status . ').');
        }

        $service = new GenerateSellerInvoiceService();
        $added = 0;

        foreach ($documents as $document) {
            try {
                $pdfPath = (clone $service)->document($document)->ensureSaved();
                if (File::exists($pdfPath) && filesize($pdfPath) > 0) {
                    $entryName = sprintf('seller-invoice-%s.pdf', str_replace(['/', '\\'], '-', $document->number));
                    $zip->addFile($pdfPath, $entryName);
                    $added++;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        $zip->close();

        if ($added === 0) {
            if (File::exists($zipFilePath)) {
                File::delete($zipFilePath);
            }
            throw new RuntimeException(trans('plugins/seller-invoicing::seller-invoicing.no_invoices_could_be_zipped'));
        }

        return $zipFilePath;
    }
}

