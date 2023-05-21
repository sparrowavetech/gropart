<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Supports\Enum;
use Html;
use Illuminate\Support\HtmlString;

/**
 * @method static RevenueTypeEnum ADD_AMOUNT()
 * @method static RevenueTypeEnum SUBTRACT_AMOUNT()
 */
class RevenueTypeEnum extends Enum
{
    public const ADD_AMOUNT = 'add-amount';
    public const SUBTRACT_AMOUNT = 'subtract-amount';

    public static $langPath = 'plugins/marketplace::revenue.types';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::ADD_AMOUNT => Html::tag('span', self::ADD_AMOUNT()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::SUBTRACT_AMOUNT => Html::tag(
                'span',
                self::SUBTRACT_AMOUNT()->label(),
                ['class' => 'label-primary status-label']
            )
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
