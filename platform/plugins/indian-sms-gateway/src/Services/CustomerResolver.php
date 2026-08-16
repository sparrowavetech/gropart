<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Database\Eloquent\Model;

class CustomerResolver
{
    public function __construct(private PhoneNormalizer $phones)
    {
    }

    public function findByPhone(string $phone): ?Model
    {
        if (! class_exists('Botble\\Ecommerce\\Models\\Customer')) {
            return null;
        }

        $modelClass = 'Botble\\Ecommerce\\Models\\Customer';
        $variants = $this->phones->variants($phone);

        return $modelClass::query()
            ->whereIn('phone', $variants)
            ->first();
    }

    public function phoneExists(string $phone): bool
    {
        return $this->findByPhone($phone) !== null;
    }
}
