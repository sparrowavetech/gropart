<?php

use Botble\Theme\Events\RenderingThemeOptionSettings;

app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
    if (function_exists('theme_option')) {
        theme_option()
            ->setField([
                'id' => 'product_description_layout',
                'section_id' => 'opt-text-subsection-ecommerce',
                'type' => 'customSelect',
                'label' => __('Product description layout'),
                'attributes' => [
                    'name' => 'product_description_layout',
                    'list' => ['legacy' => __('Use existing shopping setting'), 'tabs' => __('Tabs'), 'sections' => __('Full-width sections')],
                    'value' => 'legacy',
                ],
            ])
            ->setField([
                'id' => 'enable_top_upper_header',
                'section_id' => 'opt-text-subsection-general',
                'type' => 'customSelect',
                'label' => __('Enable Top Header CTA Banner?'),
                'attributes' => [
                    'name' => 'enable_top_upper_header',
                    'list' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'value' => 'yes',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ])
            ->setField([
                'id' => 'top_upper_header_text',
                'section_id' => 'opt-text-subsection-general',
                'type' => 'editor',
                'label' => __('Top Header CTA Text'),
                'attributes' => [
                    'name' => 'top_upper_header_text',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter any Text line to add in top header',
                    ],
                ],
            ])
            ->setField([
                'id' => 'enabled_product_categories_sidebar_on_header',
                'section_id' => 'opt-text-subsection-ecommerce',
                'type' => 'customSelect',
                'label' => __('Enable categories with sidebar on header?'),
                'attributes' => [
                    'name' => 'enabled_product_categories_sidebar_on_header',
                    'list' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'value' => 'yes',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ]);
    }
});
