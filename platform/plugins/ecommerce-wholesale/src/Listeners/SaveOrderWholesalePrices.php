<?php

namespace Botble\EcommerceWholesale\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Services\PricingRuleService;
use Botble\EcommerceWholesale\Services\WholesalePriceService;
use Illuminate\Support\Arr;

class SaveOrderWholesalePrices
{
    public function __construct(
        protected WholesalePriceService $wholesalePriceService
    ) {
    }

    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;

        if (! WholesaleHelper::isEnabled()) {
            return;
        }

        $customer = $order->user;
        $isGuestEnabled = WholesaleHelper::isEnabledForGuests();

        if (! $customer && ! $isGuestEnabled) {
            return;
        }

        if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! $isGuestEnabled) {
            return;
        }

        $orderProducts = $order->products()->get();

        $adjustedSubTotal = 0;
        $adjustedTax = 0;

        foreach ($orderProducts as $orderProduct) {
            $product = Product::query()->find($orderProduct->product_id);

            if (! $product) {
                $adjustedSubTotal += $orderProduct->qty * $orderProduct->price;
                $adjustedTax += $orderProduct->tax_amount;

                continue;
            }

            $wholesalePrice = $this->wholesalePriceService->getWholesalePrice(
                $product,
                $orderProduct->qty,
                $customer
            );

            if ($wholesalePrice !== null) {
                $wholesalePriceWithOptions = $wholesalePrice;
                $orderOptions = is_array($orderProduct->options) ? $orderProduct->options : [];

                if (
                    EcommerceHelper::isEnabledProductOptions() &&
                    ($productOptions = Arr::get($orderOptions, 'options', [])) &&
                    is_array($productOptions)
                ) {
                    // getPriceByOptions() returns ['price' => float, 'option_price_once' => float];
                    // roundPrice() and the tax ratio below need a float, so extract the 'price' key.
                    $priceResult = Cart::instance('cart')->getPriceByOptions($wholesalePrice, $productOptions);
                    $wholesalePriceWithOptions = (float) Arr::get($priceResult, 'price', $wholesalePrice);
                }

                $roundedPrice = EcommerceHelper::roundPrice($wholesalePriceWithOptions);
                $originalPrice = $orderProduct->price;

                if ($roundedPrice != $originalPrice) {
                    $orderProduct->price = $roundedPrice;

                    if ($orderProduct->tax_amount > 0 && $originalPrice > 0) {
                        $orderProduct->tax_amount = EcommerceHelper::roundPrice(
                            $orderProduct->tax_amount * ($wholesalePriceWithOptions / $originalPrice)
                        );
                    }
                }

                $this->saveWholesaleInfoToOptions($orderProduct, $product, $customer, $originalPrice);
                $orderProduct->save();

                $adjustedSubTotal += $orderProduct->qty * $orderProduct->price;
                $adjustedTax += $orderProduct->tax_amount;
            } else {
                $adjustedSubTotal += $orderProduct->qty * $orderProduct->price;
                $adjustedTax += $orderProduct->tax_amount;
            }
        }

        $adjustedSubTotal = EcommerceHelper::roundPrice($adjustedSubTotal);
        $adjustedTax = EcommerceHelper::roundPrice($adjustedTax);

        $expectedAmount = EcommerceHelper::roundPrice(
            $adjustedSubTotal
            + $adjustedTax
            + ($order->shipping_amount ?? 0)
            - ($order->discount_amount ?? 0)
            + ($order->payment_fee ?? 0)
        );

        if (
            $adjustedSubTotal != $order->sub_total
            || $adjustedTax != $order->tax_amount
            || $expectedAmount != $order->amount
        ) {
            $order->sub_total = $adjustedSubTotal;
            $order->tax_amount = $adjustedTax;
            $order->amount = $expectedAmount;
            $order->save();

            $this->updateInvoice($order, $adjustedSubTotal, $adjustedTax);
        }
    }

    protected function saveWholesaleInfoToOptions($orderProduct, Product $product, $customer, float $originalPrice): void
    {
        $originalProduct = $product->is_variation ? $product->original_product : $product;
        // Convert the product's own currency price to the store default currency before applying wholesale discounts.
        $basePrice = $product->isOnSale() ? $product->front_sale_price : $product->getConvertedPrice();
        $groupIds = $this->wholesalePriceService->getApplicableGroupIds($customer);

        if (empty($groupIds)) {
            return;
        }

        $bestPrice = app(PricingRuleService::class)->getBestPriceForQuantity(
            $originalProduct,
            $orderProduct->qty,
            $groupIds,
            $product->store_id ?? null,
            $basePrice
        );

        if (! $bestPrice) {
            return;
        }

        $rule = $bestPrice['rule'];
        $isPercentage = $rule->discount_type->getValue() === 'percentage';
        $discountLabel = $isPercentage ? ($rule->discount_value . '%') : format_price($bestPrice['discount']);

        $options = is_array($orderProduct->options) ? $orderProduct->options : json_decode($orderProduct->options, true) ?? [];
        $options['extras'] = $options['extras'] ?? [];
        $options['extras']['wholesale_discount'] = $discountLabel;
        $orderProduct->options = $options;
    }

    protected function updateInvoice($order, float $subTotal, float $taxAmount): void
    {
        $invoice = Invoice::query()
            ->where('reference_id', $order->id)
            ->where('reference_type', get_class($order))
            ->first();

        if (! $invoice) {
            return;
        }

        $invoice->sub_total = EcommerceHelper::roundPrice($subTotal);
        $invoice->tax_amount = EcommerceHelper::roundPrice($taxAmount);
        $invoice->amount = EcommerceHelper::roundPrice(
            $subTotal
            + $taxAmount
            + ($invoice->shipping_amount ?? 0)
            - ($invoice->discount_amount ?? 0)
            + ($invoice->payment_fee ?? 0)
        );
        $invoice->save();
    }
}
