<?php

namespace Botble\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PincodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'pincode' => $this->pincode,
        ];
    }
}
