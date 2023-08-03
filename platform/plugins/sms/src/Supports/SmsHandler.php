<?php

namespace Botble\Sms\Supports;

use ArrayAccess;
use Botble\Sms\Events\SendSmsEvent;
use Botble\Setting\Supports\SettingStore;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
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
    public function setVariableValue(string $variable, string $value, string $module = null): self
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
        $this->send($this->getContent(), $phone, $args, $debug);

        return true;
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
    public function send(string $content, $to = null, array $args = [], bool $debug = false)
    {
        try {
            $content = $this->prepareData($content);
            $url = $this->getUrl($to,$content);
        
            $res = file_get_contents($url);
            
           // event(new SendSmsEvent($url, $args, $debug));
        } catch (Exception $exception) {
            if ($debug) {
                throw $exception;
            }
            info($exception->getMessage());
            //$this->sendErrorException($exception);
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
        $this->templateId = $template->template_id;
        return $this->prepareData($template->template);
    }
    /**
     * @return string
     */
    public function getUrl($to,$content): string
    {
        $finalvar = ['mobile'=>$to,'message'=>urlencode($content),'template_id'=>$this->templateId];
      
        $url  = setting('sms_url');
        foreach($finalvar as $key => $value){
            $url  = str_replace("{{ ".$key." }}",$value,$url);
            $url  = str_replace("{{ ".$key."}}",$value,$url);
            $url  = str_replace("{{".$key." }}",$value,$url);
            $url  = str_replace("{{".$key."}}",$value,$url);
        }
        return $url ;
    }
}
