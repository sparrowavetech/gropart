<?php
namespace SparroWave\AdvancedCod\Supports;
use Botble\Ecommerce\Models\Order;
class OrderEligibility
{
    public static function read(Order $order): array
    {
        $rows=[];
        foreach($order->products()->with('product.variationInfo.configurableProduct')->get() as $item){
            $product=$item->product;
            if($product?->is_variation && $product->variationInfo?->configurableProduct?->id) $product=$product->variationInfo->configurableProduct;
            $rows[]=['name'=>$item->product_name,'cod'=>(bool)($product?->is_cod_eligible ?? false),
                'free'=>is_plugin_active('product-free-shipping') && (bool)setting('product_free_shipping_enabled',true) && (bool)($product?->product_free_shipping ?? false)];
        }
        return ['items'=>$rows,'cod'=>count($rows)>0 && !in_array(false,array_column($rows,'cod'),true),
            'free_applied'=>round((float)$order->shipping_amount+(float)$order->shipping_tax_amount,2)===0.0];
    }
}
