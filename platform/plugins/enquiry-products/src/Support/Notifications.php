<?php
namespace SparroWave\EnquiryProducts\Support;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use SparroWave\EnquiryProducts\Models\Enquiry;

class Notifications
{
    public function send(Enquiry $enquiry): void
    {
        $enquiry->load('product');
        $emails = array_merge(get_admin_email()->all(), [$enquiry->email]);
        if (is_plugin_active('marketplace') && $enquiry->store_id) {
            $store = \Botble\Marketplace\Models\Store::with('customer')->find($enquiry->store_id);
            $emails[] = filter_var($store?->email, FILTER_VALIDATE_EMAIL) ? $store->email : $store?->customer?->email;
        }
        foreach (array_unique(array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))) as $email) {
            try {
                EmailHandler::setModule('enquiry-products')->setVariableValues([
                    'enquiry_code' => e($enquiry->code), 'product_name' => e($enquiry->product?->name ?? ''),
                    'customer_name' => e($enquiry->name), 'customer_email' => e($enquiry->email), 'customer_phone' => e($enquiry->phone),
                ])->sendUsingTemplate('received', $email);
            } catch (\Throwable $exception) {
                BaseHelper::logError($exception);
            }
        }
    }
}
