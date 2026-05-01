<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static PricingRuleScopeEnum PRODUCT()
 * @method static PricingRuleScopeEnum CATEGORY()
 * @method static PricingRuleScopeEnum GLOBAL()
 */
class PricingRuleScopeEnum extends Enum
{
    public const PRODUCT = 'product';
    public const CATEGORY = 'category';
    public const GLOBAL = 'global';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.pricing_rule_scope';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PRODUCT => Html::tag('span', self::PRODUCT()->label(), ['class' => 'badge bg-blue-lt']),
            self::CATEGORY => Html::tag('span', self::CATEGORY()->label(), ['class' => 'badge bg-yellow-lt']),
            self::GLOBAL => Html::tag('span', self::GLOBAL()->label(), ['class' => 'badge bg-green-lt']),
            default => parent::toHtml(),
        };
    }
}
