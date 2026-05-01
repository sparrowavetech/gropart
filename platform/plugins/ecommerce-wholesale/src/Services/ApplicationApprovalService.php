<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\ACL\Models\User;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApplicationApprovalService
{
    public function approve(
        WholesaleApplication $application,
        CustomerGroup $group,
        User $reviewer
    ): Customer {
        return DB::transaction(function () use ($application, $group, $reviewer) {
            $customer = $application->customer;

            if (! $customer) {
                $customer = Customer::query()->create([
                    'name' => $application->name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'password' => Hash::make(Str::random(16)),
                    'status' => 'activated',
                    'confirmed_at' => now(),
                ]);

                $application->customer_id = $customer->id;
            }

            $customer->wholesaleGroups()->syncWithoutDetaching([
                $group->id => [
                    'assigned_at' => now(),
                    'assigned_by' => $reviewer->id,
                ],
            ]);

            $application->update([
                'status' => ApplicationStatusEnum::APPROVED,
                'assigned_group_id' => $group->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return $customer;
        });
    }

    public function reject(
        WholesaleApplication $application,
        string $reason,
        User $reviewer
    ): WholesaleApplication {
        $application->update([
            'status' => ApplicationStatusEnum::REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $application;
    }
}
