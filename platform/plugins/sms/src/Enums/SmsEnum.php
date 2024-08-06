<?php

namespace Botble\Sms\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static SmsEnum WELCOME()
 * @method static SmsEnum ORDER_CONFIRMATION()
 * @method static SmsEnum ORDER_CANCELLATION()
 * @method static SmsEnum DELIVERING_CONFIRMATION()
 * @method static SmsEnum ADMIN_ORDER_CONFIRMATION()
 * @method static SmsEnum PAYMENT_CONFIRMATION()
 * @method static SmsEnum INCOMPLETE_ORDER()
 * @method static SmsEnum ORDER_RETURN_REQUEST()
 * @method static SmsEnum INVOICE_PAYMENT_DETAIL()
 * @method static SmsEnum ENQUIRY_CONFIRMATION()
 * @method static SmsEnum EMAIL_SEND_TO_USER()
 * @method static SmsEnum VENDOR_NEW_ORDER()
 * @method static SmsEnum VENDOR_ACCOUNT_APPROVED()
 * @method static SmsEnum PRODUCT_APPROVED()
 * @method static SmsEnum WITHDRAWAL_APPROVED()
 * @method static SmsEnum OTP()
 */
class SmsEnum extends Enum
{
    public const WELCOME = 'welcome';
    public const ORDER_CONFIRMATION = 'order_confirmation';
    public const ORDER_CANCELLATION = 'order_cancellation';
    public const DELIVERING_CONFIRMATION = 'delivering_confirmation';
    public const OTP = 'otp';
    public const VENDOR_NEW_ORDER = 'vendor_new_order';
    // public const ADMIN_ORDER_CONFIRMATION = 'admin_order_confirmation';
    // public const PAYMENT_CONFIRMATION = 'payment_confirmation';
    // public const INCOMPLETE_ORDER = 'incomplete_order';
    // public const ORDER_RETURN_REQUEST = 'order_return_request';
    // public const INVOICE_PAYMENT_DETAIL = 'invoice_payment_detail';
    // public const ENQUIRY_CONFIRMATION = 'enquiry_confirmation';
    // public const EMAIL_SEND_TO_USER = 'email_send_to_user';
    // public const VENDOR_ACCOUNT_APPROVED = 'vendor_account_approved';
    // public const PRODUCT_APPROVED = 'product_approved';
    // public const WITHDRAWAL_APPROVED = 'withdrawal_approved';


    public static $langPath = 'plugins/sms::sms.actions';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::WELCOME => 'welcome',
            self::ORDER_CONFIRMATION => 'order_confirmation',
            self::ORDER_CANCELLATION => 'order_cancellation',
            self::DELIVERING_CONFIRMATION => 'delivering_confirmation',
            self::OTP => 'otp',
            // self::VENDOR_NEW_ORDER => 'vendor_new_order',
            // self::ADMIN_ORDER_CONFIRMATION => 'admin_order_confirmation',
            // self::PAYMENT_CONFIRMATION => 'payment_confirmation',
            // self::INCOMPLETE_ORDER => 'incomplete_order',
            // self::ORDER_RETURN_REQUEST => 'order_return_request',
            // self::INVOICE_PAYMENT_DETAIL => 'invoice_payment_detail',
            // self::ENQUIRY_CONFIRMATION => 'enquiry_confirmation',
            // self::EMAIL_SEND_TO_USER => 'email_send_to_user',
            // self::VENDOR_ACCOUNT_APPROVED => 'vendor_account_approved',
            // self::PRODUCT_APPROVED => 'product_approved',
            // self::WITHDRAWAL_APPROVED => 'withdrawal_approved',
            default => 'primary',
        };

        return $this->label();
    }
}

