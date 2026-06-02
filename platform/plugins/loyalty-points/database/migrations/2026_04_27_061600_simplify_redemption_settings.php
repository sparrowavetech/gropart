<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        $exchangeRow = DB::table('settings')->where('key', 'loyalty_points_points_exchange_rate')->first();

        if (! $exchangeRow) {
            return;
        }

        $exchangeRate = (float) $exchangeRow->value;

        if ($exchangeRate <= 0 || $exchangeRate == 1) {
            DB::table('settings')->where('key', 'loyalty_points_points_exchange_rate')->delete();

            return;
        }

        $currencyRow = DB::table('settings')
            ->where('key', 'loyalty_points_points_redemption_currency')
            ->first();

        $currentCurrency = $currencyRow ? (float) $currencyRow->value : 100.0;
        $newCurrency = $currentCurrency / $exchangeRate;

        DB::table('settings')->updateOrInsert(
            ['key' => 'loyalty_points_points_redemption_currency'],
            ['value' => (string) $newCurrency]
        );

        DB::table('settings')->where('key', 'loyalty_points_points_exchange_rate')->delete();
    }

    public function down(): void
    {
        // Non-reversible: original exchange_rate value is no longer stored.
    }
};
