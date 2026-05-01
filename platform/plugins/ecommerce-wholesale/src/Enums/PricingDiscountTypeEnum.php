<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static PricingDiscountTypeEnum PERCENTAGE()
 * @method static PricingDiscountTypeEnum FIXED()
 * @method static PricingDiscountTypeEnum FIXED_PRICE()
 */
class PricingDiscountTypeEnum extends Enum
{
    public const PERCENTAGE = 'percentage';
    public const FIXED = 'fixed';
    public const FIXED_PRICE = 'fixed_price';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.pricing_discount_type';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PERCENTAGE => Html::tag('span', self::PERCENTAGE()->label(), ['class' => 'badge bg-purple-lt']),
            self::FIXED => Html::tag('span', self::FIXED()->label(), ['class' => 'badge bg-orange-lt']),
            self::FIXED_PRICE => Html::tag('span', self::FIXED_PRICE()->label(), ['class' => 'badge bg-cyan-lt']),
            default => parent::toHtml(),
        };
    }
}
