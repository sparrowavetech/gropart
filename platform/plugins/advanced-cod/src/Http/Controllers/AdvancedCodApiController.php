<?php

namespace SparroWave\AdvancedCod\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;

class AdvancedCodApiController extends BaseController
{
    /**
     * Check COD Eligibility for a list of products.
     * GET /api/advanced-cod/eligibility?product_ids=1,2,3
     */
    public function checkEligibility(Request $request, BaseHttpResponse $response)
    {
        $productIds = $request->input('product_ids');
        if (! $productIds) {
            return $response
                ->setError()
                ->setMessage('Please provide product_ids (comma separated).');
        }

        $ids = explode(',', $productIds);
        $products = Product::whereIn('id', $ids)->get();

        $results = [];
        $isAllEligible = true;

        foreach ($products as $product) {
            $isEligible = (bool) $product->is_cod_eligible;
            if (! $isEligible) {
                $isAllEligible = false;
            }
            $results[] = [
                'id' => $product->id,
                'name' => $product->name,
                'is_cod_eligible' => $isEligible,
            ];
        }

        return $response
            ->setData([
                'is_cart_cod_eligible' => $isAllEligible,
                'products' => $results,
            ])
            ->setMessage('Eligibility check completed.');
    }

    /**
     * Calculate Prepayment Breakdown.
     * GET /api/advanced-cod/prepayment-calculate?amount=1000
     */
    public function getPrepaymentBreakdown(Request $request, BaseHttpResponse $response)
    {
        $amount = (float) $request->input('amount');
        if ($amount <= 0) {
             return $response
                ->setError()
                ->setMessage('Invalid amount.');
        }

        $percentage = (float) get_ecommerce_setting('cod_prepayment_percentage', 30);
        $prepaymentAmount = ($amount * $percentage) / 100;
        $remainingAmount = $amount - $prepaymentAmount;

        return $response
            ->setData([
                'total_amount' => $amount,
                'prepayment_percentage' => $percentage,
                'pay_now_amount' => round($prepaymentAmount, 2),
                'pay_on_delivery_amount' => round($remainingAmount, 2),
                'currency' => get_application_currency()->symbol,
            ])
            ->setMessage('Prepayment calculation completed.');
    }
}
