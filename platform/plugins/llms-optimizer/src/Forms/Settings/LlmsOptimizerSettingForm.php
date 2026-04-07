<?php

namespace Shaqi\LlmsOptimizer\Forms\Settings;

use Botble\Setting\Forms\SettingForm;
use Botble\Slug\Facades\SlugHelper;
use Shaqi\LlmsOptimizer\Http\Requests\Settings\LlmsOptimizerSettingRequest;

class LlmsOptimizerSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle('LLMS Optimizer Settings')
            ->setSectionDescription('Configure how llms.txt file is generated for AI crawlers')
            ->setValidatorClass(LlmsOptimizerSettingRequest::class)
            ->add('enabled', 'onOffCheckbox', [
                'label' => 'Enable Plugin',
                'value' => get_llms_optimizer_setting('enabled', llms_optimizer_config('defaults.enabled', true)),
                'help_block' => [
                    'text' => 'Enable or disable the LLMS Optimizer plugin',
                ],
            ])
            ->add('html_content_types_header', 'html', [
                'html' => '<h4 class="mb-3 mt-4">Content Types</h4><p class="text-muted">Select which content types to include in llms.txt file</p>',
            ]);

        // Dynamically add checkboxes for all slugable models
        $supportedModels = SlugHelper::supportedModels();

        foreach ($supportedModels as $modelClass => $modelName) {
            $settingKey = llms_optimizer_model_setting_key($modelClass, $supportedModels);
            $legacySettingKey = llms_optimizer_legacy_model_setting_key($modelClass);

            // Get display name
            $displayName = is_callable($modelName) ? $modelName() : $modelName;

            $this->add($settingKey, 'onOffCheckbox', [
                'label' => 'Enable ' . $displayName,
                'value' => get_llms_optimizer_setting(
                    $settingKey,
                    get_llms_optimizer_setting($legacySettingKey, true)
                ),
                'help_block' => [
                    'text' => 'Include ' . strtolower($displayName) . ' in llms.txt file',
                ],
            ]);
        }

        $this
            ->add('html_formatting_header', 'html', [
                'html' => '<h4 class="mb-3 mt-4">Formatting Options</h4><p class="text-muted">Configure how the llms.txt file is formatted</p>',
            ])
            ->add('site_description', 'textarea', [
                'label' => 'Site Description',
                'value' => get_llms_optimizer_setting('site_description', ''),
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Enter a detailed description of your site for AI systems (optional)',
                ],
                'help_block' => [
                    'text' => 'Custom description for your site. Leave empty to use site tagline instead.',
                ],
            ])
            ->add('include_site_tagline', 'onOffCheckbox', [
                'label' => 'Include Site Tagline',
                'value' => get_llms_optimizer_setting('include_site_tagline', llms_optimizer_config('defaults.include_site_tagline', true)),
                'help_block' => [
                    'text' => 'Include site description/tagline in the output (used if custom description is empty)',
                ],
            ])
            ->add('include_descriptions', 'onOffCheckbox', [
                'label' => 'Include Item Descriptions',
                'value' => get_llms_optimizer_setting('include_descriptions', llms_optimizer_config('defaults.include_descriptions', true)),
                'help_block' => [
                    'text' => 'Include brief descriptions for each item (if available)',
                ],
            ])
            ->add('include_sitemap_reference', 'onOffCheckbox', [
                'label' => 'Include Sitemap Reference',
                'value' => get_llms_optimizer_setting('include_sitemap_reference', llms_optimizer_config('defaults.include_sitemap_reference', true)),
                'help_block' => [
                    'text' => 'Include reference to XML sitemap',
                ],
            ])
            ->add('output_mode', 'customSelect', [
                'label' => 'Output Mode',
                'choices' => llms_optimizer_config('output_modes', []),
                'selected' => get_llms_optimizer_setting('output_mode', llms_optimizer_config('defaults.output_mode', 'dynamic')),
                'help_block' => [
                    'text' => 'Choose how to serve llms.txt: dynamic route, static file, or both',
                ],
            ])
            ->add('sorting_order', 'customSelect', [
                'label' => 'Sorting Order',
                'choices' => llms_optimizer_config('sorting_orders', []),
                'selected' => get_llms_optimizer_setting('sorting_order', llms_optimizer_config('defaults.sorting_order', 'newest')),
                'help_block' => [
                    'text' => 'How to sort items in each section',
                ],
            ])
            ->add('link_format', 'customSelect', [
                'label' => 'Link Format',
                'choices' => llms_optimizer_config('link_formats', []),
                'selected' => get_llms_optimizer_setting('link_format', llms_optimizer_config('defaults.link_format', 'markdown')),
                'help_block' => [
                    'text' => 'Format for displaying links',
                ],
            ])
            ->add('include_token_count', 'onOffCheckbox', [
                'label' => 'Include Token Count',
                'value' => get_llms_optimizer_setting('include_token_count', llms_optimizer_config('defaults.include_token_count', false)),
                'help_block' => [
                    'text' => 'Show estimated token count at the end of the file',
                ],
            ])
            ->add('html_optional_section_header', 'html', [
                'html' => '<h4 class="mb-3 mt-4">Optional Section</h4><p class="text-muted">Configure the optional section for less important content</p>',
            ])
            ->add('include_optional_section', 'onOffCheckbox', [
                'label' => 'Include Optional Section',
                'value' => get_llms_optimizer_setting('include_optional_section', llms_optimizer_config('defaults.include_optional_section', false)),
                'help_block' => [
                    'text' => 'Add an "Optional" section for less critical information',
                ],
            ])
            ->add('optional_links', 'textarea', [
                'label' => 'Optional Links',
                'value' => get_llms_optimizer_setting('optional_links', ''),
                'attr' => [
                    'rows' => 5,
                    'placeholder' => '[Changelog](https://example.com/changelog)' . "\n" . '[Community](https://example.com/community)',
                ],
                'help_block' => [
                    'text' => 'Add custom links to the Optional section. One link per line in markdown format: [Title](URL)',
                ],
            ])
            ->add('html_performance_header', 'html', [
                'html' => '<h4 class="mb-3 mt-4">Performance & Caching</h4><p class="text-muted">Configure caching and performance settings</p>',
            ])
            ->add('cache_duration', 'number', [
                'label' => 'Cache Duration (minutes)',
                'value' => get_llms_optimizer_setting('cache_duration', llms_optimizer_config('defaults.cache_duration', 60)),
                'attr' => [
                    'min' => 0,
                ],
                'help_block' => [
                    'text' => 'How long to cache the generated content (0 to disable caching)',
                ],
            ])
            ->add('max_items_per_type', 'number', [
                'label' => 'Max Items Per Type',
                'value' => get_llms_optimizer_setting('max_items_per_type', llms_optimizer_config('defaults.max_items_per_type', 100)),
                'attr' => [
                    'min' => 1,
                    'max' => 1000,
                ],
                'help_block' => [
                    'text' => 'Maximum number of items to include per content type (1-1000)',
                ],
            ]);
    }
}
