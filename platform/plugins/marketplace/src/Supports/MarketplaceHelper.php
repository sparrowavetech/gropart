<?php

namespace Botble\Marketplace\Supports;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as BaseEmailHandler;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order as OrderModel;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Marketplace\Models\Store;
use Botble\Theme\Facades\Theme;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Botble\Ecommerce\Models\Enquiry;
use Botble\Sms\Supports\SmsHandler;

class MarketplaceHelper
{
    public function view(string $view, array $data = [])
    {
        return view($this->viewPath($view), $data);
    }

    public function viewPath(string $view, bool $checkViewExists = true): string
    {
        if ($checkViewExists && view()->exists($themeView = Theme::getThemeNamespace('views.marketplace.' . $view))) {
            return $themeView;
        }

        return 'plugins/marketplace::themes.' . $view;
    }

    public function getSetting(string $key, string|int|array|null|bool $default = ''): string|int|array|null|bool
    {
        return setting($this->getSettingKey($key), $default);
    }

    public function getSettingKey(string $key = ''): string
    {
        return config('plugins.marketplace.general.prefix') . $key;
    }

    public function discountTypes(): array
    {
        return Arr::except(DiscountTypeOptionEnum::labels(), [DiscountTypeOptionEnum::SAME_PRICE]);
    }

    public function getAssetVersion(): string
    {
        return '1.2.2';
    }

    public function hideStorePhoneNumber(): bool
    {
        return (bool) $this->getSetting('hide_store_phone_number', false);
    }

    public function hideStoreEmail(): bool
    {
        return (bool) $this->getSetting('hide_store_email', false);
    }

    public function hideStoreSocialLinks(): bool
    {
        return (bool) $this->getSetting('hide_store_social_links', false);
    }

    public function hideStoreAddress(): bool
    {
        return (bool) $this->getSetting('hide_store_address', false);
    }

    public function allowVendorManageShipping(): bool
    {
        return (bool) $this->getSetting('allow_vendor_manage_shipping', false);
    }

    public function isVendorProfileComplete(int $customerID)
    {
        $isVendorData = Store::where('customer_id', $customerID)->first();
        $isVendorTaxData = VendorInfo::where('customer_id', $customerID)->value('tax_info');

        $data['status'] = 0;
        $data['completePercentage'] = "10";
        $data['storeVerified'] = $isVendorData['is_verified'];

        $percentageIncrease = 5;

        if ($isVendorTaxData) {
            $taxVendorSignature     = isset($isVendorTaxData['signature_image']) ? $isVendorTaxData['signature_image'] : '';
            $taxVendorBusinessName  = isset($isVendorTaxData['business_name']) ? $isVendorTaxData['business_name'] : '';
            $taxVendorAddress       = isset($isVendorTaxData['address']) ? $isVendorTaxData['address'] : '';
            $taxVendorTaxNumber     = isset($isVendorTaxData['tax_id']) ? $isVendorTaxData['tax_id'] : '';

            if ($taxVendorSignature && $taxVendorBusinessName && $taxVendorAddress && $taxVendorTaxNumber) {
                $data['completePercentage'] = 80;
                $data['status'] = 1;
            } else {
                // Increase percentage for each available tax data field
                if ($taxVendorSignature) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorBusinessName) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorAddress) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorTaxNumber) {
                    $data['completePercentage'] += $percentageIncrease;
                }
            }
        }

        $vendorDataFields = [
            'email',
            'company',
            'address',
            'state',
            'city',
            'zip_code',
            'logo'
        ];

        foreach ($vendorDataFields as $field) {
            if ($isVendorData[$field]) {
                $data['completePercentage'] += $percentageIncrease;
            }
        }

        $data['completePercentage'] = min($data['completePercentage'], 80);

        return $data;
    }

    public function sendMailToVendorAfterProcessingOrder($orders)
    {
        if ($orders instanceof Collection) {
            $orders->loadMissing(['store']);
        } else {
            $orders = [$orders];
        }

        $mailer = EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME);

        if ($mailer->templateEnabled('store_new_order')) {
            foreach ($orders as $order) {
                if (! $order->store || ! $order->store->email) {
                    continue;
                }

                $this->setEmailVendorVariables($order);
                $mailer->sendUsingTemplate('store_new_order', $order->store->email);
            }
        }

        return $orders;
    }

    public function setEmailVendorVariables(OrderModel $order): BaseEmailHandler
    {
        return EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME)
            ->setVariableValues(OrderHelper::getEmailVariables($order));
    }

    public function isCommissionCategoryFeeBasedEnabled(): bool
    {
        return (bool) $this->getSetting('enable_commission_fee_for_each_category');
    }

    public function maxFilesizeUploadByVendor(): float
    {
        $size = $this->getSetting('max_filesize_upload_by_vendor');

        if (! $size) {
            $size = setting('max_upload_filesize') ?: 10;
        }

        return $size;
    }

    public function maxProductImagesUploadByVendor(): int
    {
        return (int) $this->getSetting('max_product_images_upload_by_vendor', 20);
    }

    public function isVendorRegistrationEnabled(): bool
    {
        return (bool) $this->getSetting('enabled_vendor_registration', true);
    }

    public function getMinimumWithdrawalAmount(): float
    {
        return (float) $this->getSetting('minimum_withdrawal_amount') ?: 0;
    }

    public function allowVendorDeleteTheirOrders(): bool
    {
        return (bool) $this->getSetting('allow_vendor_delete_their_orders', true);
    }

    public function isEnabledMessagingSystem(): bool
    {
        return (bool) $this->getSetting('enabled_messaging_system', true);
    }
    /**
     * @param Collection $orders
     * @return Collection
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public static function sendEnquiryMail($enquiry)
    {
        $mailer = EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME);

        if ($mailer->templateEnabled('store_new_enquiry')) {
            if ($enquiry->product->store->email) {
                self::setEmailVendorVariablesForEnquiry($enquiry);
                $mailer->sendUsingTemplate('store_new_enquiry', $enquiry->product->store->email);
            }
        }
        return $enquiry;
    }

    /**
     * @param EnquiryrModel $order
     * @return \Botble\Base\Supports\EmailHandler
     * @throws Throwable
     */
    public static function setEmailVendorVariablesForEnquiry(Enquiry $enquiry): \Botble\Base\Supports\EmailHandler
    {
        return EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'enquiry_id'    => $enquiry->code,
                'customer_name'    => $enquiry->name,
                'customer_email'   => $enquiry->email,
                'customer_phone'   => $enquiry->phone,
                'customer_address' => $enquiry->address . ', ' . $enquiry->cityName->name . ', ' . $enquiry->stateName->name . ', ' . $enquiry->zip_code,
                'product_list'     => view('plugins/ecommerce::emails.partials.enquiry-detail', compact('enquiry'))
                    ->render(),
                'enquiry_description'      => $enquiry->description,
                'store_name'       => $enquiry->product->store->name,
            ]);
    }
    /**
     * @param EnquiryrModel $order
     * @return \Botble\Base\Supports\EmailHandler
     * @throws Throwable
     */
    public static function setSmsVendorVariablesForEnquiry(Enquiry $enquiry, SmsHandler $sms)
    {
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'customer_name'    => $enquiry->name,
                'customer_email'   => $enquiry->email,
                'customer_phone'   => $enquiry->phone,
                'customer_address' => $enquiry->address . ', ' . $enquiry->cityName->name . ', ' . $enquiry->stateName->name . ', ' . $enquiry->zip_code,
                'enquiry_id'    => $enquiry->code,
                'enquiry_description'  => $enquiry->description,
                'store_name'       => $enquiry->product->store->name,
            ]);

        return $sms;
    }

    /**
     * @param Order $order
     *
     * @return SmsHandler
     * @throws Throwable
     */
    public function setSmsVariables(OrderModel $order, SmsHandler $sms)
    {
        $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'customer_name' => $order->user->name ?: $order->address->name,
                'customer_email' => $order->user->email ?: $order->address->email,
                'customer_phone' => $order->user->phone ?: $order->address->phone,
                'customer_address' => $order->full_address,
                'shipping_method' => $order->shipping_method_name,
                'payment_method' => $order->payment->payment_channel->label(),
                'store_name' => $order->store->name,
            ]);
    }
}
