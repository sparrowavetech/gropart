<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Razorpay\Http\Controllers\RazorpayController;

class RazorpayAttemptRecorder extends RazorpayController
{
    public function record(array $data): void
    {
        if (empty($data['charge_id'])) {
            return;
        }

        $this->saveOrUpdatePayment(
            $data['charge_id'],
            $data['order_id'] ?? null,
            (float) ($data['amount'] ?? 0),
            $data['currency'],
            $data['status'] ?? null,
            $data['customer_id'] ?? null,
            $data['customer_type'] ?? null,
            'farmart-helper'
        );
    }
}
