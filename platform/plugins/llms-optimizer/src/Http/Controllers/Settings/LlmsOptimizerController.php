<?php

namespace Shaqi\LlmsOptimizer\Http\Controllers\Settings;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\Concerns\InteractsWithSettings;
use Shaqi\LlmsOptimizer\Facades\LlmsGenerator;
use Shaqi\LlmsOptimizer\Forms\Settings\LlmsOptimizerSettingForm;
use Shaqi\LlmsOptimizer\Http\Requests\Settings\LlmsOptimizerSettingRequest;

class LlmsOptimizerController extends BaseController
{
    use InteractsWithSettings;

    public function edit()
    {
        $this->pageTitle('LLMS Optimizer Settings');

        return LlmsOptimizerSettingForm::create()->renderForm();
    }

    public function update(LlmsOptimizerSettingRequest $request): BaseHttpResponse
    {
        $this->saveSettings($request->validated(), llms_optimizer_config('prefix', 'llms_optimizer_'));
        
        LlmsGenerator::clearCache();
        
        $outputMode = $request->input('output_mode');
        if (in_array($outputMode, ['static', 'both'])) {
            LlmsGenerator::saveStaticFile();
        }

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }

    public function preview(): BaseHttpResponse
    {
        $content = LlmsGenerator::generate();

        return $this
            ->httpResponse()
            ->setData([
                'content' => $content,
            ]);
    }

    public function regenerate(): BaseHttpResponse
    {
        LlmsGenerator::clearCache();
        
        $outputMode = get_llms_optimizer_setting('output_mode', llms_optimizer_config('defaults.output_mode', 'dynamic'));
        
        if (in_array($outputMode, ['static', 'both'])) {
            $success = LlmsGenerator::saveStaticFile();
            
            if (! $success) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Failed to regenerate static file. Please check file permissions.');
            }
        }

        return $this
            ->httpResponse()
            ->setMessage('LLMS.txt file regenerated successfully.');
    }

    public function clearCache(): BaseHttpResponse
    {
        LlmsGenerator::clearCache();

        return $this
            ->httpResponse()
            ->setMessage('Cache cleared successfully.');
    }
}

