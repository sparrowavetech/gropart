<?php

namespace Botble\Marketplace\Supports;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\TwigCompiler;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Location\Models\State;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Media\Facades\RvMedia;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Image\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Throwable;
use Twig\Extension\DebugExtension;

class SellerInvoiceHelper
{
    public function makeInvoicePDF(Revenue $revenue): PDFHelper|Dompdf
    {
        $fontsPath = storage_path('fonts');

        if (! File::isDirectory($fontsPath)) {
            File::makeDirectory($fontsPath);
        }

        $content = $this->getInvoiceTemplate();

        if ($content) {
            $twigCompiler = new TwigCompiler([
                'autoescape' => false,
                'debug' => true,
            ]);

            $twigCompiler
                ->addExtension(new TwigExtension())
                ->addExtension(new DebugExtension());

            $content = $twigCompiler->compile($content, $this->getDataForInvoiceTemplate($revenue));

            if ((int)get_ecommerce_setting('invoice_support_arabic_language', 0) == 1) {
                $arabic = new Arabic();
                $p = $arabic->arIdentify($content);

                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    try {
                        $utf8ar = $arabic->utf8Glyphs(substr($content, $p[$i - 1], $p[$i] - $p[$i - 1]));
                        $content = substr_replace($content, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                    } catch (Throwable) {
                        continue;
                    }
                }
            }
        }

        Cache::$error_message = null;


        return Pdf::setWarnings(false)
            ->setOption('chroot', [public_path(), base_path()])
            ->setOption('tempDir', storage_path('app'))
            ->setOption('logOutputFile', storage_path('logs/pdf.log'))
            ->setOption('isRemoteEnabled', true)
            ->loadHTML($content, 'UTF-8')
            ->setPaper('a4');
    }

    public function generateInvoice(Revenue $revenue): string
    {
        $folderPath = storage_path('app/public');
        if (! File::isDirectory($folderPath)) {
            File::makeDirectory($folderPath);
        }

        $invoicePath = sprintf('%s/seller-invoice-%s.pdf', $folderPath, $revenue->seller_inv_code);

        if (File::exists($invoicePath)) {
            return $invoicePath;
        }

        $this->makeInvoicePDF($revenue)->save($invoicePath);

        dd($invoicePath);

        return $invoicePath;
    }

    public function downloadInvoice(Revenue $revenue): Response
    {
        $pdf = $this->makeInvoicePDF($revenue);

        return response($pdf->output())
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', sprintf('inline; filename="seller-invoice-%s.pdf"', $revenue->seller_inv_code));
    }

    public function streamInvoice(Revenue $revenue): Response
    {
        return $this->makeInvoicePDF($revenue)->stream();
    }

    public function getInvoiceTemplate(): string
    {
        $defaultPath = platform_path('plugins/marketplace/resources/templates/seller-invoice.tpl');
        $storagePath = storage_path('app/templates/seller-invoice.tpl');

        if ($storagePath && File::exists($storagePath)) {
            $templateHtml = BaseHelper::getFileData($storagePath, false);
        } else {
            $templateHtml = File::exists($defaultPath) ? BaseHelper::getFileData($defaultPath, false) : '';
        }

        return (string)$templateHtml;
    }

    protected function getDataForInvoiceTemplate(Revenue $revenue): array
    {
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option(
            'logo_in_invoices'
        ) ?: theme_option('logo'));

        $companyName = get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name');

        $companyAddress = get_ecommerce_setting('company_address_for_invoicing');

        $companyCountryId = EcommerceHelperFacade::getCountryNameById($this->getCompanyCountry());
        $companyStateId = $this->getCompanyState();
        $companyCityId = $this->getCompanyCity();

        $companyCountry = Country::query()->where('id', $companyCountryId)->value('name');
        $companyState = State::query()->where('id', $companyStateId)->value('name');
        $companyCity = City::query()->where('id', $companyCityId)->value('name');
        $companyZipcode = get_ecommerce_setting('company_zipcode_for_invoicing') ?: get_ecommerce_setting('store_zip_code');

        if (! $companyAddress) {
            $companyAddress = implode(', ', array_filter([
                get_ecommerce_setting('company_address_for_invoicing', get_ecommerce_setting('store_address')),
                $companyCity,
                $companyState,
                $companyCountry,
                $companyZipcode,
            ]));
        }

        $companyPhone = get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone');
        $companyEmail = get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email');
        $companyTaxId = get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting('store_vat_number');

        $data = [
            'invoice' => $revenue->toArray(),
            'toState' => $revenue->state ? $revenue->state : null,
            'fromState'=> $companyState,
            'isIgst' => $revenue->state !== $companyStateId ? true : false,
            'logo' => $logo,
            'site_title' => theme_option('site_title'),
            'company_logo_full_path' => RvMedia::getRealPath($logo),
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_country' => $companyCountry,
            'company_state' => $companyState,
            'company_city' => $companyCity,
            'company_zipcode' => $companyZipcode,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_tax_id' => $companyTaxId,
            'is_tax_enabled' => EcommerceHelperFacade::isTaxEnabled(),
            'settings' => [
                'using_custom_font_for_invoice' => (bool)get_ecommerce_setting('using_custom_font_for_invoice'),
                'custom_font_family' => get_ecommerce_setting('invoice_font_family', 'DejaVu Sans'),
                'font_family' => (int)get_ecommerce_setting('using_custom_font_for_invoice', 0) == 1
                    ? get_ecommerce_setting('invoice_font_family', 'DejaVu Sans')
                    : 'DejaVu Sans',
            ],
        ];

        $data['settings']['font_css'] = null;

        if ($data['settings']['using_custom_font_for_invoice'] && $data['settings']['font_family']) {
            $data['settings']['font_css'] = BaseHelper::googleFonts(
                'https://fonts.googleapis.com/css2?family=' .
                urlencode($data['settings']['font_family']) .
                ':wght@400;600;700&display=swap'
            );
        }

        $orderData = $revenue->order;
        $vendorId = $orderData->store_id;
        $vendorData = Store::query()->findOrFail($vendorId);
        $vendorDetails = Customer::query()->findOrFail($vendorData->customer_id);

        $sellertaxData = VendorInfo::query()->where('customer_id', $vendorData->customer_id)->value('tax_info');
        $sellerCountryName = Country::query()->where('id', $vendorData->country)->value('name');
        $sellerStateName = State::query()->where('id', $vendorData->state)->value('name');
        $sellerCityName = City::query()->where('id', $vendorData->city)->value('name');

        if ($orderData && $vendorData) {
            $orderNumber = $orderData->code;

            if (EcommerceHelperFacade::isBillingAddressEnabled() && $vendorData->address) {
                $vendorAddress = $vendorData->address;
                $data['seller_company_address'] = $vendorAddress;
            } else {
                $vendorAddress = implode(', ', array_filter([
                    $vendorData->address,
                    $sellerCityName,
                    $sellerStateName,
                    $sellerCountryName,
                    $vendorData->zip_code,
                ]));
            }
            $data['order_id']                = $orderNumber;
            $data['seller_company_name']     = $vendorData->name;
            $data['seller_owner_name']       = $vendorDetails->name;
            $data['seller_owner_phone']       = $vendorDetails->phone;
            $data['seller_owner_email']       = $vendorDetails->email;
            $data['seller_company_tax_id']   = $sellertaxData['tax_id'];
            $data['seller_country']          = $sellerCountryName;
            $data['seller_state']            = $sellerStateName;
            $data['seller_city']             = $sellerCityName;
            $data['seller_zip_code']         = $vendorData->zip_code;
            $data['authorised_signatory_image'] = url('storage/'.setting('marketplace_authorised_signature_image'));
        }

        return apply_filters('seller_invoice_variables', $data, $revenue);
    }

    public function getCompanyCountry(): string|null
    {
        return get_ecommerce_setting('company_country_for_invoicing', get_ecommerce_setting('store_country'));
    }

    public function getCompanyState(): string|null
    {
        return get_ecommerce_setting('company_state_for_invoicing', get_ecommerce_setting('store_state'));
    }

    public function getCompanyCity(): string|null
    {
        return get_ecommerce_setting('company_city_for_invoicing', get_ecommerce_setting('store_city'));
    }

    public function getCompanyZipCode(): string|null
    {
        return get_ecommerce_setting('company_zipcode_for_invoicing', get_ecommerce_setting('store_zip_code'));
    }
}
