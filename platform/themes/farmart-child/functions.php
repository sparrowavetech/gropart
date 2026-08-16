<?php

app()->booted(function () {
    if (function_exists('theme_option')) {
        theme_option()
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
