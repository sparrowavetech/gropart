<?php

namespace Botble\LoyaltyPoints\Database\Seeders;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Language\Facades\Language;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoyaltyPointsSeeder extends BaseSeeder
{
    public function run(): void
    {
        DB::table('ec_customer_points_balances')->truncate();
        DB::table('ec_customer_points_transactions')->truncate();
        DB::table('loyalty_levels')->truncate();

        if (Schema::hasTable('loyalty_levels_translations')) {
            DB::table('loyalty_levels_translations')->truncate();
        }

        $this->seedLoyaltyLevels();

        $customers = Customer::query()->limit(10)->get();

        if ($customers->isEmpty()) {
            return;
        }

        $orders = Order::query()->whereIn('user_id', $customers->pluck('id'))->get();
        $levels = LoyaltyLevel::query()->orderByDesc('min_points')->get();

        foreach ($customers as $customer) {
            $totalPoints = rand(100, 5000);
            $lifetimePoints = $totalPoints + rand(0, 3000);

            // Determine customer level based on lifetime points
            $levelId = null;
            $levelUpdatedAt = null;
            foreach ($levels as $level) {
                if ($lifetimePoints >= $level->min_points) {
                    $levelId = $level->id;
                    $levelUpdatedAt = Carbon::now()->subDays(rand(1, 180));

                    break;
                }
            }

            CustomerPointBalance::query()->create([
                'customer_id' => $customer->id,
                'total_points' => $totalPoints,
                'lifetime_points' => $lifetimePoints,
                'level_id' => $levelId,
                'level_updated_at' => $levelUpdatedAt,
                'updated_at' => $this->now(),
            ]);

            $this->createTransactions($customer, $orders);
        }
    }

    private function seedLoyaltyLevels(): void
    {
        $levels = [
            [
                'name' => 'Bronze',
                'min_points' => 0,
                'max_points' => null,
                'earning_rate' => 1.0,
                'benefits' => "Earn 1 point per $100 spent\nBirthday Reward",
                'is_default' => true,
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'translations' => [
                    'vi' => [
                        'name' => 'Đồng',
                        'benefits' => "Kiếm 1 điểm cho mỗi $100 chi tiêu\nQuà sinh nhật",
                    ],
                ],
            ],
            [
                'name' => 'Silver',
                'min_points' => 500,
                'max_points' => null,
                'earning_rate' => 1.1,
                'benefits' => "1.1x Point Earning\nFree Shipping on orders over $50\nPriority Support",
                'is_default' => false,
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 1,
                'translations' => [
                    'vi' => [
                        'name' => 'Bạc',
                        'benefits' => "Kiếm điểm x1.1\nMiễn phí vận chuyển cho đơn hàng trên $50\nHỗ trợ ưu tiên",
                    ],
                ],
            ],
            [
                'name' => 'Gold',
                'min_points' => 2000,
                'max_points' => null,
                'earning_rate' => 1.25,
                'benefits' => "1.25x Point Earning\nFree Shipping on all orders\nExclusive Early Access\nDouble Points Days",
                'is_default' => false,
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 2,
                'translations' => [
                    'vi' => [
                        'name' => 'Vàng',
                        'benefits' => "Kiếm điểm x1.25\nMiễn phí vận chuyển cho tất cả đơn hàng\nQuyền truy cập sớm độc quyền\nNgày điểm đôi",
                    ],
                ],
            ],
            [
                'name' => 'Platinum',
                'min_points' => 5000,
                'max_points' => null,
                'earning_rate' => 1.5,
                'benefits' => "1.5x Point Earning\nConcierge Service\nFree Express Shipping\nAnnual Gift",
                'is_default' => false,
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 3,
                'translations' => [
                    'vi' => [
                        'name' => 'Bạch Kim',
                        'benefits' => "Kiếm điểm x1.5\nDịch vụ hỗ trợ riêng\nMiễn phí vận chuyển nhanh\nQuà tặng hàng năm",
                    ],
                ],
            ],
        ];

        foreach ($levels as $levelData) {
            $translations = $levelData['translations'] ?? [];
            unset($levelData['translations']);

            $level = LoyaltyLevel::query()->create($levelData);

            if (Schema::hasTable('loyalty_levels_translations') && ! empty($translations)) {
                $defaultLocale = Language::getDefaultLocaleCode();

                DB::table('loyalty_levels_translations')->insert([
                    'lang_code' => $defaultLocale,
                    'loyalty_levels_id' => $level->id,
                    'name' => $level->name,
                    'benefits' => $level->benefits,
                ]);

                foreach ($translations as $langCode => $translation) {
                    DB::table('loyalty_levels_translations')->insert([
                        'lang_code' => $langCode,
                        'loyalty_levels_id' => $level->id,
                        'name' => $translation['name'],
                        'benefits' => $translation['benefits'],
                    ]);
                }
            }
        }
    }

    private function createTransactions(Customer $customer, $orders): void
    {
        $customerOrders = $orders->where('user_id', $customer->id);
        $transactionCount = rand(5, 20);

        $types = [
            PointTransaction::TYPE_EARN => 60,
            PointTransaction::TYPE_REDEEM => 20,
            PointTransaction::TYPE_ADJUST => 10,
            PointTransaction::TYPE_REVERSE => 10,
        ];

        for ($i = 0; $i < $transactionCount; $i++) {
            $type = $this->getWeightedRandomType($types);
            $createdAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23));

            $transaction = [
                'customer_id' => $customer->id,
                'type' => $type,
                'created_at' => $createdAt,
            ];

            switch ($type) {
                case PointTransaction::TYPE_EARN:
                    $rand = rand(1, 100);

                    if ($rand <= 10) {
                        // Registration bonus
                        $transaction['points'] = rand(100, 500);
                        $transaction['order_id'] = null;
                        $transaction['note'] = trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_registration');
                    } elseif ($rand <= 30) {
                        // Review bonus
                        $transaction['points'] = rand(10, 50);
                        $transaction['order_id'] = null;
                        $transaction['note'] = trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_review', ['id' => rand(1, 1000)]);
                    } else {
                        // Order earning
                        $order = $customerOrders->isNotEmpty() ? $customerOrders->random() : null;
                        $transaction['points'] = rand(50, 500);
                        $transaction['order_id'] = $order ? $order->id : null;
                        $transaction['note'] = $order
                            ? trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_order', ['code' => $order->code])
                            : trans('plugins/loyalty-points::loyalty-points.transaction.points_earned');
                    }

                    $transaction['expires_at'] = $createdAt->copy()->addYear();

                    break;

                case PointTransaction::TYPE_REDEEM:
                    $order = $customerOrders->isNotEmpty() ? $customerOrders->random() : null;
                    $transaction['points'] = -rand(50, 300);
                    $transaction['order_id'] = $order ? $order->id : null;
                    $transaction['note'] = $order
                        ? trans('plugins/loyalty-points::loyalty-points.transaction.redeemed_for_order', ['code' => $order->code])
                        : trans('plugins/loyalty-points::loyalty-points.transaction.points_redeemed');

                    break;

                case PointTransaction::TYPE_ADJUST:
                    $adjustAmount = rand(-200, 500);
                    $transaction['points'] = $adjustAmount;
                    $transaction['order_id'] = null;
                    $transaction['note'] = $adjustAmount > 0
                        ? trans('plugins/loyalty-points::loyalty-points.transaction.admin_adjustment_add', ['points' => abs($adjustAmount)])
                        : trans('plugins/loyalty-points::loyalty-points.transaction.admin_adjustment_subtract', ['points' => abs($adjustAmount)]);

                    if ($adjustAmount > 0) {
                        $transaction['expires_at'] = $createdAt->copy()->addYear();
                    }

                    break;

                case PointTransaction::TYPE_REVERSE:
                    $order = $customerOrders->isNotEmpty() ? $customerOrders->random() : null;
                    $transaction['points'] = -rand(50, 500);
                    $transaction['order_id'] = $order ? $order->id : null;
                    $transaction['note'] = $order
                        ? trans('plugins/loyalty-points::loyalty-points.transaction.reversed_for_order', ['code' => $order->code])
                        : trans('plugins/loyalty-points::loyalty-points.transaction.points_reversed');

                    break;
            }

            PointTransaction::query()->create($transaction);
        }
    }

    private function getWeightedRandomType(array $types): string
    {
        $random = rand(1, 100);
        $sum = 0;

        foreach ($types as $type => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $type;
            }
        }

        return PointTransaction::TYPE_EARN;
    }
}
