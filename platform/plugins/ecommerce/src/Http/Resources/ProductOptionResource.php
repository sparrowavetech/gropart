<?php

namespace Botble\Ecommerce\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'required' => $this->required,
            'option_type' => (new $this->option_type())->view(),
            'values' => ProductOptionValueResource::collection($this->values),
        ];
    }
}
