<?php

namespace Botble\Sms\Enums;

use Botble\Base\Supports\Enum;
use Html;

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
    public const ADMIN_ORDER_CONFIRMATION = 'admin_order_confirmation';
    public const PAYMENT_CONFIRMATION = 'payment_confirmation';
    public const INCOMPLETE_ORDER = 'incomplete_order';
    public const ORDER_RETURN_REQUEST = 'order_return_request';
    public const INVOICE_PAYMENT_DETAIL = 'invoice_payment_detail';
    public const ENQUIRY_CONFIRMATION = 'enquiry_confirmation';
    public const EMAIL_SEND_TO_USER = 'email_send_to_user';
    public const VENDOR_NEW_ORDER = 'vendor_new_order';
    public const VENDOR_ACCOUNT_APPROVED = 'vendor_account_approved';
    public const PRODUCT_APPROVED = 'product_approved';
    public const WITHDRAWAL_APPROVED = 'withdrawal_approved';
    public const OTP = 'otp';

    /**
     * @var string
     */
    public static $langPath = 'plugins/sms::sms.actions';

    /**
     * @return string
     */
    public function lable()
    {
        switch ($this->value) {
            case self::WELCOME:
                return Html::tag('span', self::WELCOME()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::ORDER_CONFIRMATION:
                return Html::tag('span', self::ORDER_CONFIRMATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::ORDER_CANCELLATION:
                return Html::tag('span', self::ORDER_CANCELLATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::DELIVERING_CONFIRMATION:
                return Html::tag('span', self::DELIVERING_CONFIRMATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::ADMIN_ORDER_CONFIRMATION:
                return Html::tag('span', self::ADMIN_ORDER_CONFIRMATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::PAYMENT_CONFIRMATION:
                return Html::tag('span', self::PAYMENT_CONFIRMATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::INCOMPLETE_ORDER:
                return Html::tag('span', self::INCOMPLETE_ORDER()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::ORDER_RETURN_REQUEST:
                return Html::tag('span', self::ORDER_RETURN_REQUEST()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::INVOICE_PAYMENT_DETAIL:
                return Html::tag('span', self::INVOICE_PAYMENT_DETAIL()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::ENQUIRY_CONFIRMATION:
                return Html::tag('span', self::ENQUIRY_CONFIRMATION()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::EMAIL_SEND_TO_USER:
                return Html::tag('span', self::EMAIL_SEND_TO_USER()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::VENDOR_NEW_ORDER:
                return Html::tag('span', self::VENDOR_NEW_ORDER()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::VENDOR_ACCOUNT_APPROVED:
                return Html::tag('span', self::VENDOR_ACCOUNT_APPROVED()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::PRODUCT_APPROVED:
                return Html::tag('span', self::PRODUCT_APPROVED()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::WITHDRAWAL_APPROVED:
                return Html::tag('span', self::WITHDRAWAL_APPROVED()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::OTP:
                return Html::tag('span', self::OTP()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            default:
                return parent::toHtml();
        }
    }
}
