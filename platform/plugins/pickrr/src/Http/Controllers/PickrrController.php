<?php

namespace Botble\Pickrr\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Pickrr\Http\Requests\PickrrRequest;
use Botble\Pickrr\Repositories\Interfaces\PickrrInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Pickrr\Tables\PickrrTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Pickrr\Forms\PickrrForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Setting\Supports\SettingStore;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Support\Services\Cache\Cache;

class PickrrController extends BaseController
{
    /**
     * @var PickrrInterface
     */
    protected $pickrrRepository;

    /**
     * @param PickrrInterface $pickrrRepository
     */
    public function __construct(PickrrInterface $pickrrRepository)
    {
        $this->pickrrRepository = $pickrrRepository;
    }

    /**
     * @param PickrrTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(PickrrTable $table)
    {
        page_title()->setTitle(trans('plugins/pickrr::pickrr.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/pickrr::pickrr.create'));

        return $formBuilder->create(PickrrForm::class)->renderForm();
    }

    /**
     * @param PickrrRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(PickrrRequest $request, BaseHttpResponse $response)
    {
        $pickrr = $this->pickrrRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(PICKRR_MODULE_SCREEN_NAME, $request, $pickrr));

        return $response
            ->setPreviousUrl(route('pickrr.index'))
            ->setNextUrl(route('pickrr.edit', $pickrr->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $pickrr = $this->pickrrRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $pickrr));

        page_title()->setTitle(trans('plugins/pickrr::pickrr.edit') . ' "' . $pickrr->name . '"');

        return $formBuilder->create(PickrrForm::class, ['model' => $pickrr])->renderForm();
    }

    /**
     * @param int $id
     * @param PickrrRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, PickrrRequest $request, BaseHttpResponse $response)
    {
        $pickrr = $this->pickrrRepository->findOrFail($id);

        $pickrr->fill($request->input());

        $pickrr = $this->pickrrRepository->createOrUpdate($pickrr);

        event(new UpdatedContentEvent(PICKRR_MODULE_SCREEN_NAME, $request, $pickrr));

        return $response
            ->setPreviousUrl(route('pickrr.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $pickrr = $this->pickrrRepository->findOrFail($id);

            $this->pickrrRepository->delete($pickrr);

            event(new DeletedContentEvent(PICKRR_MODULE_SCREEN_NAME, $request, $pickrr));

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
            $pickrr = $this->pickrrRepository->findOrFail($id);
            $this->pickrrRepository->delete($pickrr);
            event(new DeletedContentEvent(PICKRR_MODULE_SCREEN_NAME, $request, $pickrr));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
 
    public function postSettings(
        Request $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        foreach ($request->except([
            '_token',
        ]) as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();
        $cache = new Cache(app('cache'), HandleShippingFeeService::class);
        $cache->flush();

        $message = trans('plugins/pickrr::pickrr.saved_shipping_settings_success');
        $isError = false;
        return $response->setError($isError)->setMessage($message);
    }
}
