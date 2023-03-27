<?php

namespace Botble\Ecommerce\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionValueResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        $formatPrice = $this->price ? (' + ' . $this->format_price) : '';

        return [
            'id' => $this->id,
            'name' => $this->option_value,
            'option_value' => $this->option_value,
            'order' => $this->order,
            'affect_type' => $this->affect_type,
            'affect_price' => $this->affect_price,
            'title' => $this->option_value . $formatPrice,
            'format_price' => $formatPrice,
        ];
    }
}
