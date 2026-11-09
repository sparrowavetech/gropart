<?php

namespace SparroWave\SellerInvoicing\Services;

use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Supports\TwigExtension;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Supports\LocationResolver;

class GenerateSellerInvoiceService
{
    public function __construct(protected ?Document $document = null)
    {
    }

    public function document(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function stream(?string $fileName = null): Response
    {
        $targetFile = $this->fileName($fileName);
        $savedPath = $this->ensureSaved();

        return response()->file($savedPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $targetFile),
        ]);
    }

    public function download(?string $fileName = null): Response
    {
        $targetFile = $this->fileName($fileName);
        $savedPath = $this->ensureSaved();

        return response()->download($savedPath, $targetFile, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function ensureSaved(): string
    {
        $safeNumber = str_replace(['/', '\\'], '-', $this->document?->number ?: 'temp');
        $relativePath = $this->document?->pdf_path ?: sprintf('marketplace/seller-invoices/seller-invoice-%s.pdf', $safeNumber);
        $fullPath = storage_path('app/' . $relativePath);

        if (File::exists($fullPath) && filesize($fullPath) > 0) {
            return $fullPath;
        }

        File::ensureDirectoryExists(dirname($fullPath));

        $pdf = $this->generatePdf();
        $pdf->save($fullPath);

        if ($this->document && $this->document->exists) {
            $this->document->update([
                'pdf_path' => $relativePath,
                'sha256' => hash_file('sha256', $fullPath),
            ]);
        }

        return $fullPath;
    }

    protected function fileName(?string $fileName = null): string
    {
        return $fileName ?: sprintf('seller-invoice-%s.pdf', $this->document?->number ?: 'document');
    }

    protected function generatePdf(): Pdf
    {
        return (new Pdf())
            ->templatePath($this->getTemplatePath())
            ->destinationPath($this->getCustomizedTemplatePath())
            ->supportLanguage(InvoiceHelper::getLanguageSupport())
            ->paperSizeA4()
            ->data($this->getInvoiceData())
            ->twigExtensions([new TwigExtension()])
            ->setProcessingLibrary(get_ecommerce_setting('invoice_processing_library', 'dompdf'));
    }

    protected function getInvoiceData(): array
    {
        $snapshot = $this->document?->snapshot;
        $order = $this->document?->order ?: $snapshot?->order;
        $store = $order?->store;
        $customer = $store?->customer;

        $company = $snapshot?->issuer ?: $this->fallbackCompanyData();
        $recipient = $snapshot?->recipient ?: ($store ? [
            'name' => $store->name,
            'address' => $store->address,
            'country' => $store->tax_country ?: $store->country,
            'state' => $store->tax_state ?: $store->state,
            'tax_id' => $store->tax_id,
        ] : []);

        $signatureImage = setting('marketplace_authorised_signature_image') ?: (setting('authorised_signature_image') ?: setting('seller_invoicing_signature_image'));
        $signatureRealPath = ($signatureImage && File::exists(RvMedia::getRealPath($signatureImage))) ? RvMedia::getRealPath($signatureImage) : null;
        $signatureUrl = $signatureRealPath ?: ($signatureImage ? RvMedia::getImageUrl($signatureImage) : null);
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option('logo_in_invoices') ?: Theme::getLogo());
        $logoRealPath = ($logo && File::exists(RvMedia::getRealPath($logo))) ? RvMedia::getRealPath($logo) : null;

        $companyState = LocationResolver::resolveState($company['state'] ?? get_ecommerce_setting('company_state_for_invoicing', get_ecommerce_setting('store_state')));
        $companyCountry = LocationResolver::resolveCountry($company['country'] ?? get_ecommerce_setting('company_country_for_invoicing', get_ecommerce_setting('store_country')));
        $companyCity = LocationResolver::resolveCity($company['city'] ?? get_ecommerce_setting('company_city_for_invoicing', get_ecommerce_setting('store_city')));

        $sellerState = LocationResolver::resolveState($recipient['state'] ?? ($store?->state ?: ''));
        $sellerCountry = LocationResolver::resolveCountry($recipient['country'] ?? ($store?->country ?: ''));
        $sellerCity = LocationResolver::resolveCity($recipient['city'] ?? ($store?->city_name ?: $store?->city));
        $placeOfSupply = LocationResolver::resolveState($this->document?->taxes['place_of_supply'] ?? ($sellerState ?: $companyState));

        $orderCode = ltrim($order?->code ?: ($this->document?->order_id ?: ''), '#');

        return [
            'site_title' => theme_option('site_title') ?: get_ecommerce_setting('store_name', 'Marketplace'),
            'company' => [
                'logo' => $logoRealPath ?: ($logo ? RvMedia::getImageUrl($logo) : null),
                'name' => $company['name'] ?? get_ecommerce_setting('company_name_for_invoicing', get_ecommerce_setting('store_name')),
                'address' => $company['address'] ?? get_ecommerce_setting('company_address_for_invoicing', get_ecommerce_setting('store_address')),
                'city' => $companyCity,
                'state' => $companyState,
                'country' => $companyCountry,
                'tax_id' => $company['tax_id'] ?? get_ecommerce_setting('company_tax_id_for_invoicing', get_ecommerce_setting('store_vat_number')),
                'phone' => get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone'),
                'email' => get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email'),
            ],
            'seller' => [
                'name' => $recipient['name'] ?? ($store?->name ?: 'Vendor'),
                'owner_name' => $customer?->name ?: '',
                'address' => $recipient['address'] ?? ($store?->address ?: ''),
                'city' => $sellerCity,
                'state' => $sellerState,
                'country' => $sellerCountry,
                'tax_id' => $recipient['tax_id'] ?? ($store?->tax_id ?: ''),
                'phone' => $customer?->phone ?: ($store?->phone ?: ''),
                'email' => $customer?->email ?: ($store?->email ?: ''),
                'place_of_supply' => $placeOfSupply,
            ],
            'invoice' => [
                'number' => $this->document?->number,
                'order_id' => $orderCode,
                'created_at' => ($this->document?->issued_at ?: now())->translatedFormat('F d, Y'),
                'order_date' => $order?->created_at ? $order->created_at->translatedFormat('F d, Y') : null,
                'currency' => $this->document?->currency ?: 'INR',
                'symbol' => ($this->document?->currency === 'INR' || ! $this->document?->currency) ? '₹' : ($this->document->currency),
                'subtotal' => $this->document?->subtotal ?: 0,
                'tax_amount' => $this->document?->tax_amount ?: 0,
                'grand_total' => $this->document?->grand_total ?: 0,
                'status' => $this->document?->status ?: 'issued',
            ],
            'lines' => $this->document?->lines ?: [],
            'taxes' => $this->document?->taxes ?: [],
            'authorised_signatory' => [
                'name' => setting('seller_invoicing_signatory_name', get_ecommerce_setting('store_name', 'Authorised Signatory')),
                'image' => $signatureRealPath ?: $signatureUrl,
            ],
        ];
    }

    protected function fallbackCompanyData(): array
    {
        $rawCountry = get_ecommerce_setting('company_country_for_invoicing') ?: get_ecommerce_setting('store_country');
        $rawState = get_ecommerce_setting('company_state_for_invoicing') ?: get_ecommerce_setting('store_state');
        $rawCity = get_ecommerce_setting('company_city_for_invoicing') ?: get_ecommerce_setting('store_city');

        return [
            'name' => get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name'),
            'address' => get_ecommerce_setting('company_address_for_invoicing') ?: get_ecommerce_setting('store_address'),
            'city' => LocationResolver::resolveCity($rawCity),
            'country' => LocationResolver::resolveCountry($rawCountry),
            'state' => LocationResolver::resolveState($rawState, $rawCountry),
            'tax_id' => get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting('store_vat_number'),
        ];
    }

    public function preview(): Response
    {
        $previewDocument = new Document([
            'number' => 'SINV-' . date('Y') . '-00001',
            'currency' => 'INR',
            'order_id' => 10814,
            'subtotal' => 100.00,
            'tax_amount' => 18.00,
            'grand_total' => 118.00,
            'status' => 'issued',
            'issued_at' => Carbon::now(),
            'lines' => [
                [
                    'name' => 'Marketplace Commission Fee',
                    'sac_code' => '998311',
                    'taxable_amount' => 80.00,
                    'tax_rate' => 18,
                    'cgst_amount' => 7.20,
                    'sgst_amount' => 7.20,
                    'igst_amount' => 0.00,
                    'tax_amount' => 14.40,
                    'total_amount' => 94.40,
                ],
                [
                    'name' => 'Platform Fixed Fee',
                    'sac_code' => '998311',
                    'taxable_amount' => 20.00,
                    'tax_rate' => 18,
                    'cgst_amount' => 1.80,
                    'sgst_amount' => 1.80,
                    'igst_amount' => 0.00,
                    'tax_amount' => 3.60,
                    'total_amount' => 23.60,
                ],
            ],
            'taxes' => [
                'is_india' => true,
                'is_igst' => false,
                'tax_label' => 'GST',
                'tax_id_label' => 'GSTIN',
                'tax_rate' => 18,
                'rate_label' => 'CGST @ 9% + SGST @ 9%',
                'cgst_amount' => 9.00,
                'sgst_amount' => 9.00,
                'igst_amount' => 0.00,
                'place_of_supply' => 'Rajasthan',
            ],
        ]);

        $this->document = $previewDocument;

        return (new Pdf())
            ->templatePath($this->getTemplatePath())
            ->destinationPath($this->getCustomizedTemplatePath())
            ->supportLanguage(InvoiceHelper::getLanguageSupport())
            ->paperSizeA4()
            ->data($this->getInvoiceData())
            ->twigExtensions([new TwigExtension()])
            ->setProcessingLibrary(get_ecommerce_setting('invoice_processing_library', 'dompdf'))
            ->stream();
    }

    public function getTemplatePath(): string
    {
        return platform_path('plugins/seller-invoicing/resources/templates/seller-invoice.tpl');
    }

    public function getCustomizedTemplatePath(): string
    {
        return storage_path('app/templates/seller-invoice.tpl');
    }

    public function getTemplateContent(): string
    {
        if (File::exists($this->getCustomizedTemplatePath())) {
            return \Botble\Base\Facades\BaseHelper::getFileData($this->getCustomizedTemplatePath(), false);
        }

        return \Botble\Base\Facades\BaseHelper::getFileData($this->getTemplatePath(), false);
    }

    public function getContent(): string
    {
        return $this->getTemplateContent();
    }

    public function getVariables(): array
    {
        return [
            'site_title' => 'Site Title',
            'company.name' => 'Company Name',
            'company.address' => 'Company Address',
            'company.state' => 'Company State',
            'company.country' => 'Company Country',
            'company.tax_id' => 'Company Tax ID',
            'seller.name' => 'Seller Store Name',
            'seller.address' => 'Seller Address',
            'seller.state' => 'Seller State',
            'seller.country' => 'Seller Country',
            'seller.tax_id' => 'Seller Tax ID',
            'seller.place_of_supply' => 'Place of Supply',
            'invoice.number' => 'Invoice Number',
            'invoice.order_id' => 'Order Code/ID',
            'invoice.created_at' => 'Invoice Date',
            'invoice.currency' => 'Currency',
            'invoice.subtotal' => 'Subtotal',
            'invoice.tax_amount' => 'Tax Amount',
            'invoice.grand_total' => 'Grand Total',
            'lines' => 'Line Items List',
            'taxes' => 'Tax Details and Breakdown',
            'authorised_signatory.name' => 'Authorised Signatory Name',
            'authorised_signatory.image' => 'Authorised Signature Image URL',
        ];
    }
}
