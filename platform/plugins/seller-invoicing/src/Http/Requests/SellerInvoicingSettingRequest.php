<?php

namespace SparroWave\SellerInvoicing\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SellerInvoicingSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'seller_invoicing_enabled' => ['nullable', 'boolean'],
            'seller_invoicing_prefix' => ['nullable', 'string', 'max:20'],
            'seller_invoice_code_suffix' => ['nullable', 'string', 'max:20'],
            'seller_invoicing_tax_mode' => ['nullable', 'string', 'in:auto,dual_gst,flat_tax,exempt'],
            'seller_invoicing_fee_tax_type' => ['nullable', 'string', 'in:exclusive,inclusive'],
            'seller_invoicing_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'seller_invoicing_platform_fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'seller_invoicing_tax_label' => ['nullable', 'string', 'max:50'],
            'seller_invoicing_tax_id_label' => ['nullable', 'string', 'max:50'],
            'seller_invoicing_sac_commission' => ['nullable', 'string', 'max:30'],
            'seller_invoicing_sac_shipping' => ['nullable', 'string', 'max:30'],
            'seller_invoicing_signatory_name' => ['nullable', 'string', 'max:255'],
            'seller_invoicing_signature_image' => ['nullable', 'string', 'max:255'],
            'regenerate_existing_invoices' => ['nullable', 'boolean'],
        ];
    }
}

