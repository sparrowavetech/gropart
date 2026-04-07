<?php

namespace SparroWave\IndianGst\Hooks;

class IndianGstInvoiceListener
{
    public static function overrideInvoiceTemplate()
    {
        // Logic to swap the invoice template
        // We will use view()->composer() or config override
        config(['plugins.ecommerce.general.invoice_template' => 'plugins/indian-gst::invoices.template']);
    }
}
