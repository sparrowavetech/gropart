<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Supports\Enum;

class ProductVisibilityEnum extends Enum
{
    public const PRODUCT_PUBLIC = 'public';

    public const WHOLESALE_ONLY = 'wholesale_only';

    public const SPECIFIC_GROUPS = 'specific_groups';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.product_visibility';
}
