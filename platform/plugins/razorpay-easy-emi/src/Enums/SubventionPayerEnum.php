<?php

namespace SparroWave\RazorpayEasyEmi\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static SubventionPayerEnum PLATFORM()
 * @method static SubventionPayerEnum SELLER()
 */
class SubventionPayerEnum extends Enum
{
    public const PLATFORM = 'platform';
    public const SELLER = 'seller';

    public static $langPath = 'plugins/razorpay-easy-emi::razorpay-easy-emi.subvention_payers';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PLATFORM => Html::tag('span', self::PLATFORM()->label(), ['class' => 'badge bg-primary text-primary-fg']),
            self::SELLER => Html::tag('span', self::SELLER()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            default => parent::toHtml(),
        };
    }
}

