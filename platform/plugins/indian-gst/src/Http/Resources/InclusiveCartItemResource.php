<?php
namespace SparroWave\IndianGst\Http\Resources;

use Botble\Ecommerce\Http\Resources\CartItemResource;

class InclusiveCartItemResource extends CartItemResource
{
    public function toArray($request): array
    {
        $data = parent::toArray($request);
        if ($this->options->get('price_includes_tax', false)) {
            $data['tax_price'] = $this->taxTotal;
            $data['original_price'] = $this->price + $this->options->get('option_price_once', 0) / max(1, $this->qty);
            $data['original_price_label'] = format_price($data['original_price']);
        }
        return $data;
    }
}
