<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static DiscountTypeEnum PERCENTAGE()
 * @method static DiscountTypeEnum FIXED()
 */
class DiscountTypeEnum extends Enum
{
    public const PERCENTAGE = 'percentage';
    public const FIXED = 'fixed';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.discount_type';
}
