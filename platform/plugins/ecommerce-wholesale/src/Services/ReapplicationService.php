<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Support\Facades\DB;

class ReapplicationService
{
    public function canReapply(Customer $customer): bool
    {
        $latest = $this->getLatestApplication($customer);

        if (! $latest) {
            return false;
        }

        return $latest->status->getValue() === ApplicationStatusEnum::REJECTED;
    }

    public function getLatestApplication(Customer $customer): ?WholesaleApplication
    {
        return WholesaleApplication::query()
            ->where('customer_id', $customer->getKey())
            ->latest()
            ->first();
    }

    public function getRejectedApplication(Customer $customer): ?WholesaleApplication
    {
        return WholesaleApplication::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', ApplicationStatusEnum::REJECTED)
            ->latest()
            ->first();
    }

    public function createReapplication(Customer $customer, array $data): WholesaleApplication
    {
        return DB::transaction(function () use ($customer, $data) {
            $existing = WholesaleApplication::query()
                ->where('customer_id', $customer->getKey())
                ->where('status', ApplicationStatusEnum::PENDING)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new \RuntimeException(trans('plugins/ecommerce-wholesale::wholesale.frontend.already_pending'));
            }

            return WholesaleApplication::query()->create([
                'customer_id' => $customer->getKey(),
                'email' => $customer->email,
                'name' => $customer->name,
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'],
                'tax_id' => $data['tax_id'] ?? null,
                'business_type' => $data['business_type'] ?? null,
                'expected_volume' => $data['expected_volume'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => ApplicationStatusEnum::PENDING,
            ]);
        });
    }
}
