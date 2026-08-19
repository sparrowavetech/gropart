<?php

return [
    'name' => 'Indian GST System',
    'settings' => [
        'title' => 'Indian GST System Settings',
        'description' => 'Configure Indian GST compliance, intra-state/inter-state rules, HSN codes, and invoice formatting.',
        'enable_gst' => 'Enable Indian GST Calculation',
        'enable_gst_helper' => 'Automatically split taxes into CGST+SGST (Intra-state) and IGST (Inter-state) on orders and invoices.',
        'inclusive_price' => 'Display Inclusive Tax on Products',
        'inclusive_price_helper' => 'Product prices will be treated as Inclusive of Tax with reverse GST breakdown.',
        'company_state' => 'Company / Default Seller State',
        'company_gstin' => 'Company GSTIN',
    ],
];
