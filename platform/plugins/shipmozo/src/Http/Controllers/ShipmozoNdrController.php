<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SparroWave\Shipmozo\Shipmozo;
use Throwable;

class ShipmozoNdrController extends BaseController
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function index()
    {
        PageTitle::setTitle('ShipMozo NDR Management');

        try {
            $ndrs = $this->shipmozo->getNdrAll();
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Unable to load NDR records', ['message' => $exception->getMessage()]);
            $ndrs = [];
        }

        return view('plugins/shipmozo::ndr.index', compact('ndrs'));
    }

    public function action(Request $request, string $awbNumber, BaseHttpResponse $response)
    {
        $validated = $request->validate(['action' => ['required', 'in:reattempt,rto']]);

        try {
            $result = $this->shipmozo->ndrAction($awbNumber, $validated['action']);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('NDR action failed', [
                'awb' => $awbNumber,
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage('Unable to perform the NDR action at this time.');
        }

        if (Arr::get($result, 'result') != 1 || Arr::get($result, 'error')) {
            return $response->setError()->setMessage(
                Arr::get($result, 'message', Arr::get($result, 'data.error', 'Failed to perform NDR action.'))
            );
        }

        return $response->setMessage('NDR action triggered successfully.');
    }
}
