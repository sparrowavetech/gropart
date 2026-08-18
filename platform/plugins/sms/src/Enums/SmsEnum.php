<?php

namespace Botble\Sms\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static SmsEnum WELCOME()
 * @method static SmsEnum REGISTRATION_OTP()
 * @method static SmsEnum LOGIN_OTP()
 * @method static SmsEnum PASSWORD_RESET_OTP()
 * @method static SmsEnum CHECKOUT_OTP()
 * @method static SmsEnum ORDER_CREATED_CUSTOMER()
 * @method static SmsEnum ORDER_CREATED_ADMIN()
 * @method static SmsEnum ORDER_STATUS_CHANGED_CUSTOMER()
 * @method static SmsEnum ORDER_STATUS_PENDING()
 * @method static SmsEnum ORDER_STATUS_PROCESSING()
 * @method static SmsEnum ORDER_STATUS_CONFIRMED()
 * @method static SmsEnum ORDER_STATUS_COMPLETED()
 * @method static SmsEnum ORDER_STATUS_DELIVERED()
 * @method static SmsEnum ORDER_STATUS_CANCELED()
 * @method static SmsEnum ORDER_STATUS_CANCELLED()
 * @method static SmsEnum ORDER_STATUS_RETURNED()
 * @method static SmsEnum ORDER_STATUS_PARTIAL_RETURNED()
 * @method static SmsEnum ORDER_PAYMENT_CONFIRMED_CUSTOMER()
 * @method static SmsEnum SHIPPING_STATUS_CHANGED_CUSTOMER()
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
    public const REGISTRATION_OTP = 'registration_otp';
    public const LOGIN_OTP = 'login_otp';
    public const PASSWORD_RESET_OTP = 'password_reset_otp';
    public const CHECKOUT_OTP = 'checkout_otp';
    public const ORDER_CREATED_CUSTOMER = 'order_created_customer';
    public const ORDER_CREATED_ADMIN = 'order_created_admin';
    public const ORDER_STATUS_CHANGED_CUSTOMER = 'order_status_changed_customer';
    public const ORDER_STATUS_PENDING = 'order_status_pending';
    public const ORDER_STATUS_PROCESSING = 'order_status_processing';
    public const ORDER_STATUS_CONFIRMED = 'order_status_confirmed';
    public const ORDER_STATUS_COMPLETED = 'order_status_completed';
    public const ORDER_STATUS_DELIVERED = 'order_status_delivered';
    public const ORDER_STATUS_CANCELED = 'order_status_canceled';
    public const ORDER_STATUS_CANCELLED = 'order_status_cancelled';
    public const ORDER_STATUS_RETURNED = 'order_status_returned';
    public const ORDER_STATUS_PARTIAL_RETURNED = 'order_status_partial_returned';
    public const ORDER_PAYMENT_CONFIRMED_CUSTOMER = 'order_payment_confirmed_customer';
    public const SHIPPING_STATUS_CHANGED_CUSTOMER = 'shipping_status_changed_customer';
    public const ORDER_CONFIRMATION = 'order_confirmation';
    public const ORDER_CANCELLATION = 'order_cancellation';
    public const DELIVERING_CONFIRMATION = 'delivering_confirmation';
    public const OTP = 'otp';
    public const VENDOR_NEW_ORDER = 'vendor_new_order';
    public const ADMIN_ORDER_CONFIRMATION = 'admin_order_confirmation';
    public const PAYMENT_CONFIRMATION = 'payment_confirmation';
    public const INCOMPLETE_ORDER = 'incomplete_order';
    public const ORDER_RETURN_REQUEST = 'order_return_request';
    public const INVOICE_PAYMENT_DETAIL = 'invoice_payment_detail';
    public const VENDOR_ACCOUNT_APPROVED = 'vendor_account_approved';
    public const PRODUCT_APPROVED = 'product_approved';
    public const WITHDRAWAL_APPROVED = 'withdrawal_approved';


    public static $langPath = 'plugins/sms::sms.actions';

    public static function templateLabels(): array
    {
        return [
            self::WELCOME => static::getLabel(self::WELCOME),
            self::OTP => static::getLabel(self::OTP),
            self::ORDER_CONFIRMATION => static::getLabel(self::ORDER_CONFIRMATION),
            self::ORDER_CANCELLATION => static::getLabel(self::ORDER_CANCELLATION),
            self::DELIVERING_CONFIRMATION => static::getLabel(self::DELIVERING_CONFIRMATION),
            self::ORDER_CREATED_CUSTOMER => static::getLabel(self::ORDER_CREATED_CUSTOMER),
            self::ORDER_CREATED_ADMIN => static::getLabel(self::ORDER_CREATED_ADMIN),
            self::ORDER_STATUS_RETURNED => static::getLabel(self::ORDER_STATUS_RETURNED),
            self::ORDER_PAYMENT_CONFIRMED_CUSTOMER => static::getLabel(self::ORDER_PAYMENT_CONFIRMED_CUSTOMER),
            self::SHIPPING_STATUS_CHANGED_CUSTOMER => static::getLabel(self::SHIPPING_STATUS_CHANGED_CUSTOMER),
            self::VENDOR_NEW_ORDER => static::getLabel(self::VENDOR_NEW_ORDER),
            self::VENDOR_ACCOUNT_APPROVED => static::getLabel(self::VENDOR_ACCOUNT_APPROVED),
            self::PRODUCT_APPROVED => static::getLabel(self::PRODUCT_APPROVED),
            self::WITHDRAWAL_APPROVED => static::getLabel(self::WITHDRAWAL_APPROVED),
        ];
    }

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::WELCOME => 'welcome',
            self::ORDER_CONFIRMATION => 'order_confirmation',
            self::ORDER_CANCELLATION => 'order_cancellation',
            self::DELIVERING_CONFIRMATION => 'delivering_confirmation',
            self::OTP => 'otp',
            default => 'primary',
        };

        return $this->label();
    }
}
