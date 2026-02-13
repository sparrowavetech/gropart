<?php

namespace Shaqi\LlmsOptimizer\Http\Requests\Settings;

use Botble\Slug\Facades\SlugHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Str;

class LlmsOptimizerSettingRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'enabled' => 'nullable|boolean',
            'site_description' => 'nullable|string|max:500',
            'include_site_tagline' => 'nullable|boolean',
            'include_descriptions' => 'nullable|boolean',
            'include_sitemap_reference' => 'nullable|boolean',
            'include_optional_section' => 'nullable|boolean',
            'optional_links' => 'nullable|string',
            'output_mode' => 'required|in:dynamic,static,both',
            'sorting_order' => 'required|in:newest,alphabetical',
            'max_items_per_type' => 'required|integer|min:1|max:1000',
            'link_format' => 'required|in:markdown,plain',
            'include_token_count' => 'nullable|boolean',
            'cache_duration' => 'required|integer|min:0',
        ];

        // Dynamically add validation rules for all slugable models
        $supportedModels = SlugHelper::supportedModels();

        foreach ($supportedModels as $modelClass => $modelName) {
            $modelBaseName = class_basename($modelClass);
            $settingKey = 'enable_' . Str::snake(Str::plural($modelBaseName));
            $rules[$settingKey] = 'nullable|boolean';
        }

        return $rules;
    }
}

