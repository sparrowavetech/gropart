<?php

namespace Botble\EcommerceWholesale\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static CustomerGroupStatusEnum PUBLISHED()
 * @method static CustomerGroupStatusEnum DRAFT()
 */
class CustomerGroupStatusEnum extends Enum
{
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';

    public static $langPath = 'plugins/ecommerce-wholesale::enums.customer_group_status';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PUBLISHED => Html::tag('span', self::PUBLISHED()->label(), ['class' => 'badge bg-green text-green-fg']),
            self::DRAFT => Html::tag('span', self::DRAFT()->label(), ['class' => 'badge bg-cyan-lt']),
            default => parent::toHtml(),
        };
    }
}
