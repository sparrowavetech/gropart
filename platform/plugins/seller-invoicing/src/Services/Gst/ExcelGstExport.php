<?php

namespace SparroWave\SellerInvoicing\Services\Gst;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGstExport
{
    public function generate(array $gstr1Data, array $gstr3bData): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default empty sheet

        // ----------------------------------------------------
        // Sheet 1: GSTR-3B Summary
        // ----------------------------------------------------
        $sheet3b = $spreadsheet->createSheet();
        $sheet3b->setTitle('GSTR-3B Summary');

        $siteTitle = strtoupper((string) (theme_option('site_title') ?: get_ecommerce_setting('store_name', 'Marketplace')));
        $sheet3b->setCellValue('A1', $siteTitle . ' - GSTR-3B SUMMARY & FILING GUIDE');
        $sheet3b->mergeCells('A1:F1');
        $sheet3b->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet3b->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet3b->setCellValue('A2', 'Seller GSTIN: ' . ($gstr1Data['gstin'] ?: 'N/A') . ' | Tax Period: ' . ($gstr1Data['tax_period'] ?? ''));
        $sheet3b->mergeCells('A2:F2');
        $sheet3b->getStyle('A2')->getFont()->setItalic(true);

        // Section 3.1
        $sheet3b->setCellValue('A4', '3.1 Details of Outward Supplies and inward supplies liable to reverse charge');
        $sheet3b->mergeCells('A4:F4');
        $sheet3b->getStyle('A4')->getFont()->setBold(true);

        $headers31 = ['Nature of Supplies', 'Total Taxable Value (₹)', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Cess (₹)'];
        $col = 'A';
        foreach ($headers31 as $h) {
            $sheet3b->setCellValue($col . '5', $h);
            $sheet3b->getStyle($col . '5')->getFont()->setBold(true);
            $sheet3b->getStyle($col . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }

        $rowIdx = 6;
        foreach ($gstr3bData['table_3_1'] ?? [] as $row) {
            $sheet3b->setCellValue('A' . $rowIdx, $row['description'] ?? '');
            $sheet3b->setCellValue('B' . $rowIdx, (float) ($row['txval'] ?? 0));
            $sheet3b->setCellValue('C' . $rowIdx, (float) ($row['iamt'] ?? 0));
            $sheet3b->setCellValue('D' . $rowIdx, (float) ($row['camt'] ?? 0));
            $sheet3b->setCellValue('E' . $rowIdx, (float) ($row['samt'] ?? 0));
            $sheet3b->setCellValue('F' . $rowIdx, 0.0);
            $rowIdx++;
        }

        // Section 4 ITC
        $rowIdx += 2;
        $sheet3b->setCellValue('A' . $rowIdx, '4. Eligible Input Tax Credit (ITC) - Claimable on Marketplace Platform & Courier Fees');
        $sheet3b->mergeCells('A' . $rowIdx . ':F' . $rowIdx);
        $sheet3b->getStyle('A' . $rowIdx)->getFont()->setBold(true);

        $rowIdx++;
        $headers4 = ['Details', 'Taxable Value (₹)', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Total ITC (₹)'];
        $col = 'A';
        foreach ($headers4 as $h) {
            $sheet3b->setCellValue($col . $rowIdx, $h);
            $sheet3b->getStyle($col . $rowIdx)->getFont()->setBold(true);
            $sheet3b->getStyle($col . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD4EDDA');
            $col++;
        }

        $rowIdx++;
        $itcRow = $gstr3bData['table_4_itc']['itc_available']['all_other_itc'] ?? [];
        $sheet3b->setCellValue('A' . $rowIdx, $itcRow['description'] ?? '(5) All other ITC');
        $sheet3b->setCellValue('B' . $rowIdx, (float) ($itcRow['txval'] ?? 0));
        $sheet3b->setCellValue('C' . $rowIdx, (float) ($itcRow['iamt'] ?? 0));
        $sheet3b->setCellValue('D' . $rowIdx, (float) ($itcRow['camt'] ?? 0));
        $sheet3b->setCellValue('E' . $rowIdx, (float) ($itcRow['samt'] ?? 0));
        $sheet3b->setCellValue('F' . $rowIdx, (float) ($itcRow['total'] ?? 0));

        // Net Payable Box
        $rowIdx += 2;
        $net = $gstr3bData['net_gst_payable'] ?? [];
        $sheet3b->setCellValue('A' . $rowIdx, 'NET GST PAYABLE IN CASH (Output Liability minus Marketplace ITC)');
        $sheet3b->mergeCells('A' . $rowIdx . ':D' . $rowIdx);
        $sheet3b->getStyle('A' . $rowIdx)->getFont()->setBold(true);
        $sheet3b->setCellValue('E' . $rowIdx, 'Total Payable:');
        $sheet3b->setCellValue('F' . $rowIdx, (float) ($net['total'] ?? 0));
        $sheet3b->getStyle('E' . $rowIdx . ':F' . $rowIdx)->getFont()->setBold(true);
        $sheet3b->getStyle('A' . $rowIdx . ':F' . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFD2');

        foreach (range('A', 'F') as $c) {
            $sheet3b->getColumnDimension($c)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // Sheet 2: b2b
        // ----------------------------------------------------
        $sheetB2b = $spreadsheet->createSheet();
        $sheetB2b->setTitle('b2b');
        $b2bHeaders = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Invoice Number',
            'Invoice date',
            'Invoice Value',
            'Place Of Supply',
            'Reverse Charge',
            'Applicable % of Tax Rate',
            'Invoice Type',
            'E-Commerce GSTIN',
            'Rate',
            'Taxable Value',
            'Cess Amount',
        ];
        $col = 'A';
        foreach ($b2bHeaders as $h) {
            $sheetB2b->setCellValue($col . '1', $h);
            $sheetB2b->getStyle($col . '1')->getFont()->setBold(true);
            $sheetB2b->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        $rIdx = 2;
        foreach ($gstr1Data['b2b'] ?? [] as $r) {
            $sheetB2b->setCellValue('A' . $rIdx, $r['ctin']);
            $sheetB2b->setCellValue('B' . $rIdx, $r['cname']);
            $sheetB2b->setCellValue('C' . $rIdx, $r['inum']);
            $sheetB2b->setCellValue('D' . $rIdx, $r['idt']);
            $sheetB2b->setCellValue('E' . $rIdx, (float) $r['val']);
            $sheetB2b->setCellValue('F' . $rIdx, $r['pos']);
            $sheetB2b->setCellValue('G' . $rIdx, $r['rchrg']);
            $sheetB2b->setCellValue('H' . $rIdx, '');
            $sheetB2b->setCellValue('I' . $rIdx, $r['inv_typ']);
            $sheetB2b->setCellValue('J' . $rIdx, $gstr1Data['eco']['etin'] ?? '');
            $sheetB2b->setCellValue('K' . $rIdx, (float) $r['rate']);
            $sheetB2b->setCellValue('L' . $rIdx, (float) $r['txval']);
            $sheetB2b->setCellValue('M' . $rIdx, 0.0);
            $rIdx++;
        }
        foreach (range('A', 'M') as $c) {
            $sheetB2b->getColumnDimension($c)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // Sheet 3: b2cs
        // ----------------------------------------------------
        $sheetB2cs = $spreadsheet->createSheet();
        $sheetB2cs->setTitle('b2cs');
        $b2csHeaders = [
            'Type',
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN',
        ];
        $col = 'A';
        foreach ($b2csHeaders as $h) {
            $sheetB2cs->setCellValue($col . '1', $h);
            $sheetB2cs->getStyle($col . '1')->getFont()->setBold(true);
            $sheetB2cs->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        $rIdx = 2;
        foreach ($gstr1Data['b2cs'] ?? [] as $r) {
            $sheetB2cs->setCellValue('A' . $rIdx, $r['typ']);
            $sheetB2cs->setCellValue('B' . $rIdx, $r['pos']);
            $sheetB2cs->setCellValue('C' . $rIdx, '');
            $sheetB2cs->setCellValue('D' . $rIdx, (float) $r['rate']);
            $sheetB2cs->setCellValue('E' . $rIdx, (float) $r['txval']);
            $sheetB2cs->setCellValue('F' . $rIdx, 0.0);
            $sheetB2cs->setCellValue('G' . $rIdx, $gstr1Data['eco']['etin'] ?? '');
            $rIdx++;
        }
        foreach (range('A', 'G') as $c) {
            $sheetB2cs->getColumnDimension($c)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // Sheet 4: hsn
        // ----------------------------------------------------
        $sheetHsn = $spreadsheet->createSheet();
        $sheetHsn->setTitle('hsn');
        $hsnHeaders = [
            'HSN',
            'Description',
            'UQC',
            'Total Quantity',
            'Total Value',
            'Taxable Value',
            'Integrated Tax Amount',
            'Central Tax Amount',
            'State/UT Tax Amount',
            'Cess Amount',
        ];
        $col = 'A';
        foreach ($hsnHeaders as $h) {
            $sheetHsn->setCellValue($col . '1', $h);
            $sheetHsn->getStyle($col . '1')->getFont()->setBold(true);
            $sheetHsn->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        $rIdx = 2;
        foreach ($gstr1Data['hsn'] ?? [] as $r) {
            $sheetHsn->setCellValue('A' . $rIdx, $r['hsn_sc']);
            $sheetHsn->setCellValue('B' . $rIdx, $r['desc']);
            $sheetHsn->setCellValue('C' . $rIdx, $r['uqc']);
            $sheetHsn->setCellValue('D' . $rIdx, (int) $r['qty']);
            $sheetHsn->setCellValue('E' . $rIdx, (float) $r['val']);
            $sheetHsn->setCellValue('F' . $rIdx, (float) $r['txval']);
            $sheetHsn->setCellValue('G' . $rIdx, (float) $r['iamt']);
            $sheetHsn->setCellValue('H' . $rIdx, (float) $r['camt']);
            $sheetHsn->setCellValue('I' . $rIdx, (float) $r['samt']);
            $sheetHsn->setCellValue('J' . $rIdx, 0.0);
            $rIdx++;
        }
        foreach (range('A', 'J') as $c) {
            $sheetHsn->getColumnDimension($c)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // Sheet 5: docs
        // ----------------------------------------------------
        $sheetDocs = $spreadsheet->createSheet();
        $sheetDocs->setTitle('docs');
        $docsHeaders = [
            'Nature of Document',
            'Sr. No. From',
            'Sr. No. To',
            'Total Number',
            'Cancelled',
            'Net Issued',
        ];
        $col = 'A';
        foreach ($docsHeaders as $h) {
            $sheetDocs->setCellValue($col . '1', $h);
            $sheetDocs->getStyle($col . '1')->getFont()->setBold(true);
            $sheetDocs->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        $rIdx = 2;
        foreach ($gstr1Data['docs'] ?? [] as $r) {
            $sheetDocs->setCellValue('A' . $rIdx, $r['doc_typ']);
            $sheetDocs->setCellValue('B' . $rIdx, $r['from']);
            $sheetDocs->setCellValue('C' . $rIdx, $r['to']);
            $sheetDocs->setCellValue('D' . $rIdx, (int) $r['totnum']);
            $sheetDocs->setCellValue('E' . $rIdx, (int) $r['canc']);
            $sheetDocs->setCellValue('F' . $rIdx, (int) $r['net_issue']);
            $rIdx++;
        }
        foreach (range('A', 'F') as $c) {
            $sheetDocs->getColumnDimension($c)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // Sheet 6: Marketplace Fees & ITC (Reconciliation)
        // ----------------------------------------------------
        $sheetItc = $spreadsheet->createSheet();
        $sheetItc->setTitle('Marketplace Fees & ITC');
        $itcHeaders = [
            'Invoice #',
            'Invoice Date',
            'Taxable Value (₹)',
            'CGST (₹)',
            'SGST (₹)',
            'IGST (₹)',
            'Total Tax (₹)',
            'Invoice Grand Total (₹)',
        ];
        $col = 'A';
        foreach ($itcHeaders as $h) {
            $sheetItc->setCellValue($col . '1', $h);
            $sheetItc->getStyle($col . '1')->getFont()->setBold(true);
            $sheetItc->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD4EDDA');
            $col++;
        }
        $rIdx = 2;
        foreach ($gstr3bData['itc_invoices'] ?? [] as $r) {
            $sheetItc->setCellValue('A' . $rIdx, $r['number']);
            $sheetItc->setCellValue('B' . $rIdx, $r['date']);
            $sheetItc->setCellValue('C' . $rIdx, (float) $r['subtotal']);
            $sheetItc->setCellValue('D' . $rIdx, (float) $r['cgst']);
            $sheetItc->setCellValue('E' . $rIdx, (float) $r['sgst']);
            $sheetItc->setCellValue('F' . $rIdx, (float) $r['igst']);
            $sheetItc->setCellValue('G' . $rIdx, (float) $r['tax_amount']);
            $sheetItc->setCellValue('H' . $rIdx, (float) $r['grand_total']);
            $rIdx++;
        }
        foreach (range('A', 'H') as $c) {
            $sheetItc->getColumnDimension($c)->setAutoSize(true);
        }

        // Return spreadsheet file path
        $tempDir = storage_path('app/temp-gst-export');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir . '/GST_Report_' . ($gstr1Data['tax_period'] ?? date('Y-m')) . '_' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}

