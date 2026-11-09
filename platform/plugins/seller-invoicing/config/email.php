<?php

return [
    'name' => 'plugins/seller-invoicing::seller-invoicing.email.title',
    'description' => 'plugins/seller-invoicing::seller-invoicing.email.description',
    'templates' => [
        'monthly_gst_report' => [
            'title' => 'plugins/seller-invoicing::seller-invoicing.email.monthly_gst_report_title',
            'description' => 'plugins/seller-invoicing::seller-invoicing.email.monthly_gst_report_description',
            'subject' => 'plugins/seller-invoicing::seller-invoicing.email.monthly_gst_report_subject',
            'can_off' => true,
            'enabled' => true,
            'variables' => [
                'seller_name' => 'plugins/seller-invoicing::seller-invoicing.email.seller_name',
                'store_name' => 'plugins/seller-invoicing::seller-invoicing.email.store_name',
                'seller_gstin' => 'plugins/seller-invoicing::seller-invoicing.email.seller_gstin',
                'tax_period' => 'plugins/seller-invoicing::seller-invoicing.email.tax_period',
                'total_taxable_sales' => 'plugins/seller-invoicing::seller-invoicing.email.total_taxable_sales',
                'total_output_tax' => 'plugins/seller-invoicing::seller-invoicing.email.total_output_tax',
                'itc_gropart_fees' => 'plugins/seller-invoicing::seller-invoicing.email.itc_marketplace_fees',
                'itc_marketplace_fees' => 'plugins/seller-invoicing::seller-invoicing.email.itc_marketplace_fees',
                'net_gst_payable' => 'plugins/seller-invoicing::seller-invoicing.email.net_gst_payable',
                'report_url' => 'plugins/seller-invoicing::seller-invoicing.email.report_url',
            ],
        ],
        'admin_monthly_gst_summary' => [
            'title' => 'plugins/seller-invoicing::seller-invoicing.email.admin_gst_report_title',
            'description' => 'plugins/seller-invoicing::seller-invoicing.email.admin_gst_report_description',
            'subject' => 'plugins/seller-invoicing::seller-invoicing.email.admin_gst_report_subject',
            'can_off' => true,
            'enabled' => true,
            'variables' => [
                'admin_name' => 'plugins/seller-invoicing::seller-invoicing.email.admin_name',
                'tax_period' => 'plugins/seller-invoicing::seller-invoicing.email.tax_period',
                'stores_count' => 'plugins/seller-invoicing::seller-invoicing.email.stores_count',
                'total_taxable_sales' => 'plugins/seller-invoicing::seller-invoicing.email.total_taxable_sales',
                'total_output_tax' => 'plugins/seller-invoicing::seller-invoicing.email.total_output_tax',
                'total_marketplace_itc' => 'plugins/seller-invoicing::seller-invoicing.email.total_marketplace_itc',
                'report_url' => 'plugins/seller-invoicing::seller-invoicing.email.report_url',
            ],
        ],
    ],
];

