<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('customers');

        Customer::query()->truncate();
        Address::query()->truncate();

        $names = [
            'John Smith',
            'Sarah Johnson',
            'Michael Brown',
            'Emily Davis',
            'James Wilson',
            'Jessica Martinez',
            'David Anderson',
            'Ashley Taylor',
            'Robert Thomas',
            'Jennifer Garcia',
        ];

        $states = ['California', 'Texas', 'Florida', 'New York', 'Illinois', 'Pennsylvania', 'Ohio', 'Georgia'];
        $cities = ['Los Angeles', 'Houston', 'Miami', 'New York', 'Chicago', 'Philadelphia', 'Columbus', 'Atlanta'];
        $streets = ['123 Main St', '456 Oak Ave', '789 Pine Rd', '321 Elm Blvd', '654 Maple Dr', '987 Cedar Ln', '147 Birch Way', '258 Walnut Ct'];
        $zipCodes = ['90001', '77001', '33101', '10001', '60601', '19101', '43201', '30301'];

        $customers = [
            'customer@botble.com',
            'vendor@botble.com',
        ];

        $now = Carbon::now();

        foreach ($customers as $index => $item) {
            $customer = Customer::query()->forceCreate([
                'name' => $names[$index],
                'email' => $item,
                'password' => Hash::make('12345678'),
                'phone' => '+1' . rand(2000000000, 9999999999),
                'avatar' => 'customers/' . rand(1, 10) . '.jpg',
                'dob' => Carbon::now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1' . rand(2000000000, 9999999999),
                'email' => $customer->email,
                'country' => 'US',
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1' . rand(2000000000, 9999999999),
                'email' => $customer->email,
                'country' => 'US',
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => false,
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $name = $names[$i + 2] ?? $names[$i];

            $customer = Customer::query()->forceCreate([
                'name' => $name,
                'email' => 'customer' . ($i + 1) . '@example.com',
                'password' => Hash::make('12345678'),
                'phone' => '+1' . rand(2000000000, 9999999999),
                'avatar' => 'customers/' . ($i + 1) . '.jpg',
                'dob' => Carbon::now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1' . rand(2000000000, 9999999999),
                'email' => $customer->email,
                'country' => 'US',
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);
        }
    }
}
