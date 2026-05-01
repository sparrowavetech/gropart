<?php

namespace Botble\EcommerceWholesale\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Support\Collection;

interface CustomerGroupInterface extends RepositoryInterface
{
    public function getActiveGroups(): Collection;

    public function getGroupsForCustomer(int|string $customerId): Collection;
}
