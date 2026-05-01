<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Supports\Enum;

class ApplicationStatusEnum extends Enum
{
    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.application_status';

    public function toHtml(): string
    {
        return match ($this->value) {
            self::PENDING => '<span class="badge bg-yellow-lt">' . $this->label() . '</span>',
            self::APPROVED => '<span class="badge bg-green-lt">' . $this->label() . '</span>',
            self::REJECTED => '<span class="badge bg-red-lt">' . $this->label() . '</span>',
            default => parent::toHtml(),
        };
    }
}
