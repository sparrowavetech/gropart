<?php

namespace SparroWave\RazorpayEasyEmi\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static EmiSchemeEnum STANDARD()
 * @method static EmiSchemeEnum NO_COST()
 * @method static EmiSchemeEnum LOW_COST()
 */
class EmiSchemeEnum extends Enum
{
    public const STANDARD = 'standard';
    public const NO_COST = 'no_cost';
    public const LOW_COST = 'low_cost';

    public static $langPath = 'plugins/razorpay-easy-emi::razorpay-easy-emi.schemes';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::NO_COST => Html::tag('span', self::NO_COST()->label(), ['class' => 'badge bg-success text-success-fg']),
            self::LOW_COST => Html::tag('span', self::LOW_COST()->label(), ['class' => 'badge bg-info text-info-fg']),
            self::STANDARD => Html::tag('span', self::STANDARD()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            default => parent::toHtml(),
        };
    }
}

