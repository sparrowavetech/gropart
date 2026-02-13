<?php

use Botble\Theme\Events\RenderingThemeOptionSettings;

app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
    theme_option()
        ->setField([
            'id' => 'sticky_header_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable sticky header?'),
            'attributes' => [
                'name' => 'sticky_header_enabled',
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
            'id' => 'sticky_header_mobile_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable sticky header on mobile?'),
            'attributes' => [
                'name' => 'sticky_header_mobile_enabled',
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
            'id' => 'sticky_header_content_position',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Sticky header content position?'),
            'attributes' => [
                'name' => 'sticky_header_content_position',
                'list' => [
                    'bottom' => __('Header bottom'),
                    'middle' => __('Header middle'),
                    'top' => __('Header top'),
                ],
                'value' => 'middle',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->setField([
            'id' => 'preloader_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable Preloader?'),
            'attributes' => [
                'name' => 'preloader_enabled',
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
            'id' => 'lazy_load_image_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable lazy load images?'),
            'attributes' => [
                'name' => 'lazy_load_image_enabled',
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
            'id' => 'image-placeholder',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'mediaImage',
            'label' => __('Image placeholder'),
            'attributes' => [
                'name' => 'image-placeholder',
                'value' => null,
            ],
        ])
        ->setField([
            'id' => 'use_source_assets_from',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Use source assets from?'),
            'attributes' => [
                'name' => 'use_source_assets_from',
                'list' => [
                    'local' => __('Local disk'),
                    'cdn' => __('Content delivery network (CDN)'),
                ],
                'value' => 'cdn',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->setField([
            'id' => 'hotline',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'text',
            'label' => __('Hotline'),
            'attributes' => [
                'name' => 'hotline',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('Hotline'),
                    'data-counter' => 30,
                ],
            ],
        ])
        ->setField([
            'id' => 'address',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'text',
            'label' => __('Address'),
            'attributes' => [
                'name' => 'address',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('Address'),
                    'data-counter' => 120,
                ],
            ],
        ])
        ->setSection([
            'title' => __('Style'),
            'id' => 'opt-text-subsection-style',
            'subsection' => true,
            'icon' => 'ti ti-brush',
        ])
        ->setField([
            'id' => 'primary_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Primary color'),
            'attributes' => [
                'name' => 'primary_color',
                'value' => '#fab528',
            ],
        ])
        ->setField([
            'id' => 'heading_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Heading color'),
            'attributes' => [
                'name' => 'heading_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'text_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Text color'),
            'attributes' => [
                'name' => 'text_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'primary_button_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Primary button color'),
            'attributes' => [
                'name' => 'primary_button_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'primary_button_background_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Primary button background color'),
            'attributes' => [
                'name' => 'primary_button_background_color',
                'value' => '#fab528',
            ],
        ])
        ->setField([
            'id' => 'top_header_background_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Top header background color'),
            'attributes' => [
                'name' => 'top_header_background_color',
                'value' => '#f7f7f7',
            ],
        ])
        ->setField([
            'id' => 'top_header_text_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Top header text color'),
            'attributes' => [
                'name' => 'top_header_text_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'middle_header_background_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Middle header background color'),
            'attributes' => [
                'name' => 'middle_header_background_color',
                'value' => '#fff',
            ],
        ])
        ->setField([
            'id' => 'middle_header_text_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Middle header text color'),
            'attributes' => [
                'name' => 'middle_header_text_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'bottom_header_background_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Bottom header background color'),
            'attributes' => [
                'name' => 'bottom_header_background_color',
                'value' => '#fff',
            ],
        ])
        ->setField([
            'id' => 'bottom_header_text_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Bottom header text color'),
            'attributes' => [
                'name' => 'bottom_header_text_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'header_deliver_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Header deliver color'),
            'attributes' => [
                'name' => 'header_deliver_color',
                'value' => '#000',
            ],
        ])
        ->setField([
            'id' => 'header_mobile_background_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Header mobile background color'),
            'attributes' => [
                'name' => 'header_mobile_background_color',
                'value' => '#fff',
            ],
        ])
        ->setField([
            'id' => 'header_mobile_icon_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Header mobile icon color'),
            'attributes' => [
                'name' => 'header_mobile_icon_color',
                'value' => '#222',
            ],
        ])
        ->setField([
            'id' => 'footer_text_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Footer text color'),
            'attributes' => [
                'name' => 'footer_text_color',
                'value' => '#555',
            ],
        ])
        ->setField([
            'id' => 'footer_heading_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Footer heading color'),
            'attributes' => [
                'name' => 'footer_heading_color',
                'value' => '#555',
            ],
        ])
        ->setField([
            'id' => 'footer_hover_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Footer hover color'),
            'attributes' => [
                'name' => 'footer_hover_color',
                'value' => '#fab528',
            ],
        ])
        ->setField([
            'id' => 'footer_border_color',
            'section_id' => 'opt-text-subsection-style',
            'type' => 'customColor',
            'label' => __('Footer border color'),
            'attributes' => [
                'name' => 'footer_border_color',
                'value' => '#ccc',
            ],
        ])
        ->setSection([
            'title' => __('Bottom Bar Menu'),
            'id' => 'opt-text-subsection-bottom-bar-menu',
            'subsection' => true,
            'icon' => 'ti ti-category-2',
        ])
        ->setField([
            'id' => 'bottom_bar_menu_show_text',
            'section_id' => 'opt-text-subsection-bottom-bar-menu',
            'type' => 'customSelect',
            'label' => __('Show menu text'),
            'attributes' => [
                'name' => 'bottom_bar_menu_show_text',
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
            'id' => 'bottom_bar_menu_text_font_size',
            'section_id' => 'opt-text-subsection-bottom-bar-menu',
            'type' => 'number',
            'label' => __('Menu text font size (px)'),
            'attributes' => [
                'name' => 'bottom_bar_menu_text_font_size',
                'value' => 11,
                'options' => [
                    'class' => 'form-control',
                    'min' => 8,
                    'max' => 20,
                ],
            ],
        ])
        ->setSection([
            'title' => __('Social links'),
            'id' => 'opt-text-subsection-social-links',
            'subsection' => true,
            'icon' => 'ti ti-share',
        ])
        ->setField([
            'id' => '404_page_image',
            'section_id' => 'opt-text-subsection-page',
            'type' => 'mediaImage',
            'label' => __('404 page image'),
            'attributes' => [
                'name' => '404_page_image',
                'value' => '',
            ],
        ])
        ->setField([
            'id' => 'social_share_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable social sharing?'),
            'attributes' => [
                'name' => 'social_share_enabled',
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
            'id' => 'store_list_layout',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Stores List Layout'),
            'attributes' => [
                'name' => 'store_list_layout',
                'list' => get_store_list_layouts(),
                'value' => 'grid',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->setField([
            'id' => 'default_vendor_cover_image',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'mediaImage',
            'label' => __('Default vendor cover image'),
            'attributes' => [
                'name' => 'default_vendor_cover_image',
                'value' => null,
            ],
            'helper' => __('This image will be used as the default cover image for vendor pages when vendors have not set their own cover image.'),
        ])
        ->setField([
            'id' => 'product_page_vendor_info_enabled',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Show vendor contact info on product page?'),
            'attributes' => [
                'name' => 'product_page_vendor_info_enabled',
                'list' => [
                    'no' => trans('core/base::base.no'),
                    'yes' => trans('core/base::base.yes'),
                ],
                'value' => 'no',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
            'helper' => __('Display vendor contact information box in the product detail page sidebar.'),
        ])
        ->setField([
            'id' => 'product_page_vendor_info_title',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'text',
            'label' => __('Vendor info box title'),
            'attributes' => [
                'name' => 'product_page_vendor_info_title',
                'value' => 'Contact Seller',
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('E.g., Contact Seller'),
                    'data-counter' => 120,
                ],
            ],
        ])
        ->setField([
            'id' => 'product_page_vendor_info_subtitle',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'text',
            'label' => __('Vendor info box subtitle'),
            'attributes' => [
                'name' => 'product_page_vendor_info_subtitle',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('E.g., Mon - Fri: 07AM - 06PM'),
                    'data-counter' => 255,
                ],
            ],
        ])
        ->setField([
            'id' => 'product_page_vendor_info_show_phone',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Show vendor phone number?'),
            'attributes' => [
                'name' => 'product_page_vendor_info_show_phone',
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
            'id' => 'product_page_vendor_info_show_email',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Show vendor email?'),
            'attributes' => [
                'name' => 'product_page_vendor_info_show_email',
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
            'id' => 'product_page_vendor_info_show_whatsapp',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Show vendor WhatsApp?'),
            'attributes' => [
                'name' => 'product_page_vendor_info_show_whatsapp',
                'list' => [
                    'yes' => trans('core/base::base.yes'),
                    'no' => trans('core/base::base.no'),
                ],
                'value' => 'yes',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
            'helper' => __('WhatsApp link is retrieved from vendor social links settings.'),
        ])
        ->setField([
            'id' => 'product_page_vendor_info_show_address',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Show vendor address?'),
            'attributes' => [
                'name' => 'product_page_vendor_info_show_address',
                'list' => [
                    'yes' => trans('core/base::base.yes'),
                    'no' => trans('core/base::base.no'),
                ],
                'value' => 'no',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->setField([
            'id' => 'product_page_vendor_info_position',
            'section_id' => 'opt-text-subsection-marketplace',
            'type' => 'customSelect',
            'label' => __('Vendor info box position'),
            'attributes' => [
                'name' => 'product_page_vendor_info_position',
                'list' => [
                    'before' => __('Before other widgets'),
                    'after' => __('After other widgets'),
                ],
                'value' => 'before',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
            'helper' => __('Choose whether to display the vendor info box before or after other sidebar widgets.'),
        ])
        ->setField([
            'id' => 'payment_methods_image',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'mediaImage',
            'label' => __('Accepted Payment methods'),
            'attributes' => [
                'name' => 'payment_methods_image',
                'value' => null,
                'attributes' => [
                    'allow_thumb' => false,
                ],
            ],
        ])
        ->setField([
            'id' => 'payment_methods_link',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'text',
            'label' => __('Accepted Payment methods link (optional)'),
            'attributes' => [
                'name' => 'payment_methods_link',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://...',
                    'data-counter' => 255,
                ],
            ],
        ])
        ->setField([
            'id' => 'enabled_product_categories_on_header',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'label' => __('Enable shop by categories on header?'),
            'attributes' => [
                'name' => 'enabled_product_categories_on_header',
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
            'id' => 'hidden_product_categories_in_dropdown',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'text',
            'label' => __('Hidden product categories in dropdown menu'),
            'attributes' => [
                'name' => 'hidden_product_categories_in_dropdown',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('E.g., 1,2,3'),
                ],
            ],
            'helper' => __('Enter product category IDs to hide from the header dropdown menu, separated by commas (e.g., 1,2,3). After saving, go to Platform Administration → Cache Management to clear cache for changes to take effect.'),
        ])
        ->setField([
            'id' => 'blog_show_author_name',
            'section_id' => 'opt-text-subsection-blog',
            'type' => 'customSelect',
            'label' => __('Show author name?'),
            'attributes' => [
                'name' => 'blog_show_author_name',
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
});
