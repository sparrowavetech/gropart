<?php

namespace Botble\Sms\Http\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Botble\Sms\Forms\SmsForm;
use Botble\Sms\Tables\SmsTable;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Models\Enquiry;
use Botble\Sms\Http\Requests\SmsRequest;
use Botble\Sms\Http\Requests\UpdateSettingsRequest;
use Botble\Setting\Supports\SettingStore;
use Botble\Ecommerce\Supports\OrderHelper;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sms\Repositories\Interfaces\SmsInterface;

class SmsController extends BaseController
{
    /**
     * @var SmsInterface
     */
    protected $smsRepository;

    /**
     * @param SmsInterface $smsRepository
     */
    public function __construct(SmsInterface $smsRepository)
    {
        $this->smsRepository = $smsRepository;
    }

    /**
     * @param SmsTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(SmsTable $table)
    {
        page_title()->setTitle(trans('plugins/sms::sms.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/sms::sms.create'));

        return $formBuilder->create(SmsForm::class)->renderForm();
    }

    /**
     * @param SmsRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(SmsRequest $request, BaseHttpResponse $response)
    {
        $sms = $this->smsRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(SMS_MODULE_SCREEN_NAME, $request, $sms));

        return $response
            ->setPreviousUrl(route('sms.index'))
            ->setNextUrl(route('sms.edit', $sms->id))
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
        $sms = $this->smsRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $sms));

        page_title()->setTitle(trans('plugins/sms::sms.edit') . ' "' . $sms->name . '"');

        return $formBuilder->create(SmsForm::class, ['model' => $sms])->renderForm();
    }

    /**
     * @param int $id
     * @param SmsRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, SmsRequest $request, BaseHttpResponse $response)
    {
        $sms = $this->smsRepository->findOrFail($id);

        $sms->fill($request->input());

        $sms = $this->smsRepository->createOrUpdate($sms);

        event(new UpdatedContentEvent(SMS_MODULE_SCREEN_NAME, $request, $sms));

        return $response
            ->setPreviousUrl(route('sms.index'))
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
            $sms = $this->smsRepository->findOrFail($id);

            $this->smsRepository->delete($sms);

            event(new DeletedContentEvent(SMS_MODULE_SCREEN_NAME, $request, $sms));

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
            $sms = $this->smsRepository->findOrFail($id);
            $this->smsRepository->delete($sms);
            event(new DeletedContentEvent(SMS_MODULE_SCREEN_NAME, $request, $sms));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
     /**
     * @return Factory|View
     */
    public function getSettings()
    {
        page_title()->setTitle(trans('plugins/sms::sms.setting'));
        $sms_url = setting('sms_url');
        return view('plugins/sms::settings', compact('sms_url'));
    }
    /**
     * @param UpdateSettingsRequest $request
     * @param BaseHttpResponse $response
     * @param SettingStore $settingStore
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function postSettings(
        UpdateSettingsRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        foreach ($request->except([
            '_token',
        ]) as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();
        $response->setNextUrl(route('sms.settings'));
        return $response
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
    public function test()
    {
        $enquiry = Enquiry::first();
        $res = OrderHelper::sendEnquirySms($enquiry);


    }
}
