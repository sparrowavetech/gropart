<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static  ShopTypeEnum MANUFACTURE()
 * @method static  ShopTypeEnum WHOLESALER()
 * @method static  ShopTypeEnum RETAILER()
 */
class ShopTypeEnum extends Enum
{
    public const MANUFACTURE = 'manufacture';
    public const WHOLESALER = 'wholesaler';
    public const RETAILER = 'retailer';

    public static $langPath = 'plugins/marketplace::store.types';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::MANUFACTURE => Html::tag('span', self::MANUFACTURE()->label(), ['class' => 'badge bg-info text-info-fg']),
            self::WHOLESALER => Html::tag('span', self::WHOLESALER()->label(), ['class' => 'badge bg-primary text-primary-fg']),
            self::RETAILER => Html::tag('span', self::RETAILER()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            default => parent::toHtml(),
        };
    }
}
