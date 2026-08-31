<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static MarketplaceModeEnum COMMISSION()
 * @method static MarketplaceModeEnum SUBSCRIPTION()
 */
class MarketplaceModeEnum extends Enum
{
    public const COMMISSION = 'commission';

    public const SUBSCRIPTION = 'subscription';

    public static $langPath = 'plugins/marketplace::subscription.modes';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::SUBSCRIPTION => 'info',
            default => 'primary',
        };

        return BaseHelper::renderBadge($this->label(), $color);
    }
}
