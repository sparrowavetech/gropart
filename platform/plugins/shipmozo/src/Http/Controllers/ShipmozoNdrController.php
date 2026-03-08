<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use SparroWave\Shipmozo\Shipmozo;
use Illuminate\Http\Request;

class ShipmozoNdrController extends BaseController
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function index()
    {
        PageTitle::setTitle('ShipMozo NDR Management');

        $ndrs = $this->shipmozo->getNdrAll();

        return view('plugins/shipmozo::ndr.index', compact('ndrs'));
    }

    public function action(Request $request, string $awbNumber, BaseHttpResponse $response)
    {
        $action = $request->input('action'); // 'reattempt' or 'rto'

        $result = $this->shipmozo->ndrAction($awbNumber, $action);

        if (array_key_exists('error', $result) && $result['error']) {
            return $response
                ->setError()
                ->setMessage($result['message'] ?? 'Failed to perform NDR action.');
        }

        return $response
            ->setMessage('NDR action triggered successfully.');
    }
}
