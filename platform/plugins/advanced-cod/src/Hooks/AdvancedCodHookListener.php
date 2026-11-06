<?php

namespace SparroWave\AdvancedCod\Hooks;

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;

class AdvancedCodHookListener
{
    public static function addProductMetaBox($priority, $model): void
    {
        if (! $model instanceof Product) {
            return;
        }

        if (! is_plugin_active('payment') || get_payment_setting('status', 'cod') == 0) {
            return;
        }

        MetaBox::addMetaBox(
            'advanced_cod_meta_box',
            'COD Eligibility',
            function () use ($model) {
                return view('plugins/advanced-cod::product-cod-config', compact('model'))->render();
            },
            get_class($model),
            'advanced',
            'high'
        );
    }

    public static function saveProductCodEligibility(string $screen, Request $request, $model): void
    {
        if (! $model instanceof Product) {
            return;
        }

        $model->is_cod_eligible = $request->input('is_cod_eligible', 0);
        $model->save();
    }

    public static function addCodLabelToProductPage(?string $content, Product $product): ?string
    {
        if (! get_payment_setting('show_detail_badge', 'cod', 1) || ! $product->is_cod_eligible) {
            return $content;
        }

        $truckSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>';
        $label = '<span class="product-cod-label" style="display: inline-flex; vertical-align: middle; margin: 6px 6px 6px 0;"><span class="badge bg-success" style="background-color: #28a745 !important; color: white; padding: 5px 9px; font-size: 11px; border-radius: 4px; font-weight: 500; line-height: 1.2; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">' . $truckSvg . trans('plugins/advanced-cod::advanced-cod.cod_available') . '</span></span>';
        
        return $label . $content;
    }

    public static function addCodLabelToProductCard(?string $content, mixed $product = null, bool $forAdminOrder = false): ?string
    {
        if ((! $forAdminOrder && ! get_payment_setting('show_card_badge', 'cod', 0)) || ! $product || ! ($product->is_cod_eligible ?? false)) {
            return $content;
        }

        if ($forAdminOrder) {
            $truckSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 13px !important; height: 13px !important; max-width: 13px !important; max-height: 13px !important; display: inline-block; vertical-align: middle; flex-shrink: 0;"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>';
            $label = '<span class="product-cod-label" style="display: inline-flex; vertical-align: middle; margin: 4px 4px 4px 0;"><span class="badge bg-success" style="background-color: #28a745 !important; color: white !important; padding: 4px 8px; font-size: 11px; border-radius: 4px; font-weight: 500; line-height: 1.2; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">'
                . $truckSvg . '<span>' . trans('plugins/advanced-cod::advanced-cod.cod_available') . '</span>'
                . '</span></span>';
        } else {
            $truckSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>';
            $label = '<span class="badge bg-success product-cod-card-badge" style="background-color: #28a745 !important; color: white !important; padding: 2px 5px; font-size: 10px; border-radius: 4px; font-weight: 500; display: inline-flex; align-items: center; vertical-align: middle; margin: 2px 4px 2px 0;">'
                . $truckSvg . trans('plugins/advanced-cod::advanced-cod.cod_available')
                . '</span>';
        }

        if (! $forAdminOrder && is_object($product) && isset($product->id)) {
            request()->attributes->set('advanced_cod_native_card_badge', (string) $product->id);
        }

        return ($content ?? '') . $label;
    }

    public static function addCodLabelToProductView(?string $content, mixed $product = null): ?string
    {
        if (! $product) {
            return $content;
        }

        $prod = $product->original_product ?: $product;
        if (! ($prod->is_cod_eligible ?? false)) {
            return $content;
        }

        return self::addCodLabelToProductCard($content, $prod, true);
    }

    public static function addCodSettings(?string $settings, string $paymentMethod): ?string
    {
        if ($paymentMethod !== \Botble\Payment\Enums\PaymentMethodEnum::COD) {
            return $settings;
        }

        $percentage = \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage();
        $selectedGateway = (string) get_payment_setting('advance_gateway', $paymentMethod, 'auto');

        $allGateways = array_values(\Botble\Payment\Enums\PaymentMethodEnum::toArray());
        $onlineGateways = [];
        foreach ($allGateways as $gateway) {
            if (!in_array($gateway, ['cod', 'bank_transfer', 'auto'], true) && get_payment_setting('status', $gateway) == 1) {
                $onlineGateways[$gateway] = (string) get_payment_setting('name', $gateway, ucfirst($gateway));
            }
        }

        return $settings . view('plugins/advanced-cod::settings', compact('percentage', 'selectedGateway', 'onlineGateways'))->render();
    }

    public static function addTableFilters(array $filters, mixed $table): array
    {
        if (! is_plugin_active('payment') || get_payment_setting('status', 'cod') == 0) {
            return $filters;
        }

        if (
            $table instanceof \Botble\Ecommerce\Tables\ProductTable ||
            (is_plugin_active('marketplace') && class_exists(\Botble\Marketplace\Tables\ProductTable::class) && $table instanceof \Botble\Marketplace\Tables\ProductTable)
        ) {
            $filters['is_cod_eligible'] = [
                'title' => trans('plugins/advanced-cod::advanced-cod.cod_products'),
                'type' => 'customSelect',
                'choices' => [
                    1 => trans('core/base::base.yes'),
                    0 => trans('core/base::base.no'),
                ],
                'validate' => 'required|in:1,0',
            ];
        }

        return $filters;
    }
}
