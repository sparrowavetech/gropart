<?php

namespace Botble\EcommerceWholesale\Repositories\Eloquent;

use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Repositories\Interfaces\CustomerGroupInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Support\Collection;

class CustomerGroupRepository extends RepositoriesAbstract implements CustomerGroupInterface
{
    public function getActiveGroups(): Collection
    {
        return $this->model
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->get();
    }

    public function getGroupsForCustomer(int|string $customerId): Collection
    {
        return $this->model
            ->whereHas('customers', function ($query) use ($customerId): void {
                $query->where('ec_customers.id', $customerId);
            })
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->get();
    }
}
