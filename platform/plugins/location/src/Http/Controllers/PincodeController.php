<?php

namespace Botble\Location\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Location\Forms\CityForm;
use Botble\Location\Forms\PincodeForm;
use Botble\Location\Http\Requests\PincodeRequest;
use Botble\Location\Http\Resources\CityResource;
use Botble\Location\Http\Resources\PincodeResource;
use Botble\Location\Models\City;
use Botble\Location\Models\Pincode;
use Botble\Location\Repositories\Interfaces\PincodeInterface;
use Botble\Location\Tables\PincodeTable;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PincodeController extends BaseController
{
    /**
     * @var PincodeInterface
     */
    protected $pincodeRepository;

    /**
     * CityController constructor.
     * @param PincodeInterface $pincodeRepository
     */
    public function __construct(PincodeInterface $pincodeRepository)
    {
        $this->pincodeRepository = $pincodeRepository;
    }

    /**
     * @param PincodeTable $dataTable
     * @return Factory|View
     * @throws Throwable
     */
    public function index(PincodeTable $table)
    {

        page_title()->setTitle(trans('Pincodes'));
        
        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle("Create New Pincode");

        return $formBuilder->create(PincodeForm::class)->renderForm();
    }

    /**
     * @param pincodeRequest $request
     * @return BaseHttpResponse
     */
    public function store(pincodeRequest $request, BaseHttpResponse $response)
    {
        $city = $this->pincodeRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

        return $response
            ->setPreviousUrl(route('pincode.index'))
            ->setNextUrl(route('pincode.edit', $city->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $city = $this->pincodeRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $city));

        page_title()->setTitle(trans('plugins/location::city.edit') . ' "' . $city->name . '"');

        return $formBuilder->create(CityForm::class, ['model' => $city])->renderForm();
    }

    /**
     * @param $id
     * @param pincodeRequest $request
     * @return BaseHttpResponse
     */
    public function update($id, pincodeRequest $request, BaseHttpResponse $response)
    {
        $city = $this->pincodeRepository->findOrFail($id);

        $city->fill($request->input());

        $this->pincodeRepository->createOrUpdate($city);

        event(new UpdatedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

        return $response
            ->setPreviousUrl(route('city.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $city = $this->pincodeRepository->findOrFail($id);

            $this->pincodeRepository->delete($city);

            event(new DeletedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $city = $this->pincodeRepository->findOrFail($id);
            $this->pincodeRepository->delete($city);
            event(new DeletedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     */
    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = $request->input('q');

        if (!$keyword) {
            return $response->setData([]);
        }

        $data = $this->pincodeRepository->advancedGet([
            'condition' => [
                ['cities.name', 'LIKE', '%' . $keyword . '%'],
            ],
            'select'    => ['cities.id', 'cities.name'],
            'take'      => 10,
        ]);

        $data->prepend(new City(['id' => 0, 'name' => trans('plugins/location::city.select_city')]));

        return $response->setData(CityResource::collection($data));
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function ajaxGetCities(Request $request, BaseHttpResponse $response)
    {
        $params = [
            'select' => ['cities.id', 'cities.name'],
            'condition' => [
                'cities.status' => BaseStatusEnum::PUBLISHED,
            ],
        ];

        if ($request->input('state_id') && $request->input('state_id') != 'null') {
            $params['condition']['cities.state_id'] = $request->input('state_id');
        }

        $data = $this->pincodeRepository->advancedGet($params);

        $data->prepend(new City(['id' => 0, 'name' => trans('plugins/location::city.select_city')]));

        return $response->setData(CityResource::collection($data));
    }

    public function ajaxCheckPincode(Request $request, BaseHttpResponse $response)
    {
        $params = [
            'select' => ['pincodes.id', 'pincodes.pincode'],
            'condition' => [
                'pincodes.status' => BaseStatusEnum::PUBLISHED,
            ],
        ];
        if ($request->input('pincode') && $request->input('pincode') != 'null') {
            $params['condition']['pincodes.pincode'] = $request->input('pincode');
        }
        $data = $this->pincodeRepository->advancedGet($params);
        return $data;
    }

}
