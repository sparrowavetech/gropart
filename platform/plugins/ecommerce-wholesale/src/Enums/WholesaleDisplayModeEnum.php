<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static WholesaleDisplayModeEnum FULL()
 * @method static WholesaleDisplayModeEnum COMPACT()
 */
class WholesaleDisplayModeEnum extends Enum
{
    public const FULL = 'full';

    public const COMPACT = 'compact';

    public static $langPath = 'plugins/ecommerce-wholesale::wholesale.display_modes';
}
