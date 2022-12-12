<?php

namespace Botble\Sms\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Sms\Http\Requests\SmsRequest;
use Botble\Sms\Repositories\Interfaces\SmsInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Sms\Tables\SmsTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sms\Forms\SmsForm;
use Botble\Base\Forms\FormBuilder;

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

    public function SmsController(Type $var = null)
    {
        # code...
    }
}
