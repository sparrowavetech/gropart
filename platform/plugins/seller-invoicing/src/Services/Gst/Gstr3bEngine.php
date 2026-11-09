<?php

namespace SparroWave\SellerInvoicing\Services\Gst;

use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Supports\LocationResolver;

class Gstr3bEngine
{
    public function generate(array $gstr1Data, ?int $storeId, string $taxPeriod): array
    {
        $periodCarbon = Carbon::createFromFormat('Y-m', $taxPeriod);
        $startDate = $periodCarbon->copy()->startOfMonth()->startOfDay();
        $endDate = $periodCarbon->copy()->endOfMonth()->endOfDay();

        $store = $storeId ? Store::query()->find($storeId) : null;
        $sellerGstin = $store?->tax_id ?: ($store?->gstin ?: '');

        // --- 1. Compute Table 3.1: Outward Supplies ---
        $taxableValue31 = $gstr1Data['total_taxable_sales'] ?? 0.0;
        $igst31 = 0.0;
        $cgst31 = 0.0;
        $sgst31 = 0.0;

        // Sum taxes from B2B
        foreach ($gstr1Data['b2b'] ?? [] as $row) {
            $igst31 += (float) ($row['iamt'] ?? 0);
            $cgst31 += (float) ($row['camt'] ?? 0);
            $sgst31 += (float) ($row['samt'] ?? 0);
        }

        // Sum taxes from B2CL
        foreach ($gstr1Data['b2cl'] ?? [] as $row) {
            $igst31 += (float) ($row['iamt'] ?? 0);
        }

        // Sum taxes from B2CS
        foreach ($gstr1Data['b2cs'] ?? [] as $row) {
            $igst31 += (float) ($row['iamt'] ?? 0);
            $cgst31 += (float) ($row['camt'] ?? 0);
            $sgst31 += (float) ($row['samt'] ?? 0);
        }

        $table31 = [
            'outward_taxable' => [
                'description' => '(a) Outward taxable supplies (other than zero rated, nil rated and exempted)',
                'txval' => round($taxableValue31, 2),
                'iamt' => round($igst31, 2),
                'camt' => round($cgst31, 2),
                'samt' => round($sgst31, 2),
                'csamt' => 0.0,
            ],
            'outward_zero_rated' => [
                'description' => '(b) Outward taxable supplies (zero rated)',
                'txval' => 0.0,
                'iamt' => 0.0,
                'csamt' => 0.0,
            ],
            'other_outward_exempt' => [
                'description' => '(c) Other outward supplies (Nil rated, exempted)',
                'txval' => 0.0,
            ],
            'inward_reverse_charge' => [
                'description' => '(d) Inward supplies (liable to reverse charge)',
                'txval' => 0.0,
                'iamt' => 0.0,
                'camt' => 0.0,
                'samt' => 0.0,
                'csamt' => 0.0,
            ],
            'non_gst_outward' => [
                'description' => '(e) Non-GST outward supplies',
                'txval' => 0.0,
            ],
        ];

        // --- 2. Table 3.1.1: E-Commerce Operator Supplies (Sec 52) ---
        $table311 = [
            'eco_supplies' => [
                'description' => '(ii) Taxable supplies made by the registered person through electronic commerce operator',
                'txval' => round($taxableValue31, 2),
                'iamt' => round($igst31, 2),
                'camt' => round($cgst31, 2),
                'samt' => round($sgst31, 2),
                'csamt' => 0.0,
            ],
        ];

        // --- 3. Table 3.2: Inter-State supplies to unregistered persons ---
        $table32 = [];
        foreach ($gstr1Data['b2cs'] ?? [] as $row) {
            if (($row['sply_ty'] ?? '') === 'INTER') {
                $pos = $row['pos'] ?? '';
                if (! isset($table32[$pos])) {
                    $table32[$pos] = [
                        'pos' => $pos,
                        'txval' => 0.0,
                        'iamt' => 0.0,
                    ];
                }
                $table32[$pos]['txval'] = round($table32[$pos]['txval'] + (float) $row['txval'], 2);
                $table32[$pos]['iamt'] = round($table32[$pos]['iamt'] + (float) $row['iamt'], 2);
            }
        }
        foreach ($gstr1Data['b2cl'] ?? [] as $row) {
            $pos = $row['pos'] ?? '';
            if (! isset($table32[$pos])) {
                $table32[$pos] = [
                    'pos' => $pos,
                    'txval' => 0.0,
                    'iamt' => 0.0,
                ];
            }
            $table32[$pos]['txval'] = round($table32[$pos]['txval'] + (float) $row['txval'], 2);
            $table32[$pos]['iamt'] = round($table32[$pos]['iamt'] + (float) $row['iamt'], 2);
        }

        // --- 4. Table 4: Eligible Input Tax Credit (ITC) from Gropart Seller Invoices ---
        $docQuery = Document::query()
            ->with(['order.store'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('issued_at', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('issued_at')->whereBetween('created_at', [$startDate, $endDate]);
                    });
            });

        if ($storeId) {
            $storeObj = Store::query()->find($storeId);
            $docQuery->where(function ($q) use ($storeId, $storeObj) {
                $q->whereHas('order', function ($oq) use ($storeId) {
                    $oq->where('store_id', $storeId);
                });
                if ($storeObj && $storeObj->customer_id) {
                    $q->orWhere('seller_id', $storeObj->customer_id);
                }
            });
        }

        $sellerInvoices = $docQuery->get();

        $itcTaxable = 0.0;
        $itcIgst = 0.0;
        $itcCgst = 0.0;
        $itcSgst = 0.0;
        $itcInvoiceList = [];

        foreach ($sellerInvoices as $doc) {
            $sub = (float) $doc->subtotal;
            $tax = (float) $doc->tax_amount;
            $grand = (float) $doc->grand_total;

            $docIgst = 0.0;
            $docCgst = 0.0;
            $docSgst = 0.0;

            if (is_array($doc->taxes)) {
                foreach ($doc->taxes as $t) {
                    $tName = strtoupper($t['name'] ?? '');
                    $tAmt = (float) ($t['amount'] ?? 0);
                    if (str_contains($tName, 'IGST')) {
                        $docIgst += $tAmt;
                    } elseif (str_contains($tName, 'CGST')) {
                        $docCgst += $tAmt;
                    } elseif (str_contains($tName, 'SGST') || str_contains($tName, 'UTGST')) {
                        $docSgst += $tAmt;
                    }
                }
            }

            // Fallback if taxes breakdown was not itemized
            if ($docIgst == 0 && $docCgst == 0 && $docSgst == 0 && $tax > 0) {
                $docCgst = round($tax / 2, 2);
                $docSgst = round($tax - $docCgst, 2);
            }

            $itcTaxable += $sub;
            $itcIgst += $docIgst;
            $itcCgst += $docCgst;
            $itcSgst += $docSgst;

            $itcInvoiceList[] = [
                'id' => $doc->id,
                'number' => $doc->number,
                'date' => $doc->issued_at ? $doc->issued_at->format('d-m-Y') : $doc->created_at->format('d-m-Y'),
                'subtotal' => $sub,
                'tax_amount' => $tax,
                'grand_total' => $grand,
                'igst' => $docIgst,
                'cgst' => $docCgst,
                'sgst' => $docSgst,
            ];
        }

        $totalItcAvailable = round($itcIgst + $itcCgst + $itcSgst, 2);

        $table4 = [
            'itc_available' => [
                'import_goods' => ['txval' => 0.0, 'iamt' => 0.0, 'csamt' => 0.0],
                'import_services' => ['txval' => 0.0, 'iamt' => 0.0, 'csamt' => 0.0],
                'reverse_charge' => ['txval' => 0.0, 'iamt' => 0.0, 'camt' => 0.0, 'samt' => 0.0, 'csamt' => 0.0],
                'isd' => ['txval' => 0.0, 'iamt' => 0.0, 'camt' => 0.0, 'samt' => 0.0, 'csamt' => 0.0],
                'all_other_itc' => [
                    'description' => '(5) All other ITC (Marketplace Fees & Courier GST)',
                    'txval' => round($itcTaxable, 2),
                    'iamt' => round($itcIgst, 2),
                    'camt' => round($itcCgst, 2),
                    'samt' => round($itcSgst, 2),
                    'csamt' => 0.0,
                    'total' => $totalItcAvailable,
                ],
            ],
            'itc_reversed' => ['iamt' => 0.0, 'camt' => 0.0, 'samt' => 0.0, 'csamt' => 0.0],
            'net_itc' => [
                'iamt' => round($itcIgst, 2),
                'camt' => round($itcCgst, 2),
                'samt' => round($itcSgst, 2),
                'csamt' => 0.0,
                'total' => $totalItcAvailable,
            ],
        ];

        // --- 5. Net Tax Payable Calculation ---
        $totalOutputTax = round($igst31 + $cgst31 + $sgst31, 2);
        $netPayableIgst = max(0.0, round($igst31 - $itcIgst, 2));
        $netPayableCgst = max(0.0, round($cgst31 - $itcCgst, 2));
        $netPayableSgst = max(0.0, round($sgst31 - $itcSgst, 2));
        $netGstPayableTotal = round($netPayableIgst + $netPayableCgst + $netPayableSgst, 2);

        return [
            'gstin' => $sellerGstin,
            'tax_period' => $taxPeriod,
            'table_3_1' => $table31,
            'table_3_1_1' => $table311,
            'table_3_2' => array_values($table32),
            'table_4_itc' => $table4,
            'itc_invoices' => $itcInvoiceList,
            'itc_gropart_fees' => $totalItcAvailable,
            'total_output_tax' => $totalOutputTax,
            'net_gst_payable' => [
                'iamt' => $netPayableIgst,
                'camt' => $netPayableCgst,
                'samt' => $netPayableSgst,
                'total' => $netGstPayableTotal,
            ],
        ];
    }
}

