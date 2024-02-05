<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\SellerInvoiceHelper;
use Botble\Marketplace\Models\Revenue;
use Exception;
use Illuminate\Http\Request;

class SellerInvoiceController extends BaseController
{

    public function getGenerateInvoice(int|string $revenueId, Request $request)
    {
        $revenueData = Revenue::query()->findOrFail($revenueId);

        if ($request->input('type') === 'print') {
            return SellerInvoiceHelper::streamInvoice($revenueData);
        }

        return SellerInvoiceHelper::downloadInvoice($revenueData);
    }
}
