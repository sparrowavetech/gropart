<?php

namespace Botble\LoyaltyPoints\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Services\LoyaltyCardPdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoyaltyCardController extends BaseController
{
    public function __construct(protected LoyaltyCardPdfService $pdfService)
    {
    }

    public function download(): Response|string|null
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        abort_unless($customer, 404);

        $balance = CustomerPointBalance::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['total_points' => 0, 'lifetime_points' => 0]
        );

        $balance->load('level');

        if (request()->has('preview')) {
            return $this->pdfService->stream($customer, $balance);
        }

        return $this->pdfService->download($customer, $balance);
    }
}
