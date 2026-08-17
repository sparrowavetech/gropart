<?php

namespace Botble\Sms\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Botble\Sms\Forms\SmsForm;
use Botble\Sms\Tables\SmsTable;
use Botble\Base\Forms\FormBuilder;
use Botble\Sms\Http\Requests\SmsRequest;
use Botble\Sms\Http\Requests\UpdateSettingsRequest;
use Botble\Sms\Models\SmsLog;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Tables\SmsDeliveryReportTable;
use Botble\Setting\Supports\SettingStore;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sms\Repositories\Interfaces\SmsInterface;
use Illuminate\Support\Facades\Cache;

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
        $smsConfig = $this->smsSettingsConfig();
        $smsBalance = app(SmsHandler::class)->getBalance();

        return view('plugins/sms::settings', compact('smsConfig', 'smsBalance'));
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
        $settings = $request->except(['_token']);
        $settings['sms_url'] = $this->buildSendUrl($request);
        $settings['sms_delivery_report_url'] = $this->buildDeliveryUrl($request);
        $settings['sms_balance_url'] = $this->buildBalanceUrl($request);

        foreach ($settings as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();
        $response->setNextUrl(route('sms.settings'));
        return $response
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function deliveryReports(SmsDeliveryReportTable $table)
    {
        page_title()->setTitle(trans('plugins/sms::sms.delivery_report.title'));

        $table->setAjaxUrl(route('sms.delivery-reports.data'));

        return $table->renderTable();
    }

    public function deliveryReportsData(SmsDeliveryReportTable $table)
    {
        $this->syncRecentDeliveryReports();

        return $table->ajax();
    }

    private function syncRecentDeliveryReports(): void
    {
        if (! Cache::add('sms_delivery_reports_sync_lock', true, 60)) {
            return;
        }

        $handler = app(SmsHandler::class);

        SmsLog::query()
            ->whereNotNull('job_id')
            ->where(function ($query) {
                $query->whereNull('delivered_at')->orWhere('status', 'Submitted');
            })
            ->latest('id')
            ->limit(20)
            ->pluck('job_id')
            ->each(fn (string $jobId) => $handler->getDeliveryReport($jobId));
    }

    private function smsSettingsConfig(): array
    {
        $send = $this->parseGatewayUrl((string) setting('sms_url'));
        $delivery = $this->parseGatewayUrl((string) setting('sms_delivery_report_url'));
        $balance = $this->parseGatewayUrl((string) setting('sms_balance_url'));
        $query = $send['query'];

        return [
            'sms_base_api_url' => setting('sms_base_api_url') ?: $send['base_api_url'],
            'sms_user' => setting('sms_user') ?: ($query['user'] ?? $query['User'] ?? ''),
            'sms_password' => setting('sms_password') ?: ($query['password'] ?? $query['Password'] ?? ''),
            'sms_send_api_call' => setting('sms_send_api_call') ?: ($send['api_call'] ?: 'SendSMS'),
            'sms_sender_id' => setting('sms_sender_id') ?: ($query['senderid'] ?? $query['SenderId'] ?? ''),
            'sms_channel' => setting('sms_channel') ?: ($query['channel'] ?? $query['Channel'] ?? ''),
            'sms_dcs' => setting('sms_dcs') ?: ($query['DCS'] ?? '0'),
            'sms_flashsms' => setting('sms_flashsms') ?: ($query['flashsms'] ?? '0'),
            'sms_route' => setting('sms_route') ?: ($query['route'] ?? ''),
            'sms_dlt_template_id' => setting('sms_dlt_template_id') ?: ($query['DLTTemplateId'] ?? '{{template_id}}'),
            'sms_peid' => setting('sms_peid') ?: ($query['PEID'] ?? ''),
            'sms_delivery_api_call' => setting('sms_delivery_api_call') ?: ($delivery['api_call'] ?: 'GetDelivery'),
            'sms_balance_api_call' => setting('sms_balance_api_call') ?: ($balance['api_call'] ?: 'GetBalance'),
        ];
    }

    private function parseGatewayUrl(string $url): array
    {
        $parts = parse_url($url) ?: [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $pathParts = $path === '' ? [] : explode('/', $path);
        $apiCall = (string) array_pop($pathParts);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $basePath = $pathParts ? '/' . implode('/', $pathParts) : '';
        $baseApiUrl = empty($parts['scheme']) || empty($parts['host']) ? '' : $parts['scheme'] . '://' . $parts['host'] . $port . $basePath;

        return compact('baseApiUrl') + [
            'base_api_url' => rtrim($baseApiUrl, '/'),
            'api_call' => $apiCall,
            'query' => $query,
        ];
    }

    private function buildSendUrl(Request $request): string
    {
        return $this->joinApiUrl($request->input('sms_base_api_url'), $request->input('sms_send_api_call', 'SendSMS')) . '?' . $this->buildQuery([
            'user' => $request->input('sms_user'),
            'password' => $request->input('sms_password'),
            'senderid' => $request->input('sms_sender_id'),
            'channel' => $request->input('sms_channel'),
            'DCS' => $request->input('sms_dcs', '0'),
            'flashsms' => $request->input('sms_flashsms', '0'),
            'number' => '{{mobile}}',
            'text' => '{{message}}',
            'route' => $request->input('sms_route'),
            'DLTTemplateId' => $request->input('sms_dlt_template_id', '{{template_id}}'),
            'PEID' => $request->input('sms_peid'),
        ]);
    }

    private function buildDeliveryUrl(Request $request): string
    {
        return $this->joinApiUrl($request->input('sms_base_api_url'), $request->input('sms_delivery_api_call', 'GetDelivery')) . '?' . $this->buildQuery([
            'user' => '{{user}}',
            'password' => '{{password}}',
            'Jobid' => '{{job_id}}',
        ]);
    }

    private function buildBalanceUrl(Request $request): string
    {
        return $this->joinApiUrl($request->input('sms_base_api_url'), $request->input('sms_balance_api_call', 'GetBalance')) . '?' . $this->buildQuery([
            'User' => '{{user}}',
            'Password' => '{{password}}',
        ]);
    }

    private function joinApiUrl(?string $baseUrl, ?string $apiCall): string
    {
        return rtrim((string) $baseUrl, '/') . '/' . ltrim((string) $apiCall, '/');
    }

    private function buildQuery(array $parameters): string
    {
        return collect($parameters)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $key) => rawurlencode((string) $key) . '=' . (str_contains((string) $value, '{{') ? $value : rawurlencode((string) $value)))
            ->implode('&');
    }
}
