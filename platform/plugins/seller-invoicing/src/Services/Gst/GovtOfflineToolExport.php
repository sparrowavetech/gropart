<?php

namespace SparroWave\SellerInvoicing\Services\Gst;

use ZipArchive;

class GovtOfflineToolExport
{
    public function generateJson(array $gstr1Data): string
    {
        $gstin = $gstr1Data['gstin'] ?: '08AAACQ6514E1ZN';
        $fp = $gstr1Data['fp'] ?? date('mY');

        // B2B Grouping for official schema: group invoices by customer GSTIN (ctin)
        $b2bGrouped = [];
        foreach ($gstr1Data['b2b'] ?? [] as $inv) {
            $ctin = $inv['ctin'];
            if (! isset($b2bGrouped[$ctin])) {
                $b2bGrouped[$ctin] = [
                    'ctin' => $ctin,
                    'cname' => $inv['cname'] ?? '',
                    'inv' => [],
                ];
            }

            // Extract numeric state code from POS label "08-Rajasthan"
            $posRaw = $inv['pos'] ?? '08';
            $posCode = explode('-', $posRaw)[0];

            $b2bGrouped[$ctin]['inv'][] = [
                'inum' => $inv['inum'],
                'idt' => $inv['idt'],
                'val' => (float) $inv['val'],
                'pos' => $posCode,
                'rchrg' => $inv['rchrg'] ?? 'N',
                'inv_typ' => $inv['inv_typ'] ?? 'R',
                'itms' => [
                    [
                        'num' => 1,
                        'itm_det' => [
                            'rt' => (float) $inv['rate'],
                            'txval' => (float) $inv['txval'],
                            'iamt' => (float) $inv['iamt'],
                            'camt' => (float) $inv['camt'],
                            'samt' => (float) $inv['samt'],
                            'csamt' => 0.0,
                        ],
                    ],
                ],
            ];
        }

        // B2CS Formatting
        $b2csList = [];
        foreach ($gstr1Data['b2cs'] ?? [] as $row) {
            $posRaw = $row['pos'] ?? '08';
            $posCode = explode('-', $posRaw)[0];

            $b2csList[] = [
                'sply_ty' => $row['sply_ty'] ?? 'INTRA',
                'pos' => $posCode,
                'typ' => $row['typ'] ?? 'OE',
                'rt' => (float) $row['rate'],
                'txval' => (float) $row['txval'],
                'iamt' => (float) $row['iamt'],
                'camt' => (float) $row['camt'],
                'samt' => (float) $row['samt'],
                'csamt' => 0.0,
            ];
        }

        // HSN Formatting
        $hsnList = [];
        $hsnIndex = 1;
        foreach ($gstr1Data['hsn'] ?? [] as $h) {
            $hsnList[] = [
                'num' => $hsnIndex++,
                'hsn_sc' => $h['hsn_sc'] ?? '8708',
                'desc' => $h['desc'] ?? '',
                'uqc' => $h['uqc'] ?? 'NOS',
                'qty' => (int) $h['qty'],
                'val' => (float) $h['val'],
                'txval' => (float) $h['txval'],
                'iamt' => (float) $h['iamt'],
                'camt' => (float) $h['camt'],
                'samt' => (float) $h['samt'],
                'csamt' => 0.0,
            ];
        }

        // Documents Issued Formatting
        $docIssue = [
            'doc_det' => [
                [
                    'doc_num' => 1,
                    'doc_typ' => 'Invoices for outward supply',
                    'docs' => [
                        [
                            'num' => 1,
                            'from' => $gstr1Data['docs'][0]['from'] ?? '1',
                            'to' => $gstr1Data['docs'][0]['to'] ?? '1',
                            'totnum' => (int) ($gstr1Data['docs'][0]['totnum'] ?? 0),
                            'canc' => 0,
                            'net_issue' => (int) ($gstr1Data['docs'][0]['net_issue'] ?? 0),
                        ],
                    ],
                ],
            ],
        ];

        $payload = [
            'gstin' => $gstin,
            'fp' => $fp,
            'version' => 'GSTR1_V2.0',
            'hash' => 'hash',
            'cur_gt' => (float) ($gstr1Data['total_taxable_sales'] ?? 0.0),
            'gt' => (float) ($gstr1Data['total_taxable_sales'] ?? 0.0),
            'b2b' => array_values($b2bGrouped),
            'b2cs' => $b2csList,
            'hsn' => [
                'data' => $hsnList,
            ],
            'doc_issue' => $docIssue,
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function generateCsvZip(array $gstr1Data): ?string
    {
        $tempDir = storage_path('app/temp-gst-export');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/GSTR1_Offline_CSVs_' . ($gstr1Data['fp'] ?? date('mY')) . '_' . uniqid() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        // 1. b2b.csv
        $b2bCsv = fopen('php://memory', 'r+');
        fputcsv($b2bCsv, [
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
        ]);
        foreach ($gstr1Data['b2b'] ?? [] as $r) {
            fputcsv($b2bCsv, [
                $r['ctin'],
                $r['cname'],
                $r['inum'],
                $r['idt'],
                $r['val'],
                $r['pos'],
                $r['rchrg'],
                '',
                $r['inv_typ'],
                $gstr1Data['eco']['etin'] ?? '',
                $r['rate'],
                $r['txval'],
                0.0,
            ]);
        }
        rewind($b2bCsv);
        $zip->addFromString('b2b.csv', stream_get_contents($b2bCsv));
        fclose($b2bCsv);

        // 2. b2cs.csv
        $b2csCsv = fopen('php://memory', 'r+');
        fputcsv($b2csCsv, [
            'Type',
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN',
        ]);
        foreach ($gstr1Data['b2cs'] ?? [] as $r) {
            fputcsv($b2csCsv, [
                $r['typ'],
                $r['pos'],
                '',
                $r['rate'],
                $r['txval'],
                0.0,
                $gstr1Data['eco']['etin'] ?? '',
            ]);
        }
        rewind($b2csCsv);
        $zip->addFromString('b2cs.csv', stream_get_contents($b2csCsv));
        fclose($b2csCsv);

        // 3. hsn.csv
        $hsnCsv = fopen('php://memory', 'r+');
        fputcsv($hsnCsv, [
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
        ]);
        foreach ($gstr1Data['hsn'] ?? [] as $r) {
            fputcsv($hsnCsv, [
                $r['hsn_sc'],
                $r['desc'],
                $r['uqc'],
                $r['qty'],
                $r['val'],
                $r['txval'],
                $r['iamt'],
                $r['camt'],
                $r['samt'],
                0.0,
            ]);
        }
        rewind($hsnCsv);
        $zip->addFromString('hsn.csv', stream_get_contents($hsnCsv));
        fclose($hsnCsv);

        // 4. docs.csv
        $docsCsv = fopen('php://memory', 'r+');
        fputcsv($docsCsv, [
            'Nature of Document',
            'Sr. No. From',
            'Sr. No. To',
            'Total Number',
            'Cancelled',
            'Net Issued',
        ]);
        foreach ($gstr1Data['docs'] ?? [] as $r) {
            fputcsv($docsCsv, [
                $r['doc_typ'],
                $r['from'],
                $r['to'],
                $r['totnum'],
                $r['canc'],
                $r['net_issue'],
            ]);
        }
        rewind($docsCsv);
        $zip->addFromString('docs.csv', stream_get_contents($docsCsv));
        fclose($docsCsv);

        $zip->close();

        return $zipPath;
    }
}

