<?php

namespace Botble\EcommerceWholesale\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Models\ProductMOQ;
use Botble\EcommerceWholesale\Models\ProductVisibility;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\Setting\Models\Setting;
use Illuminate\Support\Facades\DB;

class WholesaleSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->command->info('Seeding Wholesale data...');

        $this->truncateTables();
        $this->seedCustomerGroups();
        $this->seedPricingRules();
        $this->seedWholesaleApplications();
        $this->seedProductMOQ();
        $this->seedProductVisibility();
        $this->seedWholesaleSettings();
        $this->assignDemoCustomerToWholesaleGroup();

        $this->command->info('Wholesale data seeded successfully!');
    }

    protected function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('ws_customer_group_assignments')->truncate();
        DB::table('ws_product_group_access')->truncate();
        GroupPricingRule::query()->truncate();
        WholesaleApplication::query()->truncate();
        ProductMOQ::query()->truncate();
        ProductVisibility::query()->truncate();
        CustomerGroup::query()->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function seedCustomerGroups(): void
    {
        $this->command->info('Creating customer groups...');

        $groups = [
            [
                'name' => 'Bronze',
                'description' => 'Entry-level wholesale tier with basic discounts.',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 5,
                'min_order_value' => 100,
                'min_order_quantity' => 5,
                'priority' => 4,
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Silver',
                'description' => 'Mid-tier wholesale customers with better pricing.',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 10,
                'min_order_value' => 500,
                'min_order_quantity' => 10,
                'priority' => 3,
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Gold',
                'description' => 'Premium wholesale tier for high-volume buyers.',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 15,
                'min_order_value' => 1000,
                'min_order_quantity' => 20,
                'priority' => 2,
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Platinum',
                'description' => 'VIP wholesale customers with best pricing.',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 20,
                'min_order_value' => 5000,
                'min_order_quantity' => 50,
                'priority' => 1,
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Retailers',
                'description' => 'Retail business partners with fixed discount.',
                'discount_type' => DiscountTypeEnum::FIXED,
                'discount_value' => 50,
                'min_order_value' => 200,
                'min_order_quantity' => 10,
                'priority' => 5,
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::query()->create($group);
        }

        $this->command->info('Created ' . count($groups) . ' customer groups.');
    }

    protected function seedPricingRules(): void
    {
        $this->command->info('Creating pricing rules...');

        $products = Product::query()
            ->where('is_variation', false)
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping pricing rules.');

            return;
        }

        $rulesCount = 0;

        foreach ($products as $product) {
            $tiers = [
                ['min' => 5, 'max' => 9, 'discount' => 5],
                ['min' => 10, 'max' => 24, 'discount' => 10],
                ['min' => 25, 'max' => 49, 'discount' => 15],
                ['min' => 50, 'max' => 99, 'discount' => 20],
                ['min' => 100, 'max' => null, 'discount' => 25],
            ];

            foreach ($tiers as $tier) {
                GroupPricingRule::query()->create([
                    'scope' => PricingRuleScopeEnum::PRODUCT,
                    'product_id' => $product->id,
                    'customer_group_id' => null,
                    'min_quantity' => $tier['min'],
                    'max_quantity' => $tier['max'],
                    'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
                    'discount_value' => $tier['discount'],
                    'status' => CustomerGroupStatusEnum::PUBLISHED,
                ]);
                $rulesCount++;
            }
        }

        $this->command->info('Created ' . $rulesCount . ' pricing rules for ' . $products->count() . ' products.');
    }

    protected function seedWholesaleApplications(): void
    {
        $this->command->info('Creating wholesale applications...');

        $customers = Customer::query()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $groups = CustomerGroup::query()->pluck('id')->all();
        $applicationsCount = 0;

        foreach ($customers as $index => $customer) {
            $status = match ($index % 3) {
                0 => ApplicationStatusEnum::PENDING,
                1 => ApplicationStatusEnum::APPROVED,
                default => ApplicationStatusEnum::REJECTED,
            };

            $application = WholesaleApplication::query()->create([
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'name' => $customer->name,
                'phone' => $customer->phone ?? $this->faker->phoneNumber(),
                'company_name' => $this->faker->company(),
                'tax_id' => $this->faker->optional(0.6)->regexify('[0-9]{10}'),
                'business_type' => $this->faker->randomElement(['retailer', 'distributor', 'manufacturer', 'reseller']),
                'expected_volume' => $this->faker->randomElement(['1000-5000', '5000-10000', '10000-50000', '50000+']),
                'notes' => $this->faker->optional(0.5)->sentence(),
                'status' => $status,
            ]);

            if ($status === ApplicationStatusEnum::APPROVED) {
                $application->update([
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                    'assigned_group_id' => $this->faker->randomElement($groups),
                ]);

                DB::table('ws_customer_group_assignments')->insert([
                    'customer_id' => $customer->id,
                    'customer_group_id' => $application->assigned_group_id,
                    'assigned_at' => now(),
                    'assigned_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($status === ApplicationStatusEnum::REJECTED) {
                $application->update([
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                    'rejection_reason' => $this->faker->sentence(),
                ]);
            }

            $applicationsCount++;
        }

        $this->command->info('Created ' . $applicationsCount . ' wholesale applications.');
    }

    protected function seedProductMOQ(): void
    {
        $this->command->info('Creating product MOQ settings...');

        $products = Product::query()
            ->where('is_variation', false)
            ->inRandomOrder()
            ->limit(20)
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping MOQ settings.');

            return;
        }

        $moqCount = 0;

        foreach ($products as $product) {
            $minQty = $this->faker->randomElement([5, 10, 12, 24, 50]);
            $increment = $this->faker->randomElement([1, 5, 6, 12]);

            ProductMOQ::query()->create([
                'product_id' => $product->id,
                'customer_group_id' => null,
                'min_quantity' => $minQty,
                'quantity_increment' => $increment,
            ]);

            $moqCount++;
        }

        $this->command->info('Created ' . $moqCount . ' product MOQ settings.');
    }

    protected function seedProductVisibility(): void
    {
        $this->command->info('Creating product visibility settings...');

        $products = Product::query()
            ->where('is_variation', false)
            ->inRandomOrder()
            ->limit(15)
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping visibility settings.');

            return;
        }

        $groups = CustomerGroup::query()->pluck('id')->all();
        $visibilityCount = 0;

        foreach ($products as $index => $product) {
            $type = match ($index % 3) {
                0 => ProductVisibilityEnum::WHOLESALE_ONLY,
                1 => ProductVisibilityEnum::SPECIFIC_GROUPS,
                default => ProductVisibilityEnum::PRODUCT_PUBLIC,
            };

            if ($type === ProductVisibilityEnum::PRODUCT_PUBLIC) {
                continue;
            }

            ProductVisibility::query()->create([
                'product_id' => $product->id,
                'visibility_type' => $type,
            ]);

            if ($type === ProductVisibilityEnum::SPECIFIC_GROUPS && ! empty($groups)) {
                $selectedGroups = $this->faker->randomElements($groups, $this->faker->numberBetween(1, min(3, count($groups))));

                foreach ($selectedGroups as $groupId) {
                    DB::table('ws_product_group_access')->insert([
                        'product_id' => $product->id,
                        'customer_group_id' => $groupId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $visibilityCount++;
        }

        $this->command->info('Created ' . $visibilityCount . ' product visibility settings.');
    }

    protected function seedWholesaleSettings(): void
    {
        $this->command->info('Seeding wholesale settings...');

        $bronzeGroup = CustomerGroup::query()->where('name', 'Bronze')->first();

        $settings = [
            'wholesale_enabled' => '1',
            'wholesale_require_approval' => '1',
            'wholesale_show_prices_to_guests' => '1',
            'wholesale_allow_multiple_groups' => '0',
            'wholesale_enable_for_guests' => '1',
            'wholesale_default_group' => $bronzeGroup?->id,
            'wholesale_show_pricing_table' => '1',
            'wholesale_enable_vendor_dashboard' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        $this->command->info('Wholesale settings seeded. Default group: ' . ($bronzeGroup?->name ?? 'none') . '.');
    }

    protected function assignDemoCustomerToWholesaleGroup(): void
    {
        $this->command->info('Assigning demo customer to wholesale group...');

        $customer = Customer::query()->where('email', 'customer@botble.com')->first();

        if (! $customer) {
            $this->command->warn('Customer customer@botble.com not found. Skipping assignment.');

            return;
        }

        $group = CustomerGroup::query()->where('name', 'Gold')->first();

        if (! $group) {
            $this->command->warn('Gold customer group not found. Skipping assignment.');

            return;
        }

        WholesaleApplication::query()
            ->where('customer_id', $customer->id)
            ->delete();

        WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => $customer->phone ?? $this->faker->phoneNumber(),
            'company_name' => $this->faker->company(),
            'tax_id' => $this->faker->regexify('[0-9]{10}'),
            'business_type' => 'retailer',
            'expected_volume' => '10000-50000',
            'status' => ApplicationStatusEnum::APPROVED,
            'assigned_group_id' => $group->id,
            'reviewed_by' => 1,
            'reviewed_at' => now(),
        ]);

        DB::table('ws_customer_group_assignments')
            ->where('customer_id', $customer->id)
            ->delete();

        DB::table('ws_customer_group_assignments')->insert([
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
            'assigned_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Assigned customer {$customer->email} to {$group->name} wholesale group with approved application.");
    }
}
