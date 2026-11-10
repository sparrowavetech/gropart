<?php

namespace SparroWave\SellerInvoicing\Services\Gst;

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use SparroWave\SellerInvoicing\Supports\LocationResolver;

class Gstr1Engine
{
    public function generate(?int $storeId, string $taxPeriod): array
    {
        $periodCarbon = Carbon::createFromFormat('Y-m', $taxPeriod);
        $startDate = $periodCarbon->copy()->startOfMonth()->startOfDay();
        $endDate = $periodCarbon->copy()->endOfMonth()->endOfDay();

        $store = $storeId ? Store::query()->find($storeId) : null;
        $sellerGstin = $store?->tax_id ?: ($store?->gstin ?: '');
        $sellerStateCode = LocationResolver::resolveGstStateCode($store?->tax_state ?: ($store?->state ?: '08'));

        $query = Order::query()
            ->with(['products.product', 'address', 'store'])
            ->where(function ($q) {
                $q->where('is_finished', 1)->orWhere('status', 'completed');
            })
            ->whereNotIn('status', [OrderStatusEnum::CANCELED, 'canceled'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $orders = $query->orderBy('created_at', 'asc')->get();

        $b2b = [];
        $b2cl = [];
        $b2cs = [];
        $hsnSummary = [];
        $documentsSummary = [];
        $ecoSummary = [
            'etin' => (string) (get_ecommerce_setting('company_tax_id_for_invoicing') ?: setting('indian_gst_ecommerce_operator_gstin', '08AAACQ6514E1ZN')),
            'merchant_name' => $store?->name ?: 'Marketplace Sellers',
            'taxable_value' => 0.0,
            'igst' => 0.0,
            'cgst' => 0.0,
            'sgst' => 0.0,
            'cess' => 0.0,
        ];

        $totalTaxable = 0.0;
        $totalOutputTax = 0.0;
        $totalB2bTaxable = 0.0;
        $totalB2bTax = 0.0;
        $totalB2csTaxable = 0.0;
        $totalB2csTax = 0.0;

        $docNumbers = [];

        foreach ($orders as $order) {
            $posStateCode = LocationResolver::resolveGstStateCode($order->address?->state);
            $posLabel = LocationResolver::getGstPosLabel($order->address?->state);
            $isIntra = ($sellerStateCode === $posStateCode);

            $orderCode = $order->code ?: ('#' . $order->id);
            $docNumbers[] = $orderCode;
            $orderDate = $order->created_at ? $order->created_at->format('d-m-Y') : $startDate->format('d-m-Y');
            $orderTotal = (float) $order->amount;
            $orderTax = (float) $order->tax_amount;
            $orderSubtotal = (float) ($order->sub_total ?: max(0, $orderTotal - $orderTax));

            // Determine if buyer provided GSTIN (B2B)
            $buyerGstin = trim((string) ($order->tax_id ?: ($order->address?->tax_id ?: '')));
            $isB2b = ! empty($buyerGstin) && (bool) preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i', $buyerGstin);

            // Compute rate and split
            $rate = 18.0;
            if ($orderSubtotal > 0 && $orderTax > 0) {
                $calcRate = round(($orderTax / $orderSubtotal) * 100, 2);
                if (in_array(round($calcRate), [0, 5, 12, 18, 28])) {
                    $rate = (float) round($calcRate);
                }
            }

            $igst = $isIntra ? 0.0 : $orderTax;
            $cgst = $isIntra ? round($orderTax / 2, 2) : 0.0;
            $sgst = $isIntra ? round($orderTax - $cgst, 2) : 0.0;

            $totalTaxable += $orderSubtotal;
            $totalOutputTax += $orderTax;

            if ($isB2b) {
                $totalB2bTaxable += $orderSubtotal;
                $totalB2bTax += $orderTax;

                $b2b[] = [
                    'ctin' => strtoupper($buyerGstin),
                    'cname' => $order->address?->name ?: ($order->user?->name ?: 'B2B Customer'),
                    'inum' => $orderCode,
                    'idt' => $orderDate,
                    'val' => $orderTotal,
                    'pos' => $posLabel,
                    'rchrg' => 'N',
                    'inv_typ' => 'R',
                    'rate' => $rate,
                    'txval' => $orderSubtotal,
                    'iamt' => $igst,
                    'camt' => $cgst,
                    'samt' => $sgst,
                    'csamt' => 0.0,
                ];
            } else {
                if (! $isIntra && $orderTotal > 100000) {
                    $b2cl[] = [
                        'pos' => $posLabel,
                        'inum' => $orderCode,
                        'idt' => $orderDate,
                        'val' => $orderTotal,
                        'rate' => $rate,
                        'txval' => $orderSubtotal,
                        'iamt' => $igst,
                        'csamt' => 0.0,
                    ];
                } else {
                    $totalB2csTaxable += $orderSubtotal;
                    $totalB2csTax += $orderTax;

                    $key = $posLabel . '_' . $rate . '_' . ($isIntra ? 'INTRA' : 'INTER');
                    if (! isset($b2cs[$key])) {
                        $b2cs[$key] = [
                            'sply_ty' => $isIntra ? 'INTRA' : 'INTER',
                            'pos' => $posLabel,
                            'typ' => 'OE',
                            'rate' => $rate,
                            'txval' => 0.0,
                            'iamt' => 0.0,
                            'camt' => 0.0,
                            'samt' => 0.0,
                            'csamt' => 0.0,
                            'count' => 0,
                        ];
                    }
                    $b2cs[$key]['txval'] = round($b2cs[$key]['txval'] + $orderSubtotal, 2);
                    $b2cs[$key]['iamt'] = round($b2cs[$key]['iamt'] + $igst, 2);
                    $b2cs[$key]['camt'] = round($b2cs[$key]['camt'] + $cgst, 2);
                    $b2cs[$key]['samt'] = round($b2cs[$key]['samt'] + $sgst, 2);
                    $b2cs[$key]['count']++;
                }
            }

            // ECO Section (Section 52)
            $ecoSummary['taxable_value'] = round($ecoSummary['taxable_value'] + $orderSubtotal, 2);
            $ecoSummary['igst'] = round($ecoSummary['igst'] + $igst, 2);
            $ecoSummary['cgst'] = round($ecoSummary['cgst'] + $cgst, 2);
            $ecoSummary['sgst'] = round($ecoSummary['sgst'] + $sgst, 2);

            // Table 12: HSN Summary
            foreach ($order->products as $item) {
                $hsn = trim((string) ($item->product?->hsn_code ?: '8708'));
                $itemQty = (int) ($item->qty ?: 1);
                $itemPrice = (float) $item->price;
                $itemTax = (float) $item->tax_amount;
                $itemTaxable = round($itemPrice * $itemQty, 2);
                $itemTotal = round($itemTaxable + $itemTax, 2);

                $itemIgst = $isIntra ? 0.0 : $itemTax;
                $itemCgst = $isIntra ? round($itemTax / 2, 2) : 0.0;
                $itemSgst = $isIntra ? round($itemTax - $itemCgst, 2) : 0.0;

                if (! isset($hsnSummary[$hsn])) {
                    $hsnSummary[$hsn] = [
                        'hsn_sc' => $hsn,
                        'desc' => $item->product_name ?: ('Goods under HSN ' . $hsn),
                        'uqc' => 'NOS',
                        'qty' => 0,
                        'val' => 0.0,
                        'txval' => 0.0,
                        'iamt' => 0.0,
                        'camt' => 0.0,
                        'samt' => 0.0,
                        'csamt' => 0.0,
                        'rate' => $rate,
                    ];
                }

                $hsnSummary[$hsn]['qty'] += $itemQty;
                $hsnSummary[$hsn]['val'] = round($hsnSummary[$hsn]['val'] + $itemTotal, 2);
                $hsnSummary[$hsn]['txval'] = round($hsnSummary[$hsn]['txval'] + $itemTaxable, 2);
                $hsnSummary[$hsn]['iamt'] = round($hsnSummary[$hsn]['iamt'] + $itemIgst, 2);
                $hsnSummary[$hsn]['camt'] = round($hsnSummary[$hsn]['camt'] + $itemCgst, 2);
                $hsnSummary[$hsn]['samt'] = round($hsnSummary[$hsn]['samt'] + $itemSgst, 2);
            }
        }

        $docCount = count($docNumbers);
        $firstDoc = $docNumbers[0] ?? 'N/A';
        $lastDoc = $docNumbers[$docCount - 1] ?? 'N/A';

        $documentsSummary[] = [
            'doc_num' => 1,
            'doc_typ' => 'Invoices for outward supply',
            'from' => $firstDoc,
            'to' => $lastDoc,
            'totnum' => $docCount,
            'canc' => 0,
            'net_issue' => $docCount,
        ];

        return [
            'gstin' => $sellerGstin,
            'fp' => $periodCarbon->format('mY'),
            'tax_period' => $taxPeriod,
            'order_count' => $orders->count(),
            'total_taxable_sales' => round($totalTaxable, 2),
            'total_output_tax' => round($totalOutputTax, 2),
            'b2b_count' => count($b2b),
            'b2b_taxable' => round($totalB2bTaxable, 2),
            'b2b_tax' => round($totalB2bTax, 2),
            'b2cs_count' => count($b2cs),
            'b2cs_taxable' => round($totalB2csTaxable, 2),
            'b2cs_tax' => round($totalB2csTax, 2),
            'b2b' => array_values($b2b),
            'b2cl' => array_values($b2cl),
            'b2cs' => array_values($b2cs),
            'hsn' => array_values($hsnSummary),
            'docs' => $documentsSummary,
            'eco' => $ecoSummary,
        ];
    }
}

