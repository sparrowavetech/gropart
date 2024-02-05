<?php

namespace Botble\Marketplace\Facades;

use Botble\Marketplace\Supports\SellerInvoiceHelper as BaseSellerInvoiceHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Barryvdh\DomPDF\PDF|\Dompdf\Dompdf makeInvoicePDF(\Botble\Marketplace\Models\Revenue $revenue)
 * @method static string generateInvoice(\Botble\Marketplace\Models\Revenue $revenue)
 * @method static \Illuminate\Http\Response downloadInvoice(\Botble\Marketplace\Models\Revenue $revenue)
 * @method static \Illuminate\Http\Response streamInvoice(\Botble\Ecommerce\Models\Revenue $revenue)
 * @method static string|null getCompanyCountry()
 * @method static string|null getCompanyState()
 * @method static string|null getCompanyCity()
 * @method static string|null getCompanyZipCode()
 *
 * @see \Botble\Marketplace\Supports\SellerInvoiceHelper
 */
class SellerInvoiceHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseSellerInvoiceHelper::class;
    }
}
