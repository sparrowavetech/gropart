<?php

namespace SparroWave\RazorpayEasyEmi\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static EmiRequestStatusEnum PENDING()
 * @method static EmiRequestStatusEnum APPROVED()
 * @method static EmiRequestStatusEnum REJECTED()
 */
class EmiRequestStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public static $langPath = 'plugins/razorpay-easy-emi::razorpay-easy-emi.request_statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-warning text-dark']),
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'badge bg-success text-success-fg']),
            self::REJECTED => Html::tag('span', self::REJECTED()->label(), ['class' => 'badge bg-danger text-danger-fg']),
            default => parent::toHtml(),
        };
    }
}

