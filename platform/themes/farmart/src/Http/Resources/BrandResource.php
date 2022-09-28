<?php

namespace Theme\Farmart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use RvMedia;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'url'         => $this->url,
            'description' => Str::limit($this->description, 150),
            'logo'        => RvMedia::getImageUrl($this->logo, null, false, RvMedia::getDefaultImage()),
        ];
    }
}
