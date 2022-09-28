<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Supports\Enum;

class OrderReturnReasonEnum extends Enum
{
    public const NOLONGER_WANT = 'no_longer_want';
    public const DAMAGED = 'damaged';
    public const DEFECTIVE = 'defective';
    public const INCORRECT_ITEM = 'incorrect_item';
    public const ARRIVED_LATE = 'arrived_late';
    public const NOT_AS_DESCRIBED = 'not_as_described';
    public const OTHER = 'other';

    /**
     * @var string
     */
    public static $langPath = 'plugins/ecommerce::order.order_return_reasons';
}
