<?php
namespace SparroWave\IndianGst\Listeners;

use Botble\Ecommerce\Listeners\StoreTaxComponentsListener;
use Botble\Ecommerce\Models\OrderProduct;
use Illuminate\Support\Facades\DB;

class StoreInclusiveTaxComponents extends StoreTaxComponentsListener
{
    public function handle(OrderProduct $item): void
    {
        parent::handle($item);
        static::reconcile($item);
    }

    public static function reconcile(OrderProduct $item): void
    {
        if (! ($item->options['price_includes_tax'] ?? false)) {
            return;
        }
        $components = $item->taxComponents()->orderBy('id')->get();
        $useRates = (float) $components->sum('amount') <= 0;
        $weight = (float) $components->sum($useRates ? 'rate' : 'amount');
        if ($components->isEmpty() || $weight <= 0) {
            return;
        }
        // The checkout tax snapshot is authoritative, including discounts and quantity.
        // Allocate in cents so CGST/SGST or other components sum exactly to that tax.
        DB::transaction(function () use ($item, $components, $weight, $useRates) {
            $remaining = round((float) $item->tax_amount, 2);
            $breakdown = [];
            foreach ($components as $index => $component) {
                $amount = $index === $components->count() - 1 ? $remaining
                    : round((float) $item->tax_amount * ($useRates ? $component->rate : $component->amount) / $weight, 2);
                $remaining = round($remaining - $amount, 2);
                $component->amount = $amount;
                $component->saveQuietly();
                $breakdown[] = $component->only(['name', 'code', 'rate', 'amount', 'jurisdiction', 'metadata']);
            }
            $item->updateQuietly(['tax_breakdown' => $breakdown]);
            $item->unsetRelation('taxComponents');
        });
    }
}
