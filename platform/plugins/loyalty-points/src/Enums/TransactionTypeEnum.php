<?php

namespace Botble\LoyaltyPoints\Enums;

use Botble\Base\Supports\Enum;

class TransactionTypeEnum extends Enum
{
    public const EARN = 'earn';

    public const REDEEM = 'redeem';

    public const ADJUST = 'adjust';

    public const REVERSE = 'reverse';

    protected static $langPath = 'plugins/loyalty-points::loyalty-points.transaction_types';

    public function toHtml(): string
    {
        $color = match ($this->value) {
            self::EARN => 'success',
            self::REDEEM => 'warning',
            self::ADJUST => 'info',
            self::REVERSE => 'danger',
            default => 'secondary',
        };

        return sprintf('<span class="badge bg-%s text-white">%s</span>', $color, $this->label());
    }
}
