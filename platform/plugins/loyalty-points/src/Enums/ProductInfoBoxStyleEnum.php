<?php

namespace Botble\LoyaltyPoints\Enums;

use Botble\Base\Supports\Enum;

class ProductInfoBoxStyleEnum extends Enum
{
    public const DEFAULT = 'default';

    public const MINIMAL = 'minimal';

    public const COMPACT = 'compact';

    public const CARD = 'card';

    public const BANNER = 'banner';

    protected static $langPath = 'plugins/loyalty-points::loyalty-points.product_info_box_styles';
}
