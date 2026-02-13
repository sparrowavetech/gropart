<?php

return [
    'menu' => 'Bundles',

    'yes' => 'Yes',
    'no' => 'No',

    'metabox' => [
        'title' => 'Bundle settings',
        'is_bundle' => 'This product is a bundle (combo)',
    ],

    'list' => [
        'title' => 'Product Bundles',
        'create' => 'Create bundle',
        'empty' => 'No bundles yet.',
        'columns' => [
            'id' => '#',
            'name' => 'Name',
            'type' => 'Type',
            'pricing' => 'Pricing',
            'active' => 'Active',
            'actions' => 'Actions',
        ],
        'confirm_delete' => 'Delete this bundle?',
        'buttons' => [
            'edit' => 'Edit',
            'delete' => 'Delete',
        ],
    ],

    'form' => [
        'create_title' => 'Create bundle',
        'edit_title' => 'Edit bundle',
        'fields' => [
            'name' => 'Bundle name',
            'slug' => 'Slug',
            'description' => 'Description',
            'image' => 'Image',
            'type' => 'Type',
            'pricing_rule' => 'Pricing rule',
            'pricing_value' => 'Pricing value',
            'start_date' => 'Start date',
            'end_date' => 'End date',
            'active' => 'Active',
            'featured' => 'Featured',
            'attach_products' => 'Attach to products',
            'attach_help' => 'These are the product detail pages where this bundle should appear.',

            'slug_help' => 'Leave blank to auto-generate from name. Used for the bundle detail page URL.',
            'image_help' => 'Accepts a media path or a full URL. Used when displaying bundles as cards.',

            'group_name' => 'Group name',
            'min' => 'Min',
            'max' => 'Max',
        ],
        'types' => [
            'fixed' => 'Fixed bundle',
            'mix' => 'Mix & match',
        ],
        'pricing_types' => [
            'fixed_total' => 'Fixed total price',
            'percent_off' => 'Percent off',
            'amount_off' => 'Amount off',
        ],
        'sections' => [
            'fixed_items' => 'Fixed items',
            'mix_groups' => 'Mix & match groups',
            'group' => 'Group',
            'items' => 'Items',
            'tips' => 'Tips',
        ],
        'table' => [
            'product' => 'Product',
            'variation_id' => 'Variation ID',
            'qty' => 'Qty',
        ],
        'actions' => [
            'save' => 'Save',
            'back' => 'Back',
            'add_item' => 'Add item',
            'remove' => 'Remove',
            'add_group' => 'Add group',
            'remove_group' => 'Remove group',
        ],
        'placeholders' => [
            'search_product' => 'Search product...',
            'select_products' => 'Select products...',
            'slug' => 'auto-generated',
            'image' => 'e.g. /storage/bundles/combo.jpg',
        ],
        'labels' => [
            'variation' => 'Variant',
        ],
        'empties' => [
            'no_fixed_items' => 'No fixed items yet.',
            'no_groups' => 'No groups yet.',
            'no_group_items' => 'No items yet.',
        ],
        'price_summary' => [
            'title' => 'Price details',
            'base_total' => 'Base total',
            'discount' => 'Discount',
            'final_total' => 'Final price',
            'estimate_note' => 'Estimated from the minimum selection in each group.',
            'empty' => 'Add products to preview the price.',
            'loading' => 'Calculating...',
        ],
        'tips_list' => [
            'fixed_total' => 'Use fixed_total when you want the combo to have an exact total price.',
            'percent_off' => 'Use percent_off for "Save 10% when buying together".',
            'attach' => 'Attach to products to show bundles on those product pages (via the provided include).',
        ],
    ],

    'shortcode' => [
        'name' => 'Product Bundles',
        'description' => 'Render product bundles UI for a product ID.',
        'missing_product' => 'Missing product_id for shortcode.',
    ],

    'shortcode_groups' => [
        'name' => 'Bundle Groups',
        'description' => 'Display bundle cards (like products) with image and link to bundle detail.',
        'title' => 'Title',
        'subtitle' => 'Subtitle',
        'limit' => 'Limit',
        'layout' => 'Layout',
        'type' => 'Type filter',
        'featured_only' => 'Featured only',
        'layouts' => [
            'grid' => 'Grid',
            'tabs' => 'Tabs (coming soon)',
            'columns' => 'Columns',
        ],
        'types' => [
            'all' => 'All',
            'fixed' => 'Fixed bundles',
            'mix' => 'Mix & match',
        ],
    ],

    'messages' => [
        'saved' => 'Bundle saved successfully.',
        'deleted' => 'Bundle deleted successfully.',
    ],

    'saved' => 'Bundle saved successfully.',
    'deleted' => 'Bundle deleted successfully.',

    'front' => [
        'title' => 'Combos',
        'contains_title' => 'This combo includes the following products',
        'view_combo' => 'View combo',
        'add_combo' => 'Add combo',
        'choose_between' => '(choose :min-:max)',
        'group_between' => 'Selection for a group must be between :min and :max.',
        'bundle_not_available' => 'This bundle is not available.',
        'group_requires_between' => 'Group ":name" requires between :min and :max selections.',
        'invalid_selection' => 'Invalid selection.',
        'nothing_to_add' => 'Nothing to add.',
        'bundle_added' => 'Bundle added to cart.',
        'request_failed' => 'Request failed.',
        'failed_to_add' => 'Failed to add.',
        'price_from' => 'From',
        'from_price' => 'From :price',
        'bundle_price' => 'Price',
        'featured' => 'Featured',
        'view_details_for_price' => 'View details',
    ],

    'admin_js' => [
        'product_loading' => 'Loading...',
    ],
];
