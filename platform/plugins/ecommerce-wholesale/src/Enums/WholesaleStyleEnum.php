<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static WholesaleStyleEnum MODERN()
 * @method static WholesaleStyleEnum MINIMAL()
 * @method static WholesaleStyleEnum CLASSIC()
 * @method static WholesaleStyleEnum ELEGANT()
 */
class WholesaleStyleEnum extends Enum
{
    public const MODERN = 'modern';

    public const MINIMAL = 'minimal';

    public const CLASSIC = 'classic';

    public const ELEGANT = 'elegant';

    public static $langPath = 'plugins/ecommerce-wholesale::wholesale.styles';
}
