<?php

namespace Botble\Marketplace\Supports;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as BaseEmailHandler;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order as OrderModel;
use Botble\Ecommerce\Models\Enquiry;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Botble\Theme\Facades\Theme;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

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
        return (bool)$this->getSetting('hide_store_phone_number', false);
    }

    public function hideStoreEmail(): bool
    {
        return (bool)$this->getSetting('hide_store_email', false);
    }

    public function hideStoreSocialLinks(): bool
    {
        return (bool)$this->getSetting('hide_store_social_links', false);
    }

    public function allowVendorManageShipping(): bool
    {
        return (bool)$this->getSetting('allow_vendor_manage_shipping', false);
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
        return (bool)$this->getSetting('enable_commission_fee_for_each_category');
    }

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
    public static function setEmailVendorVariablesForEnquiry(Enquiry $enquiry): \Botble\Base\Supports\EmailHandler
    {
        return EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'enquiry_id'    => $enquiry->code,
                'customer_name'    => $enquiry->name,
                'customer_email'   => $enquiry->email,
                'customer_phone'   => $enquiry->phone,
                'customer_address' => $enquiry->address.', '.$enquiry->cityName->name.', '.$enquiry->stateName->name.', '.$enquiry->zip_code,
                'product_list'     => view('plugins/ecommerce::emails.partials.enquiry-detail', compact('enquiry'))
                    ->render(),
                'enquiry_description'      => $enquiry->description,
                'store_name'       => $enquiry->product->store->name,
            ]);
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
        return (int)$this->getSetting('max_product_images_upload_by_vendor', 20);
    }
}
