<?php

namespace Botble\Sms\Supports;

use ArrayAccess;
use Botble\Sms\Events\SendSmsEvent;
use Botble\Sms\Models\SmsLog;
use Botble\Setting\Supports\SettingStore;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Botble\Base\Supports\EmailHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class SmsHandler
{
    /**
     * @var string
     */
    protected $type = 'plugins';

    /**
     * @var string
     */
    protected $module = null;

    /**
     * @var string
     */
    protected $template = null;

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var array
     */
    protected $variableValues = [];
    /**
     * @param string $module
     * @return $this
     */
    protected $templateId = null;
    /**
     * @param string $module
     * @return $this
     */

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SmsHandler
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSettingPrefix(): string
    {
        return 'sms_';
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     * @return SmsHandler
     */
    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }
    /**
     * @return array
     */
    public function getCoreVariables(): array
    {
        return [
            'site_title' => trans('core/base::base.email_template.site_title'),
            'site_url' => trans('core/base::base.email_template.site_url'),
            'date_time' => trans('core/base::base.email_template.date_time'),
            'date_year' => trans('core/base::base.email_template.date_year'),
            'site_admin_email' => trans('core/base::base.email_template.site_admin_email'),
        ];
    }

    /**
     * @param string $variable
     * @param string $value
     * @param string|null $module
     * @return $this
     */
    public function setVariableValue(string $variable, string $value, ?string $module = null): self
    {
        Arr::set($this->variableValues, ($module ?: $this->module) . '.' . $variable, $value);

        return $this;
    }

    /**
     * @param string|null $module
     * @return array
     */
    public function getVariableValues(?string $module = null): array
    {
        if ($module) {
            return Arr::get($this->variableValues, $module, []);
        }

        return $this->variableValues;
    }

    /**
     * @param array $data
     * @param string|null $module
     * @return $this
     */
    public function setVariableValues(array $data, ?string $module = null): self
    {

        foreach ($data as $name => $value) {
            $this->variableValues[$module ?: $this->module][$name] = $value;
        }
        return $this;
    }
    /**
     * @param string|null $module
     * @return array
     */
    public function getTemplateInfo(?string $module = null): array
    {
        return $this->variableValues;
    }


    /**
     * @return array
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @param string $type
     * @param string $module
     * @param string $name
     * @return array|ArrayAccess|mixed
     */
    public function getTemplateData(string $type, string $module, string $name)
    {

        return Arr::get($this->templates, $type . '.' . $module . '.templates.' . $name);
    }

    /**
     * @param string $type
     * @param string $module
     * @param string $name
     * @return array|ArrayAccess|mixed
     */
    public function getVariables(string $type, string $module, string $name)
    {
        $this->template = $name;
        return config($type . '.sms.sms');
    }

    /**
     * @param string $template
     * @param string|null|array $email
     * @param array $args
     * @param bool $debug
     * @param string $type
     * @param null $subject
     * @return bool
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function sendUsingTemplate(string $template, $phone = null, array $args = [], bool $debug = false, string $type = 'plugins', $subject = null): bool
    {
        if (!$this->templateEnabled($template)) {
            return false;
        }

        $this->type = $type;
        $this->template = $template;
        return $this->send($this->getContent(), $phone, $args, $debug);
    }

    /**
     * @param string $template
     * @param string $type
     * @return array|SettingStore|string|null
     */
    public function templateEnabled(string $template, string $type = 'plugins')
    {
        return get_setting_sms_status($template);
    }

    /**
     * @param string $content
     * @param string $title
     * @param string|array $to
     * @param array $args
     * @param bool $debug
     * @throws Throwable
     */
    public function send(string $content, $to = null, array $args = [], bool $debug = false): bool
    {
        try {
            $content = $this->prepareData($content);
            $url = $this->getUrl($to, $content);

            if ($url === '') {
                return false;
            }

            $response = Http::timeout((int) setting('sms_http_timeout', 10))
                ->retry((int) setting('sms_http_retries', 1), 300)
                ->get($url);

            event(new SendSmsEvent($url, $args, $debug));

            if (! $response->successful()) {
                Log::warning('SMS API request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $this->rememberLastSendResponse($response->json(), (string) $to, $content);

            return true;
        } catch (Exception $exception) {
            if ($debug) {
                throw $exception;
            }

            Log::error('SMS API request exception: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * @param string $content
     * @return string
     */
    public function prepareData(string $content): string
    {
        $this->initVariableValues();

        if (!empty($content)) {


            if ($this->module && $this->template) {
                $variables = $this->getVariables($this->type ?: 'plugins', $this->module, $this->template);
                $content = $this->replaceVariableValue(
                    array_keys($variables),
                    $this->module,
                    $content
                );
                $content = $this->replaceVariableValue(array_keys($this->getCoreVariables()), 'core', $content);
            }
        }
        return  $content;
    }

    public function initVariableValues()
    {
        $this->variableValues['core'] = [
            'site_title' => setting('admin_title') ?: config('app.name'),
            'site_url' => url(''),
            'date_time' => Carbon::now()->toDateTimeString(),
            'date_year' => Carbon::now()->format('Y'),
            'site_admin_email' => get_admin_email()->first(),
        ];
    }

    /**
     * @param array $variables
     * @param string $module
     * @param string $content
     * @return string
     */

    protected function replaceVariableValue(array $variables, string $module, string $content): string
    {
        foreach ($variables as $variable) {
            $keys = [
                '{{ ' . $variable . ' }}',
                '{{' . $variable . '}}',
                '{{ ' . $variable . '}}',
                '{{' . $variable . ' }}',
                '<?php echo e(' . $variable . '); ?>',
            ];

            foreach ($keys as $key) {
                $content = str_replace($key, $this->getVariableValue($variable, $module), $content);
            }
        }

        return $content;
    }

    /**
     * @param string $variable
     * @param string $module
     * @param string $default
     * @return string
     */
    public function getVariableValue(string $variable, string $module, string $default = ''): string
    {
        return (string)Arr::get($this->variableValues, $module . '.' . $variable, $default);
    }

    /**
     * Sends an email to the developer about the exception.
     *
     * @param Exception|Throwable $exception
     * @return void
     *
     * @throws Throwable
     */
    public function sendErrorException(Exception $exception)
    {
        try {
            $ex = FlattenException::create($exception);

            $url = URL::full();
            $error = $this->renderException($exception);
            $email = new EmailHandler;
            $email->send(
                view('core/base::emails.error-reporting', compact('url', 'ex', 'error'))->render(),
                $exception->getFile(),
                !empty(config('core.base.general.error_reporting.to')) ?
                    config('core.base.general.error_reporting.to') :
                    get_admin_email()->toArray()
            );
        } catch (Exception $ex) {
            info($ex->getMessage());
        }
    }

    /**
     * @param Throwable|Exception $exception
     * @return string
     */
    protected function renderException($exception): string
    {
        $renderer = new HtmlErrorRenderer(true);

        $exception = $renderer->render($exception);

        if (!headers_sent()) {
            http_response_code($exception->getStatusCode());

            foreach ($exception->getHeaders() as $name => $value) {
                header($name . ': ' . $value, false);
            }
        }

        return $exception->getAsString();
    }
    /**
     * @return string
     */
    public function getContent(): string
    {
        $template = get_setting_sms_template_content($this->template);
        if (! $template) {
            return '';
        }

        $this->templateId = $template->template_id;

        return $this->prepareData((string) $template->template);
    }
    /**
     * @return string
     */
    public function getUrl($to, $content): string
    {
        $url = trim((string) setting('sms_url'));

        if ($url === '' || empty($to) || $content === '') {
            return '';
        }

        $variables = [
            'mobile' => $this->normalizeMobileForUrl((string) $to, $url),
            'message' => $content,
            'template_id' => (string) $this->templateId,
        ];

        foreach ($variables as $key => $value) {
            $encodedValue = rawurlencode($value);
            foreach ([
                '{{ ' . $key . ' }}',
                '{{ ' . $key . '}}',
                '{{' . $key . ' }}',
                '{{' . $key . '}}',
            ] as $placeholder) {
                $url = str_replace($placeholder, $encodedValue, $url);
            }
        }

        return $url;
    }

    public function getDeliveryReport(?string $jobId = null): array
    {
        $jobId = trim((string) ($jobId ?: setting('sms_last_job_id')));
        $urls = array_values(array_filter(array_unique([
            $this->getDeliveryReportUrl($jobId),
            $this->replacePlaceholders($this->getDefaultDeliveryReportUrl(), ['job_id' => $jobId]),
        ])));

        if ($jobId === '' || ! $urls) {
            return [
                'success' => false,
                'message' => 'No SMS Job ID is available yet.',
                'response' => null,
            ];
        }

        $lastException = null;

        foreach ($urls as $url) {
            try {
                $response = Http::timeout((int) setting('sms_http_timeout', 10))
                    ->retry((int) setting('sms_http_retries', 1), 300)
                    ->get($url);

                $body = $response->json();
                setting()->set('sms_last_delivery_report', json_encode($body ?: $response->body()));
                setting()->save();

                if (is_array($body)) {
                    $this->syncDeliveryReport($jobId, $body);
                }

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'message' => 'Delivery report updated.',
                        'response' => $body ?: $response->body(),
                    ];
                }
            } catch (Exception $exception) {
                $lastException = $exception;
                Log::warning('SMS delivery report exception: ' . $exception->getMessage());
            }
        }

        return [
            'success' => false,
            'message' => $lastException?->getMessage() ?: 'Delivery report request failed.',
            'response' => null,
        ];
    }

    private function syncDeliveryReport(string $jobId, array $payload): void
    {
        foreach ((array) Arr::get($payload, 'DeliveryReports', []) as $report) {
            $messageId = (string) Arr::get($report, 'MessageId', '');
            $query = SmsLog::query()->where('job_id', $jobId);

            if ($messageId !== '') {
                $query->where(function ($query) use ($messageId) {
                    $query->whereNull('message_id')->orWhere('message_id', $messageId);
                });
            }

            $log = $query->latest('id')->first();

            if (! $log) {
                continue;
            }

            $log->fill([
                'message_id' => $messageId ?: $log->message_id,
                'status' => (string) Arr::get($report, 'DeliveryStatus', $log->status),
                'delivered_at' => Arr::get($report, 'DeliveryDate') ?: $log->delivered_at,
                'delivery_error_code' => Arr::get($report, 'ErrorCode') ?: null,
                'cost' => $this->firstReportValue($report, ['Cost', 'cost', 'SMSCost', 'SmsCost', 'Credit', 'Credits']) ?? $log->cost,
                'op_cr' => $this->firstReportValue($report, ['OPCR', 'OP/CR', 'OperatorCircle', 'Operator', 'Circle', 'Network']) ?? $log->op_cr,
                'delivery_payload' => $report,
            ])->save();
        }
    }

    private function firstReportValue(array $report, array $keys): mixed
    {
        foreach ($keys as $key) {
            $value = Arr::get($report, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function getDeliveryReportUrl(?string $jobId = null): string
    {
        $url = trim((string) setting('sms_delivery_report_url'));
        $credentials = $this->smsUrlCredentials();

        if ($url === '') {
            $url = $this->getDefaultDeliveryReportUrl();
        }

        return $this->replacePlaceholders($url, [
            'job_id' => (string) $jobId,
            'jobid' => (string) $jobId,
            'Jobid' => (string) $jobId,
            'user' => $credentials['user'] ?? '',
            'User' => $credentials['user'] ?? '',
            'password' => $credentials['password'] ?? '',
            'Password' => $credentials['password'] ?? '',
        ]);
    }

    public function getDefaultDeliveryReportUrl(): string
    {
        $smsUrl = trim((string) setting('sms_url'));

        if ($smsUrl === '') {
            return '';
        }

        $parts = parse_url($smsUrl) ?: [];
        $credentials = $this->smsUrlCredentials($parts);

        if (empty($parts['scheme']) || empty($parts['host']) || empty($credentials['user']) || empty($credentials['password'])) {
            return '';
        }

        $base = $parts['scheme'] . '://' . $parts['host'] . '/api/mt/GetDelivery';

        return $base . '?user=' . rawurlencode((string) $credentials['user'])
            . '&password=' . rawurlencode((string) $credentials['password'])
            . '&Jobid={{job_id}}';
    }

    public function getBalance(): array
    {
        $urls = array_values(array_filter(array_unique([
            $this->getBalanceUrl(),
            $this->getDefaultBalanceUrl(),
        ])));

        if (! $urls) {
            return [
                'success' => false,
                'message' => 'SMS balance API credentials are not configured.',
                'balance' => [],
                'response' => null,
            ];
        }

        $lastException = null;

        foreach ($urls as $url) {
            try {
                $response = Http::timeout((int) setting('sms_http_timeout', 10))
                    ->retry((int) setting('sms_http_retries', 1), 300)
                    ->get($url);

                $body = $response->json();
                $balance = $this->parseBalance((string) Arr::get($body, 'Balance', ''));
                $success = $response->successful() && Arr::get($body, 'ErrorCode') === '0';

                if (is_array($body)) {
                    setting()->set('sms_last_balance_response', json_encode($body));
                    setting()->save();
                }

                if ($success) {
                    return [
                        'success' => true,
                        'message' => (string) Arr::get($body, 'ErrorMessage', 'Done'),
                        'balance' => $balance,
                        'response' => $body ?: $response->body(),
                    ];
                }
            } catch (Exception $exception) {
                $lastException = $exception;
                Log::warning('SMS balance request exception: ' . $exception->getMessage());
            }
        }

        $cached = json_decode((string) setting('sms_last_balance_response'), true);

        return [
            'success' => false,
            'message' => $lastException?->getMessage() ?: 'Balance request failed.',
            'balance' => is_array($cached) ? $this->parseBalance((string) Arr::get($cached, 'Balance', '')) : [],
            'response' => $cached,
        ];
    }

    public function getBalanceUrl(): string
    {
        $smsUrl = trim((string) setting('sms_url'));

        if ($smsUrl === '') {
            return '';
        }

        $parts = parse_url($smsUrl);
        $credentials = $this->smsUrlCredentials($parts);
        $user = $credentials['user'];
        $password = $credentials['password'];

        if (empty($parts['scheme']) || empty($parts['host']) || $user === '' || $password === '') {
            return '';
        }

        $url = trim((string) setting('sms_balance_url'));

        if ($url !== '') {
            return $this->replacePlaceholders($url, [
                'user' => $user,
                'User' => $user,
                'password' => $password,
                'Password' => $password,
            ]);
        }

        return $this->getDefaultBalanceUrl();
    }

    public function getDefaultBalanceUrl(): string
    {
        $smsUrl = trim((string) setting('sms_url'));

        if ($smsUrl === '') {
            return '';
        }

        $parts = parse_url($smsUrl);
        $credentials = $this->smsUrlCredentials($parts);
        $user = $credentials['user'];
        $password = $credentials['password'];

        if (empty($parts['scheme']) || empty($parts['host']) || $user === '' || $password === '') {
            return '';
        }

        return $parts['scheme'] . '://' . $parts['host'] . '/api/mt/GetBalance'
            . '?User=' . rawurlencode($user)
            . '&Password=' . rawurlencode($password);
    }

    private function smsUrlCredentials(?array $parts = null): array
    {
        $parts ??= parse_url(trim((string) setting('sms_url'))) ?: [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        return [
            'user' => (string) ($query['user'] ?? $query['User'] ?? ''),
            'password' => (string) ($query['password'] ?? $query['Password'] ?? ''),
        ];
    }

    private function parseBalance(string $balance): array
    {
        $result = [];

        foreach (explode('|', $balance) as $item) {
            [$label, $value] = array_pad(explode(':', $item, 2), 2, null);

            if ($label !== null && $value !== null) {
                $result[trim($label)] = trim($value);
            }
        }

        return $result;
    }

    private function rememberLastSendResponse(mixed $payload, string $to, string $content): void
    {
        if (! is_array($payload)) {
            return;
        }

        $jobId = (string) Arr::get($payload, 'JobId', '');
        $messageId = (string) Arr::get($payload, 'MessageData.0.MessageId', '');
        $status = Arr::get($payload, 'ErrorCode') === '000' ? 'Submitted' : (string) Arr::get($payload, 'ErrorMessage', 'Failed');

        if ($jobId === '') {
            return;
        }

        setting()->set('sms_last_job_id', $jobId);
        setting()->set('sms_last_response', json_encode($payload));
        setting()->save();

        if (! Schema::hasTable('sms_logs')) {
            return;
        }

        SmsLog::query()->create([
            'job_id' => $jobId,
            'message_id' => $messageId ?: null,
            'template' => $this->template,
            'template_id' => (string) $this->templateId,
            'recipient' => preg_replace('/\D+/', '', $to) ?: $to,
            'message' => $content,
            'status' => $status,
            'sent_at' => now(),
            'response_payload' => $payload,
        ]);
    }

    private function replacePlaceholders(string $url, array $variables): string
    {
        foreach ($variables as $key => $value) {
            foreach ([
                '{{ ' . $key . ' }}',
                '{{ ' . $key . '}}',
                '{{' . $key . ' }}',
                '{{' . $key . '}}',
            ] as $placeholder) {
                $url = str_replace($placeholder, rawurlencode($value), $url);
            }
        }

        return $url;
    }

    private function normalizeMobileForUrl(string $mobile, string $url): string
    {
        $mobile = preg_replace('/\D+/', '', $mobile) ?: '';

        if (preg_match('/91\s*\{\{\s*mobile\s*\}\}/', $url) && strlen($mobile) === 12 && str_starts_with($mobile, '91')) {
            return substr($mobile, 2);
        }

        return $mobile;
    }
}
