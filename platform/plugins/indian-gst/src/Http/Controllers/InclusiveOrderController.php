<?php
namespace SparroWave\IndianGst\Http\Controllers;

use Botble\Ecommerce\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use SparroWave\IndianGst\Http\Resources\InclusiveCartItemResource;

class InclusiveOrderController extends OrderController
{
    protected function getDataBeforeCreateOrder(Request $request): array
    {
        $data = parent::getDataBeforeCreateOrder($request);
        $data['products'] = InclusiveCartItemResource::collection(
            $data['products']->collection->map(fn ($resource) => $resource->resource)
        );
        return $data;
    }
}
